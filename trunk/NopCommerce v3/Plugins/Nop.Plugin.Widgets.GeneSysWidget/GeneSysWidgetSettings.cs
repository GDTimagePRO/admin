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
        public string GeneSysUrl { get; set; }
        public string SiteName { get; set; }
    }
}
