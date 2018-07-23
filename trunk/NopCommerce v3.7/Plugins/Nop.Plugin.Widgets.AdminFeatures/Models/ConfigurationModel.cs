using Nop.Web.Framework;
using Nop.Web.Framework.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Nop.Plugin.Widgets.AdminFeatures.Models
{
    public class ConfigurationModel : BaseNopModel
    {
        [NopResourceDisplayName("Plugins.Widgets.AdminFeatures.UsePickingSlips")]
        public bool UsePickingSlips { get; set; }
        [NopResourceDisplayName("Plugins.Widgets.AdminFeatures.UseUPSExport")]
        public bool UseUPSExport { get; set; }
        [NopResourceDisplayName("Plugins.Widgets.AdminFeatures.UseUPSImport")]
        public bool UseUPSImport { get; set; }
    }
}
