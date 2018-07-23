using Nop.Core;
using Nop.Core.Domain.Orders;
using Nop.Core.Infrastructure;
using Nop.Services.Catalog;
using Nop.Services.Configuration;
using Nop.Services.Orders;
using Nop.Web.Controllers;
using Nop.Web.Framework.Mvc;
using Nop.Web.Models.Media;
using Nop.Web.Models.ShoppingCart;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Web.Mvc;
using System.Web.Script.Serialization;

namespace Nop.Plugin.Widgets.GeneSysWidget.Filters
{
    class ShoppingCartFilter : ActionFilterAttribute, IFilterProvider
    {
        public IEnumerable<Filter> GetFilters(ControllerContext controllerContext,
            ActionDescriptor actionDescriptor)
        {
            if (controllerContext.Controller is ShoppingCartController &&
                (actionDescriptor.ActionName.ToLowerInvariant().Equals("flyoutshoppingcart") ||
                actionDescriptor.ActionName.ToLowerInvariant().Equals("cart") ||
                actionDescriptor.ActionName.ToLowerInvariant().Equals("ordersummary")))
            {
                return new List<Filter>()
                { 
                    new Filter(this, FilterScope.Action, 0)
                };
            }

            return new List<Filter>();
        }

        private PictureModel getPicture(int itemId, GeneSysWidgetSettings genesysWidgetSettings, IProductAttributeParser productAttributeParser, IWorkContext workContext)
        {
            string attrValue = null;
            ShoppingCartItem i = workContext.CurrentCustomer.ShoppingCartItems.Where(a => a.Id == itemId).FirstOrDefault();
            if (i != null)
            {
                attrValue = productAttributeParser.ParseValues(i.AttributesXml, 0).FirstOrDefault();

                if (attrValue != null)
                {
                    Helpers.OrderDataHelper.OrderDetails details = Helpers.OrderDataHelper.getOrderDetails(attrValue);
                    PictureModel pic = new PictureModel()
                    {
                        ImageUrl = String.Format("{0}/design_part/get_image.php?thumbnail=false&id={1}", genesysWidgetSettings.GenesysDesignUrl, details.imageId_S),
                        FullSizeImageUrl = String.Format("{0}/design_part/get_image.php?thumbnail=false&id={1}", genesysWidgetSettings.GenesysDesignUrl, details.imageId_L)
                    };
                    return pic;
                }
            }
            return null;
        }

        public override void OnActionExecuted(ActionExecutedContext filterContext)
        {
            if (filterContext.Controller.ViewData.Model != null)
            {
                ISettingService _settingService = EngineContext.Current.Resolve<ISettingService>();
                IStoreContext _storeContext = EngineContext.Current.Resolve<IStoreContext>();
                var genesysWidgetSettings = _settingService.LoadSetting<GeneSysWidgetSettings>(_storeContext.CurrentStore.Id);
                IProductAttributeParser _productAttributeParser = EngineContext.Current.Resolve<IProductAttributeParser>();
                IWorkContext _workContext = EngineContext.Current.Resolve<IWorkContext>();
                BaseNopModel model = (BaseNopModel)filterContext.Controller.ViewData.Model;
                if (model is MiniShoppingCartModel)
                {
                    MiniShoppingCartModel shopModel = (MiniShoppingCartModel)model;
                    foreach (var item in shopModel.Items)
                    {
                        PictureModel pic = getPicture(item.Id, genesysWidgetSettings, _productAttributeParser, _workContext);
                        if (pic != null)
                        {
                            item.Picture = pic;
                        }
                    }
                }
                else if (model is ShoppingCartModel)
                {
                    ShoppingCartModel shopModel = (ShoppingCartModel)model;
                    foreach (var item in shopModel.Items)
                    {
                        PictureModel pic = getPicture(item.Id, genesysWidgetSettings, _productAttributeParser, _workContext);
                        if (pic != null)
                        {
                            item.Picture = pic;
                        }
                    }
                }
            }
            
            base.OnActionExecuted(filterContext);
        }
    }
}
