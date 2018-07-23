using Nop.Web.Framework;
using Nop.Web.Framework.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Web.Mvc;

namespace Nop.Plugin.Widgets.GeneSysWidget.Models
{
    public class ConfigurationModel : BaseNopModel
    {
        [NopResourceDisplayName("Plugins.Widgets.GeneSysWidget.GeneSysUrl")]
        public string GeneSysUrl { get; set; }
        [NopResourceDisplayName("Plugins.Widgets.GeneSysWidget.SiteName")]
        public string SiteName { get; set; }

        /*public int ReturnToID { get; set; }
        [NopResourceDisplayName("Plugins.Widgets.GeneSysWidget.ReturnTo")]
        public SelectList ReturnToValues { get; set; }*/
    }
}
