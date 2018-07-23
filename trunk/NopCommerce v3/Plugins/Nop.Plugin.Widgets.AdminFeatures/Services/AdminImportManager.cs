using Nop.Core.Domain.Orders;
using Nop.Core.Domain.Shipping;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.Threading.Tasks;
using Nop.Services.Orders;
using Nop.Services.Shipping;

namespace Nop.Plugin.Widgets.AdminFeatures.Services
{
    class AdminImportManager
    {

        private readonly IOrderService _orderService;
        private readonly IShipmentService _shipmentService;

        public AdminImportManager(IOrderService orderService, IShipmentService shipmentService)
        {
            _orderService = orderService;
            _shipmentService = shipmentService;
        }

        protected string[] SplitCSV(string line)
        {
            System.Text.RegularExpressions.RegexOptions options = ((System.Text.RegularExpressions.RegexOptions.IgnorePatternWhitespace | System.Text.RegularExpressions.RegexOptions.Multiline)
                | System.Text.RegularExpressions.RegexOptions.IgnoreCase);
            Regex reg = new Regex("(?:^|,)(\\\"(?:[^\\\"]+|\\\"\\\")*\\\"|[^,]*)", options);
            MatchCollection coll = reg.Matches(line);
            string[] items = new string[coll.Count];
            int i = 0;
            foreach (Match m in coll)
            {
                items[i++] = m.Groups[0].Value.Trim('"').Trim(',').Trim('"').Trim();
            }
            return items;
        }

        public void ImportShippingInfoFromCSV(Stream stream)
        {
            StreamReader reader = new StreamReader(stream);
            string text = reader.ReadToEnd();
            string[] lines = text.Split("\n".ToCharArray());
            var count = 0;
            foreach (string line in lines)
            {
                if (count == 0 || String.IsNullOrWhiteSpace(line))
                {
                    count++;
                    continue;
                }
                string[] parts = SplitCSV(line);
                if (!parts[4].Equals("Y"))
                {
                    string[] oid = parts[1].Split(":".ToCharArray());
                    int orderID = 0;
                    if (oid.Length < 2)
                    {
                        orderID = Convert.ToInt32(parts[1]);
                        //return;
                    }
                    else
                    {
                        orderID = Convert.ToInt32(oid[1]);
                    }
                    /*string oid = parts[1].Replace("Order Id: ", "").Trim();
                    int orderID = Convert.ToInt32(oid);*/
                    string dateString = parts[2];
                    Int32 year = Convert.ToInt32(dateString.Substring(0, 4));
                    Int32 month = Convert.ToInt32(dateString.Substring(4, 2));
                    Int32 day = Convert.ToInt32(dateString.Substring(6, 2));
                    Int32 hour = Convert.ToInt32(dateString.Substring(8, 2));
                    Int32 minute = Convert.ToInt32(dateString.Substring(10, 2));
                    Int32 second = Convert.ToInt32(dateString.Substring(12, 2));
                    DateTime shippedOn = new DateTime(year, month, day, hour, minute, second);
                    String trackingNumber = parts[0];
                    Order order = _orderService.GetOrderById(orderID);

                    Shipment shipment = null;

                    decimal? totalWeight = null;
                    foreach (var opv in order.OrderProductVariants)
                    {
                        //is shippable
                        if (!opv.ProductVariant.IsShipEnabled)
                            continue;

                        //ensure that this product variant can be shipped (have at least one item to ship)
                        var maxQtyToAdd = opv.GetTotalNumberOfItemsCanBeAddedToShipment();
                        if (maxQtyToAdd <= 0)
                            continue;

                        //ok. we have at least one item. let's create a shipment (if it does not exist)

                        if (shipment == null)
                        {
                            shipment = new Shipment()
                            {
                                OrderId = order.Id,
                                TrackingNumber = trackingNumber,
                                TotalWeight = null,
                                ShippedDateUtc = shippedOn,
                                DeliveryDateUtc = null,
                                CreatedOnUtc = shippedOn,
                            };
                        }
                        //create a shipment order product variant
                        var sopv = new ShipmentOrderProductVariant()
                        {
                            OrderProductVariantId = opv.Id,
                            Quantity = 1,
                        };
                        shipment.ShipmentOrderProductVariants.Add(sopv);
                    }

                    //if we have at least one item in the shipment, then save it
                    if (shipment != null && shipment.ShipmentOrderProductVariants.Count > 0)
                    {
                        shipment.TotalWeight = totalWeight;
                        _shipmentService.InsertShipment(shipment);
                    }

                    order.ShippingStatus = ShippingStatus.Shipped;
                    order.OrderStatus = OrderStatus.Complete;
                    _orderService.UpdateOrder(order);
                }
            }
        }
    }
}
