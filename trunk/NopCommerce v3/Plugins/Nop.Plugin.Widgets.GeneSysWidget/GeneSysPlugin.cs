using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Web;
using System.Web.Routing;
using Nop.Core;
using Nop.Core.Plugins;
using Nop.Services.Cms;
using Nop.Services.Configuration;
using Nop.Services.Localization;

namespace Nop.Plugin.Widgets.GeneSysWidget
{
    public class GeneSysPlugin : BasePlugin, IWidgetPlugin
    {
        ISettingService _settingService;

        public GeneSysPlugin(ISettingService settingService)
        {
            _settingService = settingService;
        }

        public void GetConfigurationRoute(out string actionName, out string controllerName, out RouteValueDictionary routeValues)
        {
            actionName = "Configure";
            controllerName = "WidgetsGeneSysWidget";
            routeValues = new RouteValueDictionary() { { "Namespaces", "Nop.Plugin.Widgets.GeneSysWidget.Controllers" }, { "area", null } };
        }

        public IList<string> GetWidgetZones()
        {
            return new List<string>() { "add_to_cart", "productdetails_before_pictures", "shopping_cart_pic", "admin_pic", "checkout_completed_top", "order_details_image" };
        }

        public void GetDisplayWidgetRoute(string widgetZone, out string actionName, out string controllerName, out RouteValueDictionary routeValues)
        {
            actionName = "PublicInfo";
            controllerName = "WidgetsGeneSysWidget";
            routeValues = new RouteValueDictionary()
            {
                {"Namespaces", "Nop.Plugin.Widgets.GeneSysWidget.Controllers"},
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
            var settings = new GeneSysWidgetSettings()
            {
                GeneSysUrl = "",
                SiteName = "",
            };
            _settingService.SaveSetting(settings);

            //locales
            this.AddOrUpdatePluginLocaleResource("Plugins.Widgets.GeneSysWidget.GeneSysUrl", "GeneSys URL");
            this.AddOrUpdatePluginLocaleResource("Plugins.Widgets.GeneSysWidget.GeneSysUrl.Hint", "URL where GeneSys is located");
            this.AddOrUpdatePluginLocaleResource("Plugins.Widgets.GeneSysWidget.SiteName", "Site Name");
            this.AddOrUpdatePluginLocaleResource("Plugins.Widgets.GeneSysWidget.SiteName.Hint", "Client Key/Site Name");
            this.AddOrUpdatePluginLocaleResource("Plugins.Widgets.GeneSysWidget.ReturnTo", "Return To");
            this.AddOrUpdatePluginLocaleResource("Plugins.Widgets.GeneSysWidget.ReturnTo.Hint", "If the user should return to the item page or cart after personalizing a product");

            base.Install();
        }

        public override void Uninstall()
        {
            //settings
            _settingService.DeleteSetting<GeneSysWidgetSettings>();

            //locales
            this.DeletePluginLocaleResource("Plugins.Widgets.GeneSysWidget.GeneSysUrl");
            this.DeletePluginLocaleResource("Plugins.Widgets.GeneSysWidget.GeneSysUrl.Hint");
            this.DeletePluginLocaleResource("Plugins.Widgets.GeneSysWidget.SiteName");
            this.DeletePluginLocaleResource("Plugins.Widgets.GeneSysWidget.SiteName.Hint");
            this.DeletePluginLocaleResource("Plugins.Widgets.GeneSysWidget.ReturnTo");
            this.DeletePluginLocaleResource("Plugins.Widgets.GeneSysWidget.ReturnTo.Hint");

            base.Uninstall();
        }
    }
}
