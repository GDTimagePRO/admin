using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Web.Mvc;
using Nop.Web.Controllers;
using Nop.Services.Catalog;
using Nop.Core.Infrastructure;
using System.Web;
using System.Web.Routing;
using Nop.Services.Seo;
using System.Web.Script.Serialization;
using System.Net;
using Nop.Services.Orders;
using Nop.Core;
using Nop.Core.Domain.Orders;
using Nop.Core.Domain.Catalog;
using Nop.Web.Models.Catalog;
using Nop.Web.Models.Media;
using Nop.Services.Configuration;

namespace Nop.Plugin.Widgets.GeneSysWidget.Filters
{
    class ProductDetailsFilter : ActionFilterAttribute, IFilterProvider
    {
        public IEnumerable<Filter> GetFilters(ControllerContext controllerContext,
            ActionDescriptor actionDescriptor)
        {
            if (controllerContext.Controller is ProductController &&
                actionDescriptor.ActionName.ToLowerInvariant().Equals("productdetails"))
            {
                return new List<Filter>()
                { 
                    new Filter(this, FilterScope.Action, 0)
                };
            }

            return new List<Filter>();
        }

        public override void OnActionExecuting(ActionExecutingContext filterContext)
        {
            //TODO: Merge AddtoCart from ProductDetails filter and this into one function
            ISettingService _settingService = EngineContext.Current.Resolve<ISettingService>();
            IStoreContext _storeContext = EngineContext.Current.Resolve<IStoreContext>();
            var genesysWidgetSettings = _settingService.LoadSetting<GeneSysWidgetSettings>(_storeContext.CurrentStore.Id);
            if (genesysWidgetSettings.ReturnTo == ReturnTo.Cart && filterContext.HttpContext.Request.Params["OrderId"] != null)
            {
                IShoppingCartService _shoppingService = EngineContext.Current.Resolve<IShoppingCartService>();
                IWorkContext _workContext = EngineContext.Current.Resolve<IWorkContext>();
                IProductService _productService = EngineContext.Current.Resolve<IProductService>();
                IProductAttributeParser _productAttributeParser = EngineContext.Current.Resolve<IProductAttributeParser>();
                var cartType = ShoppingCartType.ShoppingCart;
                var productId = (int)filterContext.RouteData.Values["productid"];
                var attributes = "";
                ProductVariantAttribute attr = new ProductVariantAttribute() {
                    ProductId = productId,
                    ProductAttributeId = 0,
                    TextPrompt = "",
                    IsRequired = true,
                    AttributeControlTypeId = 1,
                    DisplayOrder = 0,
                    ValidationMinLength = 0,
                    ValidationMaxLength = 0,
                    ValidationFileAllowedExtensions =  "",
                    ValidationFileMaximumSize = 0,
                    DefaultValue = "",
                };

                attributes = _productAttributeParser.AddProductAttribute("", attr, filterContext.HttpContext.Request.Params["OrderDetails"]);
                _shoppingService.AddToCart(_workContext.CurrentCustomer, _productService.GetProductById(productId), cartType, _storeContext.CurrentStore.Id, attributes, 0, 1, true);
                UrlHelper helper = new UrlHelper(filterContext.RequestContext, RouteTable.Routes);
                filterContext.Result = new RedirectResult(helper.RouteUrl("ShoppingCart"));
            }
            base.OnActionExecuting(filterContext);
        }

        public override void OnActionExecuted(ActionExecutedContext filterContext)
        {
            ISettingService _settingService = EngineContext.Current.Resolve<ISettingService>();
            IStoreContext _storeContext = EngineContext.Current.Resolve<IStoreContext>();
            var genesysWidgetSettings = _settingService.LoadSetting<GeneSysWidgetSettings>(_storeContext.CurrentStore.Id);
            if (genesysWidgetSettings.ReturnTo == ReturnTo.ProductPage && filterContext.HttpContext.Request.Params["OrderId"] != null)
            {
                String designID = filterContext.HttpContext.Request.Params["OrderId"].Replace("thumbs", "original");
                ProductDetailsModel model = (ProductDetailsModel)filterContext.Controller.ViewData.Model;
                PictureModel pic = new PictureModel()
                {
                    ImageUrl = String.Format("{0}/design_part/get_image.php?thumbnail=false&id={1}", genesysWidgetSettings.GenesysDesignUrl, designID),
                    FullSizeImageUrl = String.Format("{0}/design_part/get_image.php?thumbnail=false&id={1}", genesysWidgetSettings.GenesysDesignUrl, designID)
                };
                model.DefaultPictureModel = pic;
            }
            base.OnActionExecuted(filterContext);
        }

    }
}
