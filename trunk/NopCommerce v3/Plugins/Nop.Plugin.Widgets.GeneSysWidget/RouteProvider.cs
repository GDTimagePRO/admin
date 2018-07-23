using System.Web.Mvc;
using System.Web.Routing;
using Nop.Web.Framework.Mvc.Routes;

namespace Nop.Plugin.Widgets.GeneSysWidget
{
    public partial class RouteProvider : IRouteProvider
    {
        public void RegisterRoutes(RouteCollection routes)
        {
            routes.MapRoute("Plugin.Widgets.GeneSysWidget.Controllers.Personalize",
                 "Plugins/WidgetsGeneSysWidget/Personalize",
                 new { controller = "WidgetsGeneSysWidget", action = "Personalize" },
                 new[] { "Nop.Plugin.Widgets.GeneSysWidget.Controller" }
            );

            routes.MapRoute("Plugin.Widgets.GeneSysWidget.Controllers.PersonalizeOnly",
                 "Plugins/WidgetsGeneSysWidget/PersonalizeOnly",
                 new { controller = "WidgetsGeneSysWidget", action = "PersonalizeOnly" },
                 new[] { "Nop.Plugin.Widgets.GeneSysWidget.Controller" }
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
