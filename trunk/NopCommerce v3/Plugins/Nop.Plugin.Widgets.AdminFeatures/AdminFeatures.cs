using Nop.Core.Plugins;
using Nop.Services.Localization;
using Nop.Services.Cms;
using Nop.Services.Configuration;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Web.Routing;

namespace Nop.Plugin.Widgets.AdminFeatures
{
    public class AdminFeatures : BasePlugin, IWidgetPlugin
    {
         ISettingService _settingService;

         public AdminFeatures(ISettingService settingService)
        {
            _settingService = settingService;
        }

        public void GetConfigurationRoute(out string actionName, out string controllerName, out RouteValueDictionary routeValues)
        {
            actionName = "Configure";
            controllerName = "WidgetsAdminFeatures";
            routeValues = new RouteValueDictionary() { { "Namespaces", "Nop.Plugin.Widgets.AdminFeatures.Controllers" }, { "area", null } };
        }

        public IList<string> GetWidgetZones()
        {
            return new List<string>() { "admin_buttons", "admin_javascript" };
        }

        public void GetDisplayWidgetRoute(string widgetZone, out string actionName, out string controllerName, out RouteValueDictionary routeValues)
        {
            actionName = "PublicInfo";
            controllerName = "WidgetsAdminFeatures";
            routeValues = new RouteValueDictionary()
            {
                {"Namespaces", "Nop.Plugin.Widgets.AdminFeatures.Controllers"},
                {"area", null},
                {"widgetZone", widgetZone}
            };
        }

        /// <summary>
        /// Install plugin
        /// </summary>
        public override void Install()
        {
            //settings
            var settings = new AdminFeaturesSettings()
            {
                UsePickingSlips = false,
                UseUPSExport = false,
            };
            _settingService.SaveSetting(settings);

            //locales
            this.AddOrUpdatePluginLocaleResource("Plugins.Widgets.AdminFeatures.UsePickingSlips", "Use Picking Slip Feature");
            this.AddOrUpdatePluginLocaleResource("Plugins.Widgets.AdminFeatures.UsePickingSlips.Hint", "Check to enable");
            this.AddOrUpdatePluginLocaleResource("Plugins.Widgets.AdminFeatures.UseUPSExport", "Use UPS Export Feature");
            this.AddOrUpdatePluginLocaleResource("Plugins.Widgets.AdminFeatures.UseUPSExport.Hint", "Check to enable");
            this.AddOrUpdatePluginLocaleResource("Plugins.Widgets.AdminFeatures.UseUPSImport", "Use UPS Import Feature");
            this.AddOrUpdatePluginLocaleResource("Plugins.Widgets.AdminFeatures.UseUPSImport.Hint", "Check to enable");

            base.Install();
        }

        public override void Uninstall()
        {
            //settings
            _settingService.DeleteSetting<AdminFeaturesSettings>();

            //locales
            this.DeletePluginLocaleResource("Plugins.Widgets.AdminFeatures.UsePickingSlips");
            this.DeletePluginLocaleResource("Plugins.Widgets.AdminFeatures.UsePickingSlips.Hint");
            this.DeletePluginLocaleResource("Plugins.Widgets.AdminFeatures.UseUPSExport");
            this.DeletePluginLocaleResource("Plugins.Widgets.AdminFeatures.UseUPSExport.Hint");
            this.DeletePluginLocaleResource("Plugins.Widgets.AdminFeatures.UseUPSImport");
            this.DeletePluginLocaleResource("Plugins.Widgets.AdminFeatures.UseUPSImport.Hint");
            base.Uninstall();
        }
    }
}
