using System.Web.Mvc;
using System.Web.Routing;
using Nop.Web.Framework.Mvc.Routes;

namespace Nop.Plugin.Widgets.AdminFeatures
{
    public partial class RouteProvider : IRouteProvider
    {
        public void RegisterRoutes(RouteCollection routes)
        {
            routes.MapRoute("Plugin.Widgets.AdminFeatures.Controllers.PdfPickingSlipAll",
                 "WidgetsAdminFeatures/PdfPickingSlipAll",
                 new { controller = "WidgetsAdminFeatures", action = "PdfPickingSlipAll" },
                 new[] { "Nop.Plugin.Widgets.AdminFeatures.Controller" }
            );
            routes.MapRoute("Plugin.Widgets.AdminFeatures.Controllers.PdfPickingSlipSelected",
                 "WidgetsAdminFeatures/PdfPickingSlipSelected",
                 new { controller = "WidgetsAdminFeatures", action = "PdfPickingSlipSelected" },
                 new[] { "Nop.Plugin.Widgets.AdminFeatures.Controller" }
            );
            routes.MapRoute("Plugin.Widgets.AdminFeatures.Controllers.ExportUPSXmlAll",
                 "WidgetsAdminFeatures/ExportUPSXmlAll",
                 new { controller = "WidgetsAdminFeatures", action = "ExportUPSXmlAll" },
                 new[] { "Nop.Plugin.Widgets.AdminFeatures.Controller" }
            );
            routes.MapRoute("Plugin.Widgets.AdminFeatures.Controllers.ExportUPSXmlSelected",
                 "WidgetsAdminFeatures/ExportUPSXmlSelected",
                 new { controller = "WidgetsAdminFeatures", action = "ExportUPSXmlSelected" },
                 new[] { "Nop.Plugin.Widgets.AdminFeatures.Controller" }
            );
            routes.MapRoute("Plugin.Widgets.AdminFeatures.Controllers.ImportShippingInfo",
                 "WidgetsAdminFeatures/ImportShippingInfo",
                 new { controller = "WidgetsAdminFeatures", action = "ImportShippingInfo" },
                 new[] { "Nop.Plugin.Widgets.AdminFeatures.Controller" }
            );
        }
        public int Priority
        {
            get
            {
                return 0;
            }
        }
    }
}
