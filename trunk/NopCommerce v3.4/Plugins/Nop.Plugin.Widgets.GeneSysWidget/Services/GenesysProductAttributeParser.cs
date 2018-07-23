using Nop.Services.Catalog;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Nop.Plugin.Widgets.GeneSysWidget.Services
{
    class GenesysProductAttributeParser : ProductAttributeParser
    {
        public GenesysProductAttributeParser(IProductAttributeService productAttributeService)
            : base(productAttributeService)
        {
        }

        public override bool AreProductAttributesEqual(string attributes1, string attributes2)
        {
            return attributes1.Equals(attributes2);

        }
    }
}
