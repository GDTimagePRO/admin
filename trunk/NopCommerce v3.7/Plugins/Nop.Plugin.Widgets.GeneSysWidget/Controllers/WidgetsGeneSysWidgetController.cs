using System;
using System.Web.Mvc;
using Nop.Web.Framework.Controllers;
using Nop.Services.Security;
using Nop.Plugin.Widgets.GeneSysWidget.Models;
using Nop.Services.Configuration;
using Nop.Services.Orders;
using Nop.Web.Framework;
using Nop.Services.Stores;
using Nop.Core;

namespace Nop.Plugin.Widgets.GeneSysWidget.Controllers
{
    public class WidgetsGeneSysWidgetController : BasePluginController
    {

        #region Fields
        private readonly ISettingService _settingService;
        private readonly IOrderService _orderService;
        private readonly IPermissionService _permissionService;
        private readonly IStoreService _storeService;
        private readonly IWorkContext _workContext;
        #endregion

        public WidgetsGeneSysWidgetController(
            ISettingService settingService,
            IOrderService orderService,     
            IPermissionService permissionService,
            IStoreService storeService,
            IWorkContext workContext)
        {
            this._settingService = settingService;
            this._orderService = orderService;
            this._permissionService = permissionService;
            this._storeService = storeService;
            this._workContext = workContext;
        }

        [AdminAuthorize]
        [ChildActionOnly]
        public ActionResult Configure()
        {
            if (!_permissionService.Authorize(StandardPermissionProvider.ManageExternalAuthenticationMethods))
                return Content("Access denied");

            var storeScope = this.GetActiveStoreScopeConfiguration(_storeService, _workContext);
            var genesysWidgetSettings = _settingService.LoadSetting<GeneSysWidgetSettings>(storeScope);
            var model = new ConfigurationModel();
            model.ReturnToID = Convert.ToInt32(genesysWidgetSettings.ReturnTo);
            model.DistributionId = Convert.ToInt32(genesysWidgetSettings.Distribution);
            model.ImageTypeId = Convert.ToInt32(genesysWidgetSettings.ImageType);
            model.Email = genesysWidgetSettings.Email;
            model.ExternalSystemName = genesysWidgetSettings.ExternalSystemName;
            model.GenesysDesignUrl = genesysWidgetSettings.GenesysDesignUrl;
            model.GenesysProcessUrl = genesysWidgetSettings.GenesysProcessUrl;
            model.SiteName = genesysWidgetSettings.SiteName;
            model.ReturnToValues = genesysWidgetSettings.ReturnTo.ToSelectList();
            model.DistributionValues = genesysWidgetSettings.Distribution.ToSelectList();
            model.ImageTypeValues = genesysWidgetSettings.ImageType.ToSelectList();

            model.ActiveStoreScopeConfiguration = storeScope;
            if (storeScope > 0)
            {
                model.ReturnToValues_OverrideForStore = _settingService.SettingExists(genesysWidgetSettings, x => x.ReturnTo, storeScope);
                model.GenesysDesignUrl_OverrideForStore = _settingService.SettingExists(genesysWidgetSettings, x => x.GenesysDesignUrl, storeScope);
                model.GenesysProcessUrl_OverrideForStore = _settingService.SettingExists(genesysWidgetSettings, x => x.GenesysProcessUrl, storeScope);
                model.SiteName_OverrideForStore = _settingService.SettingExists(genesysWidgetSettings, x => x.SiteName, storeScope);
                model.DistributionValues_OverrideForStore = _settingService.SettingExists(genesysWidgetSettings, x => x.Distribution, storeScope);
                model.Email_OverrideForStore = _settingService.SettingExists(genesysWidgetSettings, x => x.Email, storeScope);
                model.ExternalSystemName_OverrideForStore = _settingService.SettingExists(genesysWidgetSettings, x => x.ExternalSystemName, storeScope);
                model.ImageTypeValues_OverrideForStore = _settingService.SettingExists(genesysWidgetSettings, x => x.ImageType, storeScope);
            }

            return View("~/Plugins/Widgets.GeneSysWidget/Views/GeneSysWidget/Configure.cshtml", model);
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

            var storeScope = this.GetActiveStoreScopeConfiguration(_storeService, _workContext);

            var genesysWidgetSettings = _settingService.LoadSetting<GeneSysWidgetSettings>(storeScope);

            genesysWidgetSettings.ReturnTo = (ReturnTo)model.ReturnToID;
            genesysWidgetSettings.GenesysDesignUrl = model.GenesysDesignUrl;
            genesysWidgetSettings.GenesysProcessUrl = model.GenesysProcessUrl;
            genesysWidgetSettings.SiteName = model.SiteName;
            genesysWidgetSettings.Distribution = (Distribution)model.DistributionId;
            genesysWidgetSettings.Email = model.Email;
            genesysWidgetSettings.ExternalSystemName = model.ExternalSystemName;
            genesysWidgetSettings.ImageType = (ImageType)model.ImageTypeId;

            if (model.ReturnToValues_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(genesysWidgetSettings, x => x.ReturnTo, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(genesysWidgetSettings, x => x.ReturnTo, storeScope);

            if (model.GenesysDesignUrl_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(genesysWidgetSettings, x => x.GenesysDesignUrl, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(genesysWidgetSettings, x => x.GenesysDesignUrl, storeScope);

            if (model.GenesysProcessUrl_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(genesysWidgetSettings, x => x.GenesysProcessUrl, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(genesysWidgetSettings, x => x.GenesysProcessUrl, storeScope);

            if (model.SiteName_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(genesysWidgetSettings, x => x.SiteName, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(genesysWidgetSettings, x => x.SiteName, storeScope);

            if (model.DistributionValues_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(genesysWidgetSettings, x => x.Distribution, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(genesysWidgetSettings, x => x.Distribution, storeScope);

            if (model.Email_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(genesysWidgetSettings, x => x.Email, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(genesysWidgetSettings, x => x.Email, storeScope);

            if (model.ExternalSystemName_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(genesysWidgetSettings, x => x.ExternalSystemName, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(genesysWidgetSettings, x => x.ExternalSystemName, storeScope);

            if (model.ImageTypeValues_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(genesysWidgetSettings, x => x.ImageType, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(genesysWidgetSettings, x => x.ImageType, storeScope);

            //_settingService.SaveSetting(genesysWidgetSettings);
            _settingService.ClearCache();

            return Configure();
        }

    }
}
