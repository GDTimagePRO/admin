using Nop.Plugin.Widgets.AdminFeatures.Models;
using Nop.Services.Common;
using Nop.Services.Configuration;
using Nop.Services.Security;
using Nop.Web.Framework.Controllers;
using Nop.Core.Domain.Orders;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Web.Mvc;
using Nop.Services.Orders;
using Nop.Core.Domain.Payments;
using Nop.Core.Domain.Shipping;
using Nop.Core;
using Nop.Plugin.Widgets.GeneSysWidget;
using Nop.Core.Domain.Common;
using Nop.Services.Localization;
using Nop.Services.Payments;
using Nop.Services.Helpers;
using Nop.Services.Messages;
using Nop.Web.Controllers;
using Nop.Web.Framework.Mvc;
using Nop.Services.Shipping;
using Nop.Admin.Controllers;

namespace Nop.Plugin.Widgets.AdminFeatures.Controllers
{
    public class WidgetsAdminFeaturesController : BasePluginController
    {
        private readonly IPermissionService _permissionService;
        private readonly AdminFeaturesSettings _adminFeaturesSettings;
        private readonly ISettingService _settingService;
        private readonly IPdfService _pdfService;
        private readonly IOrderService _orderService;
        private readonly IOrderProcessingService _orderProcessingService;
        private readonly IWorkContext _workContext;
        private readonly GeneSysWidgetSettings _genesysSettings;
        private readonly IWebHelper _webHelper;
        private readonly PdfSettings _pdfSettings;
        private readonly ILocalizationService _localizationService;
        private readonly AddressSettings _addressSettings;
        private readonly IPaymentService _paymentService;
        private readonly IDateTimeHelper _dateTimeHelper;
        private readonly IWorkflowMessageService _workflowMessageService;
        private readonly IShipmentService _shipmentService;

        public WidgetsAdminFeaturesController(IPermissionService permissionService, AdminFeaturesSettings adminFeaturesSettings, ISettingService settingService, 
            IPdfService pdfService, IOrderService orderService, IOrderProcessingService orderProcessingService, IWorkContext workContext, GeneSysWidgetSettings genesysSettings,
            IWebHelper webhelper, PdfSettings pdfSettings, ILocalizationService localizationService, AddressSettings addressSettings, IPaymentService paymentService, IDateTimeHelper dateTimeHelper, 
            IWorkflowMessageService workflowMessageService, IShipmentService shipmentService)
        {
            _permissionService = permissionService;
            _adminFeaturesSettings = adminFeaturesSettings;
            _settingService = settingService;
            _pdfService = pdfService;
            _orderService = orderService;
            _orderProcessingService = orderProcessingService;
            _workContext = workContext;
            _genesysSettings = genesysSettings;
            _webHelper = webhelper;
            _pdfSettings = pdfSettings;
            _localizationService = localizationService;
            _addressSettings = addressSettings;
            _paymentService = paymentService;
            _dateTimeHelper = dateTimeHelper;
            _workflowMessageService = workflowMessageService;
            _shipmentService = shipmentService;
        }

        private ButtonsModel PrepareButtonModel()
        {
            ButtonsModel model = new ButtonsModel();
            if (_adminFeaturesSettings.UsePickingSlips)
            {
                model.buttons.Add(new ButtonsModel.Button("Order", "List", "PdfPickingSlipAll", "Print All Picking Slips", false));
                model.buttons.Add(new ButtonsModel.Button("Order", "List", "PdfPickingSlipSelected", "Print Selected Picking Slips", true));
            }
            if (_adminFeaturesSettings.UseUPSExport)
            {
                model.buttons.Add(new ButtonsModel.Button("Order", "List", "ExportUPSXmlAll", "Export All to UPS XML", false));
                model.buttons.Add(new ButtonsModel.Button("Order", "List", "ExportUPSXmlSelected", "Export Selected to UPS XML", true));
            }
            if (_adminFeaturesSettings.UseUPSImport)
            {
                model.buttons.Add(new ButtonsModel.Button("Order", "List", "ImportShippingInfo", "Import UPS Shipping Info", false, true));
            }
            return model;
        }

        [AdminAuthorize]
        [ChildActionOnly]
        public ActionResult Configure()
        {
            if (!_permissionService.Authorize(StandardPermissionProvider.ManageExternalAuthenticationMethods))
                return Content("Access denied");

            var model = new ConfigurationModel();
            model.UsePickingSlips = _adminFeaturesSettings.UsePickingSlips;
            model.UseUPSExport = _adminFeaturesSettings.UseUPSExport;
            model.UseUPSImport = _adminFeaturesSettings.UseUPSImport;

            return View("~/Plugins/Widgets.AdminFeatures/Views/AdminFeatures/Configure.cshtml", model);
        }

        [HttpPost]
        [AdminAuthorize]
        [ChildActionOnly]
        public ActionResult Configure(ConfigurationModel model)
        {
            if (!_permissionService.Authorize(StandardPermissionProvider.ManageExternalAuthenticationMethods))
                return Content("Access denied");

            if (!ModelState.IsValid)
                return Configure();

            _adminFeaturesSettings.UseUPSExport = model.UseUPSExport;
            _adminFeaturesSettings.UsePickingSlips = model.UsePickingSlips;
            _adminFeaturesSettings.UseUPSImport = model.UseUPSImport;
            _settingService.SaveSetting(_adminFeaturesSettings);

            return View("~/Plugins/Widgets.AdminFeatures/Views/AdminFeatures/Configure.cshtml", model);
        }

        [ChildActionOnly]
        public ActionResult PublicInfo(string widgetZone)
        {
            ButtonsModel model = PrepareButtonModel();
            if (widgetZone == "admin_javascript")
            {
                return View("~/Plugins/Widgets.AdminFeatures/Views/AdminFeatures/Javascript.cshtml", model);
            }
            else
            {
                return View("~/Plugins/Widgets.AdminFeatures/Views/AdminFeatures/Buttons.cshtml", model);
            }
        }

        public ActionResult PdfPickingSlipAll()
        {
            if (!_permissionService.Authorize(StandardPermissionProvider.ManageOrders))
                return Content("Access denied");

            var orders = _orderService.SearchOrders(0, 0, 0, 0, 0, 0, null, null, OrderStatus.Processing,
                      PaymentStatus.Paid, ShippingStatus.NotYetShipped, null, null, 0, int.MaxValue);

            byte[] bytes = null;
            Services.AdminPdfServices adminPdfService = (Services.AdminPdfServices)_pdfService;
            using (var stream = new MemoryStream())
            {
                adminPdfService.PrintPickingSlipsToPdf(stream, orders, _workContext.WorkingLanguage);
                bytes = stream.ToArray();
            }

            Services.AdminOrderService adminOrderService = new Services.AdminOrderService(_orderService, _workflowMessageService);
            foreach (var order in orders)
            {
                adminOrderService.MarkOrderAsPending(order, false);
            }
            return File(bytes, "application/pdf", "pickingslips.pdf");
        }

        public ActionResult PdfPickingSlipSelected(string selectedIds)
        {
            if (!_permissionService.Authorize(StandardPermissionProvider.ManageOrders))
                return Content("Access denied");

            var orders = new List<Order>();
            if (selectedIds != null)
            {
                var ids = selectedIds
                    .Split(new char[] { ',' }, StringSplitOptions.RemoveEmptyEntries)
                    .Select(x => Convert.ToInt32(x))
                    .ToArray();
                orders.AddRange(_orderService.GetOrdersByIds(ids));
            }

            byte[] bytes = null;
            Services.AdminPdfServices adminPdfService = (Services.AdminPdfServices)_pdfService;
            using (var stream = new MemoryStream())
            {
                adminPdfService.PrintPickingSlipsToPdf(stream, orders, _workContext.WorkingLanguage);
                bytes = stream.ToArray();
            }

            return File(bytes, "application/pdf", "pickingslips.pdf");
        }

        public ActionResult ExportUPSXmlAll()
        {
            if (!_permissionService.Authorize(StandardPermissionProvider.ManageOrders))
                return Content("Access denied");

            try
            {
                var orders = _orderService.SearchOrders(0, 0, 0, 0, 0, 0, null, null, null,
                    null, null, null, null, 0, int.MaxValue);
                Services.AdminExportManager adminExportManager = new Services.AdminExportManager();
                var xml = adminExportManager.ExportOrdersToUPSXML(orders);
                return new XmlDownloadResult(xml, "orders.xml");
            }
            catch (Exception exc)
            {
                ErrorNotification(exc);
                return RedirectToAction("List");
            }
        }

        public ActionResult ExportUPSXmlSelected(string selectedIds)
        {
            if (!_permissionService.Authorize(StandardPermissionProvider.ManageOrders))
                return Content("Access denied");

            var orders = new List<Order>();
            if (selectedIds != null)
            {
                var ids = selectedIds
                    .Split(new char[] { ',' }, StringSplitOptions.RemoveEmptyEntries)
                    .Select(x => Convert.ToInt32(x))
                    .ToArray();
                orders.AddRange(_orderService.GetOrdersByIds(ids));
            }
            Services.AdminExportManager adminExportManager = new Services.AdminExportManager();
            var xml = adminExportManager.ExportOrdersToUPSXML(orders);
            return new XmlDownloadResult(xml, "orders.xml");
        }

        [HttpPost]
        public ActionResult ImportShippingInfo(FormCollection form)
        {
            if (!_permissionService.Authorize(StandardPermissionProvider.ManageOrders))
                return Content("Access denied");

            try
            {
                var file = Request.Files["ImportShippingInfo-file"];
                if (file != null && file.ContentLength > 0)
                {
                    Services.AdminImportManager adminImportManager = new Services.AdminImportManager(_orderService, _shipmentService);
                    adminImportManager.ImportShippingInfoFromCSV(file.InputStream);
                }
                else
                {
                    ErrorNotification(_localizationService.GetResource("Admin.Common.UploadFile"));
                    return RedirectToAction("List", new { Controller = "Order", Area = "Admin" });
                }
                SuccessNotification("Shipping Information Imported Successfully");
                return RedirectToAction("List", new { Controller = "Order", Area = "Admin" });
            }
            catch (Exception exc)
            {
                ErrorNotification(exc);
                return RedirectToAction("List", new { Controller = "Order", Area = "Admin" });
            }
        }


    }
}
