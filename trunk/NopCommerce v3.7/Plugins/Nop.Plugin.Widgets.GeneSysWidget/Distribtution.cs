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
    public enum Distribution : int
    {
        /// <summary>
        /// ProductPage
        /// </summary>
        Workstation = 0,
        /// <summary>
        /// Cart
        /// </summary>
        Email = 1
    }
}
