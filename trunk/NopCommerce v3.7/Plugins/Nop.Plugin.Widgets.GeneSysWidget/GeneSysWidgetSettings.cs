using Nop.Core.Configuration;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Nop.Plugin.Widgets.GeneSysWidget
{
    public class GeneSysWidgetSettings : ISettings
    {
        public ReturnTo ReturnTo { get; set; }
        public string GenesysDesignUrl { get; set; }
        public string GenesysProcessUrl { get; set; }
        public string SiteName { get; set; }
        public Distribution Distribution { get; set; }
        public ImageType ImageType { get; set; }
        public string Email { get; set; }
        public string ExternalSystemName { get; set; }
    }
}
