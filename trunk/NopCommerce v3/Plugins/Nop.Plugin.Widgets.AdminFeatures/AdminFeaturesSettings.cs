using Nop.Core.Configuration;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Nop.Plugin.Widgets.AdminFeatures
{
    public class AdminFeaturesSettings : ISettings
    {
        public bool UsePickingSlips { get; set; }
        public bool UseUPSExport { get; set; }
        public bool UseUPSImport { get; set; }
    }
}
