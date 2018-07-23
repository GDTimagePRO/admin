using Nop.Core;
using Nop.Core.Domain.Common;
using Nop.Core.Domain.Orders;
using Nop.Core.Infrastructure;
using Nop.Services.Catalog;
using Nop.Services.Configuration;
using Nop.Services.Events;
using Nop.Services.Logging;
using Nop.Services.Orders;
using Nop.Services.Stores;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Text;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.Script.Serialization;

namespace Nop.Plugin.Widgets.GeneSysWidget.EventHandlers
{
    class GenesysOrderHandler : IConsumer<OrderPaidEvent>
    {

        private void WorkstationCheckout(Order order, GeneSysWidgetSettings genesysWidgetSettings)
        {
            IProductAttributeParser _productAttributeParser = EngineContext.Current.Resolve<IProductAttributeParser>();
            int i = 0;
            JavaScriptSerializer serializer = new JavaScriptSerializer();
            foreach (var item in order.OrderItems)
            {
                string attrValue = _productAttributeParser.ParseValues(item.AttributesXml, 0).FirstOrDefault();

                if (attrValue != null)
                {
                    Helpers.OrderDataHelper.OrderDetails details = Helpers.OrderDataHelper.getOrderDetails(attrValue);

                    var parameters = "externalOrderId=" + ((order.Id * 1000) + i) + "&orderItemId=" + details.orderItemId;
                    if (order.ShippingAddress != null)
                    {
                        parameters += "&shippingInfo=" + HttpUtility.UrlEncode(serializer.Serialize(getShippingData(order.ShippingAddress)));
                    }
                    string URI = genesysWidgetSettings.GenesysDesignUrl + "/services/update_order_item.php?confirm=true&" + parameters;
                    string resp;
                    try
                    {
                        using (WebClient wc = new WebClient())
                        {
                            wc.Headers[HttpRequestHeader.ContentType] = "application/x-www-form-urlencoded";
                            resp = wc.DownloadString(URI);
                        }

                        jsonResult data = serializer.Deserialize<jsonResult>(resp);
                        if (data.errorCode != 0)
                        {
                            var logger = EngineContext.Current.Resolve<ILogger>();
                            logger.Error("Error: " + data.errorMessage + " Order #: " + order.Id + " DesignId: " + details.orderItemId, null, order.Customer);
                        }
                    }
                    catch (Exception e)
                    {
                        var logger = EngineContext.Current.Resolve<ILogger>();
                        logger.Error("Error completing web request to genesys server during order paid action", e, order.Customer);
                    }
                    i++;
                }
            }
        }

        private void EmailCheckout(Order order, GeneSysWidgetSettings genesysWidgetSettings)
        {
            IStoreService storeService = EngineContext.Current.Resolve<IStoreService>();
            IProductAttributeParser _productAttributeParser = EngineContext.Current.Resolve<IProductAttributeParser>();
            JavaScriptSerializer serializer = new JavaScriptSerializer();
            EmailServiceParams esp = new EmailServiceParams();
            StringBuilder message = new StringBuilder();
            string imageType = "png";
            if (genesysWidgetSettings.ImageType == ImageType.SVG)
            {
                imageType = "svg";
            }
            esp.to = new string[] { genesysWidgetSettings.Email };
            esp.subject = storeService.GetStoreById(order.StoreId).Name + " - Order # " + order.Id;
            List<EmailServiceAttachmentParams> attachments = new List<EmailServiceAttachmentParams>();
            message.Append("<html><body><table border=\"0\"><tr><td colspan=\"2\">Order Information</td></tr>");
            message.Append(String.Format("<tr><td>Order id: </td><td>{0}</td></tr>", order.Id));
            message.Append(String.Format("<tr><td>Customer Name: </td><td>{0}</td></tr>", order.Customer.SystemName));
            message.Append(String.Format("<tr><td>Customer Email: </td><td>{0}</td></tr>", order.Customer.Email));
            message.Append(String.Format("<tr><td>Shipping Phone #: </td><td>{0}</td></tr>", order.ShippingAddress.PhoneNumber));
            message.Append("<tr><td></td><td></td></tr>");
            message.Append("<tr><td colspan=\"2\">Order Items</td></tr>");
            foreach (var item in order.OrderItems)
            {
                message.Append("<tr><td></td><td></td></tr>");
                message.Append(String.Format("<tr><td>Item Name: </td><td>{0}</td></tr>", item.Product.Name));
                message.Append(String.Format("<tr><td>Item SKU: </td><td>{0}</td></tr>", item.Product.Sku));
                message.Append(String.Format("<tr><td>Item Quantity: </td><td>{0}</td></tr>", item.Quantity));
                string attrValue = _productAttributeParser.ParseValues(item.AttributesXml, 0).FirstOrDefault();
                if (attrValue != null)
                {
                    Helpers.OrderDataHelper.OrderDetails details = Helpers.OrderDataHelper.getOrderDetails(attrValue);
                    foreach (Helpers.OrderDataHelper.DesignDetails design in details.designs)
                    {
                        EmailServiceAttachmentParams attachment = new EmailServiceAttachmentParams();
                        attachment.rid = "";
                        attachment.fileName = design.designId + "." + imageType;
                        attachment.sid = design.designId + " _image";
                        attachment.url = genesysWidgetSettings.GenesysDesignUrl + "render_image.php?dpi=300&type=" + imageType + "&designId=" + design.designId + "&name=" + design.designId + "." + imageType;
                        message.Append(String.Format("<tr><td>Image: </td><td>{0}</td></tr>", attachment.sid));
                        attachments.Add(attachment);
                    }
                }
            }
            esp.attachments = attachments.ToArray();
            message.Append("</table></body></html>");
            esp.messageHTML = message.ToString();
            if (attachments.Count > 0)
            {
                string URI = genesysWidgetSettings.GenesysProcessUrl + "/ARTServer/SendMail?params=" + HttpUtility.UrlEncode(serializer.Serialize(esp));
                string resp;
                try
                {
                    using (WebClient wc = new WebClient())
                    {
                        wc.Headers[HttpRequestHeader.ContentType] = "application/x-www-form-urlencoded";
                        resp = wc.DownloadString(URI);
                    }

                    jsonResult data = serializer.Deserialize<jsonResult>(resp);
                    if (data.errorCode != 0)
                    {
                        var logger = EngineContext.Current.Resolve<ILogger>();
                        logger.Error("Error: " + data.errorMessage + " Order #: " + order.Id, null, order.Customer);
                    }
                }
                catch (Exception e)
                {
                    var logger = EngineContext.Current.Resolve<ILogger>();
                    logger.Error("Error completing email request to genesys server during order paid action", e, order.Customer);
                }
            }
        }


        public void HandleEvent(OrderPaidEvent eventMessage)
        {
            IOrderService _orderService = EngineContext.Current.Resolve<IOrderService>();
            IWorkContext _workContext = EngineContext.Current.Resolve<IWorkContext>();
            ISettingService _settingService = EngineContext.Current.Resolve<ISettingService>();
            
            
            Order order = eventMessage.Order;
            var genesysWidgetSettings = _settingService.LoadSetting<GeneSysWidgetSettings>(order.StoreId);

            if (genesysWidgetSettings.Distribution == Distribution.Email)
            {
                EmailCheckout(order, genesysWidgetSettings);
            }
            else
            {
                WorkstationCheckout(order, genesysWidgetSettings);
            }
           
        }

        private jsonShipData getShippingData(Address address)
        {
            jsonShipData data = new jsonShipData();
            data.first_name = address.FirstName;
            data.last_name = address.LastName;
            data.address_1 = address.Address1;
            data.address_2 = address.Address2;
            data.city = address.City;
            data.zip_postal_code = address.ZipPostalCode;
            data.state_province = address.StateProvince.Name;
            data.country = address.Country.Name;
            data.email = address.Email;
            return data;
        }

        private class jsonResult
        {
            public int errorCode { get; set; }
            public string errorMessage { get; set; }
        }

        private class jsonShipData
        {
            public string first_name { get; set; }
            public string last_name { get; set; }
            public string address_1 { get; set; }
            public string address_2 { get; set; }
            public string city { get; set; }
            public string state_province { get; set; }
            public string zip_postal_code { get; set; }
            public string country { get; set; }
            public string email { get; set; }
            public string weight { get; set; }
        }

        private class EmailServiceAttachmentParams
        {
            public string rid;
            public string sid;
            public string fileName;
            public string url;
        }

        private class EmailServiceParams
        {
            public string from { get; set; }
            public string[] to { get; set; }
            public string subject { get; set; }
            public string messageHTML { get; set; }
            public EmailServiceAttachmentParams[] attachments { get; set; }
        }
    }

    
}
