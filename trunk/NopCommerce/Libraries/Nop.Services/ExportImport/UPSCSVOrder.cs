using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Nop.Core.Domain.Orders;
namespace Nop.Services.ExportImport
{
    class UPSCSVOrder
    {
        public virtual string ContactName { get; set; }
        public virtual string Email { get; set; }
        public virtual string Phone { get; set; }
        public virtual string CompanyOrName { get; set; }
        public virtual string Address1 { get; set; }
        public virtual string Address2 { get; set; }
        public virtual string City { get; set; }
        public virtual string State { get; set; }
        public virtual string PostalCode { get; set; }
        public virtual string Country { get; set; }
        public virtual string PackagingType { get; set; }
        public virtual string ServiceType { get; set; }
        public virtual decimal Weight { get; set; }
        
    }
}
