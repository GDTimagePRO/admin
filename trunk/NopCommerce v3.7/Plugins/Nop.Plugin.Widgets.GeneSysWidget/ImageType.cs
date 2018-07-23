using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Nop.Plugin.Widgets.GeneSysWidget
{
    /// <summary>
    /// Represents where to send genesys data
    /// </summary>
    public enum ImageType : int
    {
        /// <summary>
        /// ProductPage
        /// </summary>
        PNG = 0,
        /// <summary>
        /// Cart
        /// </summary>
        SVG = 1
    }
}
