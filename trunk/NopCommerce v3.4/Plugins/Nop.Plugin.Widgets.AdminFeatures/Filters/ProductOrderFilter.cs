using Nop.Admin.Controllers;
using Nop.Admin.Models.Orders;
using Nop.Core.Infrastructure;
using Nop.Plugin.Widgets.GeneSysWidget;
using Nop.Services.Catalog;
using Nop.Services.Orders;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Web.Mvc;
using System.Web.Script.Serialization;

namespace Nop.Plugin.Widgets.AdminFeatures.Filters
{
    class ProductOrderFilter : ActionFilterAttribute, IFilterProvider
    {
        public IEnumerable<Filter> GetFilters(ControllerContext controllerContext,
            ActionDescriptor actionDescriptor)
        {
            if (controllerContext.Controller is OrderController &&
                (actionDescriptor.ActionName.ToLowerInvariant().Equals("edit")))
            {
                return new List<Filter>()
                { 
                    new Filter(this, FilterScope.Action, 0)
                };
            }

            return new List<Filter>();
        }

        public override void OnActionExecuted(ActionExecutedContext filterContext)
        {
            if (filterContext.Controller.ViewData.Model is OrderModel)
            {
                IOrderService _orderService = EngineContext.Current.Resolve<IOrderService>();
                IProductAttributeParser _productAttributeParser = EngineContext.Current.Resolve<IProductAttributeParser>();
                GeneSysWidgetSettings _genesysWidgetSettings = EngineContext.Current.Resolve<GeneSysWidgetSettings>();
                OrderModel model = (OrderModel)filterContext.Controller.ViewData.Model;
               
                foreach (var item in model.Items)
                {
                    if (String.IsNullOrEmpty(item.AttributeInfo))
                    { 
                        var attrValue = _productAttributeParser.ParseValues(_orderService.GetOrderItemById(item.Id).AttributesXml, 0).FirstOrDefault();
                        if (attrValue != null)
                        {
                            GeneSysWidget.Helpers.OrderDataHelper.OrderDetails details = GeneSysWidget.Helpers.OrderDataHelper.getOrderDetails(attrValue);
                            var src = String.Format("{0}/design_part/get_image.php?thumbnail=true&id={1}", _genesysWidgetSettings.GenesysDesignUrl, details.imageId_S);
                            item.CustomProperties.Add("src", src);
                        }
                    }
                    else if (item.AttributeInfo.Contains("Order Id:"))
                    {
                        char[] delimiterChars = { ':' };
                        String[] pieces = item.AttributeInfo.Split(delimiterChars);
                        String designID = pieces[1].Trim();
                        var src = String.Format("{0}/design_part/get_image.php?thumbnail=true&id={1}", _genesysWidgetSettings.GenesysDesignUrl, designID);
                        item.CustomProperties.Add("src", src);
                    }
                }
            }
        }
    }
}
