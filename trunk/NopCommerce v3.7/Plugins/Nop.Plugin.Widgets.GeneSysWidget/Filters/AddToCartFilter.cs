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
using Nop.Services.Common;
using Nop.Services.Logging;
using Nop.Services.Configuration;

namespace Nop.Plugin.Widgets.GeneSysWidget.Filters
{
    class AddToCartFilter : ActionFilterAttribute, IFilterProvider
    {
        public IEnumerable<Filter> GetFilters(ControllerContext controllerContext,
            ActionDescriptor actionDescriptor)
        {
            if (controllerContext.Controller is ShoppingCartController &&
                (actionDescriptor.ActionName.ToLowerInvariant().Equals("addproducttocart_details") ||
                actionDescriptor.ActionName.ToLowerInvariant().Equals("addproducttocart_catalog")))
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
            ISettingService _settingService = EngineContext.Current.Resolve<ISettingService>();
            IProductService _productService = EngineContext.Current.Resolve<IProductService>();
            IStoreContext _storeContext = EngineContext.Current.Resolve<IStoreContext>();
            var genesysWidgetSettings = _settingService.LoadSetting<GeneSysWidgetSettings>(_storeContext.CurrentStore.Id);

            if (!filterContext.HttpContext.Request.UrlReferrer.Query.Contains("OrderId"))
            {
                var product = _productService.GetProductById((int)filterContext.ActionParameters["productId"]);
            
                if (product.ProductSpecificationAttributes.Any(a => a.SpecificationAttributeOption.SpecificationAttribute.Name.ToLowerInvariant().Equals("genesysproduct")))
                {
                    var code = product.Sku;
                    var siteName = HttpUtility.UrlEncode(genesysWidgetSettings.SiteName);
                    Uri returnUrl;
                    UrlHelper helper = new UrlHelper(filterContext.RequestContext, RouteTable.Routes);
                    var submitUrl = HttpUtility.UrlEncode(helper.RouteUrl("Product", new { SeName = product.GetSeName() }, filterContext.HttpContext.Request.Url.Scheme));
                    returnUrl = filterContext.HttpContext.Request.UrlReferrer;
                    var furl = String.Format("{0}/SetUp.php?code={1}&sName={2}&url={3}&return_url={4}&system_name={5}&redirect=false", genesysWidgetSettings.GenesysDesignUrl, code, siteName, submitUrl, returnUrl, genesysWidgetSettings.ExternalSystemName);
                    JavaScriptSerializer serializer = new JavaScriptSerializer();
                    jsonData data = null;
                    try
                    {
                        using (var w = new WebClient())
                        {
                            var json_data = w.DownloadString(furl);
                            data = serializer.Deserialize<jsonData>(json_data);
                        }
                    }
                    catch (Exception e)
                    {
                        var logger = EngineContext.Current.Resolve<ILogger>();
                        var _workContext = EngineContext.Current.Resolve<IWorkContext>();
                        var customer = _workContext.CurrentCustomer;
                        logger.Error("Error completing web request to genesys server during add to cart action", e, customer);
                    }
                    if (!String.IsNullOrEmpty(data.url))
                    {
                        JsonResult json = new JsonResult() { Data = new { redirect = data.url }, JsonRequestBehavior = JsonRequestBehavior.AllowGet };
                        filterContext.Result = json;
                    }
                    else
                    {
                        throw new Exception(data.error);
                    }
                }
            }
            else if (genesysWidgetSettings.ReturnTo == ReturnTo.ProductPage)
            {
                //TODO: Merge AddtoCart from ProductDetails filter and this into one function
                IShoppingCartService _shoppingService = EngineContext.Current.Resolve<IShoppingCartService>();
                IGenericAttributeService genericAtttributeService = EngineContext.Current.Resolve<IGenericAttributeService>();
                IWorkContext _workContext = EngineContext.Current.Resolve<IWorkContext>();
                IProductAttributeParser _productAttributeParser = EngineContext.Current.Resolve<IProductAttributeParser>();
                var cartType = ShoppingCartType.ShoppingCart;
                var productId = (int)filterContext.ActionParameters["productId"];
                var attributes = "";
                String orderId = HttpUtility.ParseQueryString(filterContext.HttpContext.Request.UrlReferrer.Query)["OrderDetails"];

                ProductAttributeMapping attr = new ProductAttributeMapping()
                {
                    Id = 0,
                    ProductId = productId,
                    ProductAttributeId = 0,
                    TextPrompt = "",
                    IsRequired = true,
                    AttributeControlTypeId = 1,
                    DisplayOrder = 0,
                    ValidationMinLength = 0,
                    ValidationMaxLength = 0,
                    ValidationFileAllowedExtensions = "",
                    ValidationFileMaximumSize = 0,
                    DefaultValue = "",
                };

                attributes = _productAttributeParser.AddProductAttribute("", attr, orderId);
                _shoppingService.AddToCart(_workContext.CurrentCustomer, _productService.GetProductById(productId), cartType, _storeContext.CurrentStore.Id, attributes, 0, null, null, 1, true);
                UrlHelper helper = new UrlHelper(filterContext.RequestContext, RouteTable.Routes);
                JsonResult json = new JsonResult() { Data = new { redirect = helper.RouteUrl("ShoppingCart", "", filterContext.HttpContext.Request.Url.Scheme) }, JsonRequestBehavior = JsonRequestBehavior.AllowGet };
                filterContext.Result = json;
            }
           
            base.OnActionExecuting(filterContext);
        }

        class jsonData
        {
            public string error { get; set; }
            public string errormessage { get; set; }
            public string sid { get; set; }
            public string url { get; set; }
        }
    }
}
