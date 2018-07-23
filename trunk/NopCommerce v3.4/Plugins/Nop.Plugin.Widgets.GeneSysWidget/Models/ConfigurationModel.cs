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
        public int ActiveStoreScopeConfiguration { get; set; }

        [NopResourceDisplayName("Plugins.Widgets.GeneSysWidget.DesignUrl")]
        public string GenesysDesignUrl { get; set; }
        public bool GenesysDesignUrl_OverrideForStore { get; set; }

        [NopResourceDisplayName("Plugins.Widgets.GeneSysWidget.ProcessUrl")]
        public string GenesysProcessUrl { get; set; }
        public bool GenesysProcessUrl_OverrideForStore { get; set; }

        [NopResourceDisplayName("Plugins.Widgets.GeneSysWidget.SiteName")]
        public string SiteName { get; set; }
        public bool SiteName_OverrideForStore { get; set; }

        public int ReturnToID { get; set; }
        [NopResourceDisplayName("Plugins.Widgets.GeneSysWidget.ReturnTo")]
        public SelectList ReturnToValues { get; set; }
        public bool ReturnToValues_OverrideForStore { get; set; }

        public int DistributionId { get; set; }
        [NopResourceDisplayName("Plugins.Widgets.GeneSysWidget.Distribution")]
        public SelectList DistributionValues { get; set; }
        public bool DistributionValues_OverrideForStore { get; set; }

        [NopResourceDisplayName("Plugins.Widgets.GeneSysWidget.Email")]
        public string Email { get; set; }
        public bool Email_OverrideForStore { get; set; }

        [NopResourceDisplayName("Plugins.Widgets.GeneSysWidget.ExternalSystemName")]
        public string ExternalSystemName { get; set; }
        public bool ExternalSystemName_OverrideForStore { get; set; }

        public int ImageTypeId { get; set; }
        [NopResourceDisplayName("Plugins.Widgets.GeneSysWidget.ImageType")]
        public SelectList ImageTypeValues { get; set; }
        public bool ImageTypeValues_OverrideForStore { get; set; }
    }
}
