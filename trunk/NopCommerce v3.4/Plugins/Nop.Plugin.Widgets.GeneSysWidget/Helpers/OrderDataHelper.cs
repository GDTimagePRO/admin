using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.Threading.Tasks;
using System.Web.Script.Serialization;

namespace Nop.Plugin.Widgets.GeneSysWidget.Helpers
{
    public class OrderDataHelper
    {

        public static OrderDetails getOrderDetails(string attribute)
        {
            OrderDetails order;
            if (Regex.IsMatch(attribute, @"^thumbs\.order_items/\d+_prev\.png"))
            {
                order = new OrderDetails();
                order.imageId_L = attribute;
                order.imageId_S = attribute;
                order.designs = new List<DesignDetails>();
                order.orderItemId = Int32.Parse(Regex.Match(attribute, @"\d+").Value);
            }
            else
            {
                JavaScriptSerializer serializer = new JavaScriptSerializer();
                order = serializer.Deserialize<Helpers.OrderDataHelper.OrderDetails>(attribute);
            }
            return order;
        }

        public class DesignDetails
        {
            public int designId { get; set; }
        }

        public class OrderDetails
        {
            public int orderItemId { get; set; }
            public string imageId_L { get; set; }
            public string imageId_S { get; set; }
            public List<DesignDetails> designs { get; set; }
        }
    }
}
