using System;
using System.Linq;
using System.Web.Mvc;
using Nop.Web.Framework.Controllers;
using Nop.Services.Catalog;
using Nop.Web.Models.Catalog;
using Nop.Core.Domain.Catalog;
using Nop.Web.Infrastructure.Cache;
using Nop.Services.Localization;
using Nop.Services.Seo;
using Nop.Web.Models.Media;
using Nop.Services.Media;
using Nop.Core.Caching;
using Nop.Core.Domain.Media;
using Nop.Core;
using Nop.Services.Security;
using Nop.Services.Tax;
using Nop.Services.Directory;
using Nop.Core.Domain.Customers;
using Nop.Services.Customers;
using System.Net;
using System.Web.Script.Serialization;
using System.Web;
using Nop.Plugin.Widgets.GeneSysWidget.Models;
using Nop.Services.Configuration;
using Nop.Core.Domain.Orders;
using Nop.Web.Models.ShoppingCart;
using Nop.Services.Orders;
using System.Text.RegularExpressions;
using Nop.Core.Infrastructure;
using Nop.Services.Logging;
using Nop.Web.Controllers;
using Nop.Web.Framework;
using Nop.Web.Framework.UI;
using Nop.Core.Domain.Common;

namespace Nop.Plugin.Widgets.GeneSysWidget.Controllers
{
    public class WidgetsGeneSysWidgetController : BaseNopController
    {

        #region Fields
        private readonly IProductService _productService;
        private readonly ICacheManager _cacheManager;
        private readonly IProductTemplateService _productTemplateService;
        private readonly MediaSettings _mediaSettings;
        private readonly IPictureService _pictureService;
        private readonly ILocalizationService _localizationService;
        private readonly CatalogSettings _catalogSettings;
        private readonly IWorkContext _workContext;
        private readonly IBackInStockSubscriptionService _backInStockSubscriptionService;
        private readonly IStoreContext _storeContext;
        private readonly IPermissionService _permissionService;
        private readonly ITaxService _taxService;
        private readonly IPriceCalculationService _priceCalculationService;
        private readonly ICurrencyService _currencyService;
        private readonly IPriceFormatter _priceFormatter;
        private readonly IProductAttributeService _productAttributeService;
        private readonly GeneSysWidgetSettings _genesysWidgetSettings;
        private readonly ISettingService _settingService;
        private readonly IOrderService _orderService;
        #endregion

        public WidgetsGeneSysWidgetController(ICategoryService categoryService,
            IManufacturerService manufacturerService, IProductService productService,
            IProductTemplateService productTemplateService,
            ICategoryTemplateService categoryTemplateService,
            IManufacturerTemplateService manufacturerTemplateService,
            IProductAttributeService productAttributeService, IProductAttributeParser productAttributeParser,
            IWorkContext workContext, IStoreContext storeContext,
            ITaxService taxService, ICurrencyService currencyService,
            IPictureService pictureService, ILocalizationService localizationService,
            IPriceCalculationService priceCalculationService, IPriceFormatter priceFormatter,
            IWebHelper webHelper, ISpecificationAttributeService specificationAttributeService,
            IRecentlyViewedProductsService recentlyViewedProductsService, ICompareProductsService compareProductsService,
            IProductTagService productTagService, IBackInStockSubscriptionService backInStockSubscriptionService, 
            IAclService aclService, IPermissionService permissionService, IDownloadService downloadService,
            MediaSettings mediaSettings, CatalogSettings catalogSettings,
            ICacheManager cacheManager, GeneSysWidgetSettings genesysWidgetSettings, ISettingService settingService, IOrderService orderService)
        {

            this._productService = productService;
            this._productTemplateService = productTemplateService;
            this._productAttributeService = productAttributeService;
            this._workContext = workContext;
            this._storeContext = storeContext;
            this._taxService = taxService;
            this._currencyService = currencyService;
            this._pictureService = pictureService;
            this._localizationService = localizationService;
            this._priceCalculationService = priceCalculationService;
            this._priceFormatter = priceFormatter;
            this._backInStockSubscriptionService = backInStockSubscriptionService;
            this._permissionService = permissionService;


            this._mediaSettings = mediaSettings;
            this._catalogSettings = catalogSettings;

            this._cacheManager = cacheManager;
            this._genesysWidgetSettings = genesysWidgetSettings;
            this._settingService = settingService;
            this._orderService = orderService;
        }

        #region Utils
        [NonAction]
        protected ProductDetailsModel PrepareProductModel(Product product)
        {
            if (product == null)
                throw new ArgumentNullException("product");

            var model = new ProductDetailsModel()
            {
                Id = product.Id,
                Name = product.GetLocalized(x => x.Name),
                ShortDescription = product.GetLocalized(x => x.ShortDescription),
                FullDescription = product.GetLocalized(x => x.FullDescription),
                MetaKeywords = product.GetLocalized(x => x.MetaKeywords),
                MetaDescription = product.GetLocalized(x => x.MetaDescription),
                MetaTitle = product.GetLocalized(x => x.MetaTitle),
                SeName = product.GetSeName(),
            };

            //template

            var templateCacheKey = string.Format(ModelCacheEventConsumer.PRODUCT_TEMPLATE_MODEL_KEY, product.ProductTemplateId);
            model.ProductTemplateViewPath = _cacheManager.Get(templateCacheKey, () =>
            {
                var template = _productTemplateService.GetProductTemplateById(product.ProductTemplateId);
                if (template == null)
                    template = _productTemplateService.GetAllProductTemplates().FirstOrDefault();
                if (template == null)
                    throw new Exception("No default template could be loaded");
                return template.ViewPath;
            });

            //pictures
            model.DefaultPictureZoomEnabled = _mediaSettings.DefaultPictureZoomEnabled;
            var pictures = _pictureService.GetPicturesByProductId(product.Id);
            if (pictures.Any())
            {
                //default picture
                model.DefaultPictureModel = new PictureModel()
                {
                    ImageUrl = _pictureService.GetPictureUrl(pictures.FirstOrDefault(), _mediaSettings.ProductDetailsPictureSize),
                    FullSizeImageUrl = _pictureService.GetPictureUrl(pictures.FirstOrDefault()),
                    Title = string.Format(_localizationService.GetResource("Media.Product.ImageLinkTitleFormat"), model.Name),
                    AlternateText = string.Format(_localizationService.GetResource("Media.Product.ImageAlternateTextFormat"), model.Name),
                };
                //all pictures
                foreach (var picture in pictures)
                {
                    model.PictureModels.Add(new PictureModel()
                    {
                        ImageUrl = _pictureService.GetPictureUrl(picture, _mediaSettings.ProductThumbPictureSizeOnProductDetailsPage),
                        FullSizeImageUrl = _pictureService.GetPictureUrl(picture),
                        Title = string.Format(_localizationService.GetResource("Media.Product.ImageLinkTitleFormat"), model.Name),
                        AlternateText = string.Format(_localizationService.GetResource("Media.Product.ImageAlternateTextFormat"), model.Name),
                    });
                }
            }
            else
            {
                //no images. set the default one
                model.DefaultPictureModel = new PictureModel()
                {
                    ImageUrl = _pictureService.GetDefaultPictureUrl(_mediaSettings.ProductDetailsPictureSize),
                    FullSizeImageUrl = _pictureService.GetDefaultPictureUrl(),
                    Title = string.Format(_localizationService.GetResource("Media.Product.ImageLinkTitleFormat"), model.Name),
                    AlternateText = string.Format(_localizationService.GetResource("Media.Product.ImageAlternateTextFormat"), model.Name),
                };
            }


            //product variants
            foreach (var variant in _productService.GetProductVariantsByProductId(product.Id))
                model.ProductVariantModels.Add(PrepareProductVariantModel(new ProductDetailsModel.ProductVariantModel(), variant));

            return model;
        }

        [NonAction]
        protected ProductDetailsModel.ProductVariantModel PrepareProductVariantModel(ProductDetailsModel.ProductVariantModel model, ProductVariant productVariant)
        {
            if (productVariant == null)
                throw new ArgumentNullException("productVariant");

            if (model == null)
                throw new ArgumentNullException("model");

            #region Properties

            model.Id = productVariant.Id;
            model.Name = productVariant.GetLocalized(x => x.Name);
            model.ShowSku = _catalogSettings.ShowProductSku;
            model.Sku = productVariant.Sku;
            model.Description = productVariant.GetLocalized(x => x.Description);
            model.ShowManufacturerPartNumber = _catalogSettings.ShowManufacturerPartNumber;
            model.ManufacturerPartNumber = productVariant.ManufacturerPartNumber;
            model.ShowGtin = _catalogSettings.ShowGtin;
            model.Gtin = productVariant.Gtin;
            model.StockAvailability = productVariant.FormatStockMessage(_localizationService);
            model.PictureModel.FullSizeImageUrl = _pictureService.GetPictureUrl(productVariant.PictureId, 0, false);
            model.PictureModel.ImageUrl = _pictureService.GetPictureUrl(productVariant.PictureId, _mediaSettings.ProductVariantPictureSize, false);
            model.PictureModel.Title = string.Format(_localizationService.GetResource("Media.Product.ImageLinkTitleFormat"), model.Name);
            model.PictureModel.AlternateText = string.Format(_localizationService.GetResource("Media.Product.ImageAlternateTextFormat"), model.Name);
            model.HasSampleDownload = productVariant.IsDownload && productVariant.HasSampleDownload;
            model.IsCurrentCustomerRegistered = _workContext.CurrentCustomer.IsRegistered();
            //back in stock subscriptions)
            if (productVariant.ManageInventoryMethod == ManageInventoryMethod.ManageStock &&
                productVariant.BackorderMode == BackorderMode.NoBackorders &&
                productVariant.AllowBackInStockSubscriptions &&
                productVariant.StockQuantity <= 0)
            {
                //out of stock
                model.DisplayBackInStockSubscription = true;
                model.BackInStockAlreadySubscribed = _backInStockSubscriptionService
                    .FindSubscription(_workContext.CurrentCustomer.Id, productVariant.Id, _storeContext.CurrentStore.Id) != null;
            }

            #endregion

            #region Product variant price
            model.ProductVariantPrice.ProductVariantId = productVariant.Id;
            model.ProductVariantPrice.DynamicPriceUpdate = _catalogSettings.EnableDynamicPriceUpdate;
            if (_permissionService.Authorize(StandardPermissionProvider.DisplayPrices))
            {
                model.ProductVariantPrice.HidePrices = false;
                if (productVariant.CustomerEntersPrice)
                {
                    model.ProductVariantPrice.CustomerEntersPrice = true;
                }
                else
                {
                    if (productVariant.CallForPrice)
                    {
                        model.ProductVariantPrice.CallForPrice = true;
                    }
                    else
                    {
                        decimal taxRate = decimal.Zero;
                        decimal oldPriceBase = _taxService.GetProductPrice(productVariant, productVariant.OldPrice, out taxRate);
                        decimal finalPriceWithoutDiscountBase = _taxService.GetProductPrice(productVariant, _priceCalculationService.GetFinalPrice(productVariant, false), out taxRate);
                        decimal finalPriceWithDiscountBase = _taxService.GetProductPrice(productVariant, _priceCalculationService.GetFinalPrice(productVariant, true), out taxRate);

                        decimal oldPrice = _currencyService.ConvertFromPrimaryStoreCurrency(oldPriceBase, _workContext.WorkingCurrency);
                        decimal finalPriceWithoutDiscount = _currencyService.ConvertFromPrimaryStoreCurrency(finalPriceWithoutDiscountBase, _workContext.WorkingCurrency);
                        decimal finalPriceWithDiscount = _currencyService.ConvertFromPrimaryStoreCurrency(finalPriceWithDiscountBase, _workContext.WorkingCurrency);

                        if (finalPriceWithoutDiscountBase != oldPriceBase && oldPriceBase > decimal.Zero)
                            model.ProductVariantPrice.OldPrice = _priceFormatter.FormatPrice(oldPrice);

                        model.ProductVariantPrice.Price = _priceFormatter.FormatPrice(finalPriceWithoutDiscount);

                        if (finalPriceWithoutDiscountBase != finalPriceWithDiscountBase)
                            model.ProductVariantPrice.PriceWithDiscount = _priceFormatter.FormatPrice(finalPriceWithDiscount);

                        model.ProductVariantPrice.PriceValue = finalPriceWithoutDiscount;
                        model.ProductVariantPrice.PriceWithDiscountValue = finalPriceWithDiscount;
                    }
                }
            }
            else
            {
                model.ProductVariantPrice.HidePrices = true;
                model.ProductVariantPrice.OldPrice = null;
                model.ProductVariantPrice.Price = null;
            }
            #endregion

            #region 'Add to cart' model

            model.AddToCart.ProductVariantId = productVariant.Id;

            //quantity
            model.AddToCart.EnteredQuantity = productVariant.OrderMinimumQuantity;

            //'add to cart', 'add to wishlist' buttons
            model.AddToCart.DisableBuyButton = productVariant.DisableBuyButton || !_permissionService.Authorize(StandardPermissionProvider.EnableShoppingCart);
            model.AddToCart.DisableWishlistButton = productVariant.DisableWishlistButton || !_permissionService.Authorize(StandardPermissionProvider.EnableWishlist);
            if (!_permissionService.Authorize(StandardPermissionProvider.DisplayPrices))
            {
                model.AddToCart.DisableBuyButton = true;
                model.AddToCart.DisableWishlistButton = true;
            }
            //pre-order
            model.AddToCart.AvailableForPreOrder = productVariant.AvailableForPreOrder;

            //customer entered price
            model.AddToCart.CustomerEntersPrice = productVariant.CustomerEntersPrice;
            if (model.AddToCart.CustomerEntersPrice)
            {
                decimal minimumCustomerEnteredPrice = _currencyService.ConvertFromPrimaryStoreCurrency(productVariant.MinimumCustomerEnteredPrice, _workContext.WorkingCurrency);
                decimal maximumCustomerEnteredPrice = _currencyService.ConvertFromPrimaryStoreCurrency(productVariant.MaximumCustomerEnteredPrice, _workContext.WorkingCurrency);

                model.AddToCart.CustomerEnteredPrice = minimumCustomerEnteredPrice;
                model.AddToCart.CustomerEnteredPriceRange = string.Format(_localizationService.GetResource("Products.EnterProductPrice.Range"),
                    _priceFormatter.FormatPrice(minimumCustomerEnteredPrice, false, false),
                    _priceFormatter.FormatPrice(maximumCustomerEnteredPrice, false, false));
            }
            //allowed quantities
            var allowedQuantities = productVariant.ParseAllowedQuatities();
            foreach (var qty in allowedQuantities)
            {
                model.AddToCart.AllowedQuantities.Add(new SelectListItem()
                {
                    Text = qty.ToString(),
                    Value = qty.ToString()
                });
            }

            #endregion

            #region Gift card

            model.GiftCard.IsGiftCard = productVariant.IsGiftCard;
            if (model.GiftCard.IsGiftCard)
            {
                model.GiftCard.GiftCardType = productVariant.GiftCardType;
                model.GiftCard.SenderName = _workContext.CurrentCustomer.GetFullName();
                model.GiftCard.SenderEmail = _workContext.CurrentCustomer.Email;
            }

            #endregion

            #region Product attributes

            var productVariantAttributes = _productAttributeService.GetProductVariantAttributesByProductVariantId(productVariant.Id);
            foreach (var attribute in productVariantAttributes)
            {
                var pvaModel = new ProductDetailsModel.ProductVariantModel.ProductVariantAttributeModel()
                {
                    Id = attribute.Id,
                    ProductVariantId = productVariant.Id,
                    ProductAttributeId = attribute.ProductAttributeId,
                    Name = attribute.ProductAttribute.GetLocalized(x => x.Name),
                    Description = attribute.ProductAttribute.GetLocalized(x => x.Description),
                    TextPrompt = attribute.TextPrompt,
                    IsRequired = attribute.IsRequired,
                    AttributeControlType = attribute.AttributeControlType,
                    AllowedFileExtensions = _catalogSettings.FileUploadAllowedExtensions,
                };

                if (attribute.ShouldHaveValues())
                {
                    //values
                    var pvaValues = _productAttributeService.GetProductVariantAttributeValues(attribute.Id);
                    foreach (var pvaValue in pvaValues)
                    {
                        var pvaValueModel = new ProductDetailsModel.ProductVariantModel.ProductVariantAttributeValueModel()
                        {
                            Id = pvaValue.Id,
                            Name = pvaValue.GetLocalized(x => x.Name),
                            ColorSquaresRgb = pvaValue.ColorSquaresRgb, //used with "Color squares" attribute type
                            IsPreSelected = pvaValue.IsPreSelected,
                        };
                        pvaModel.Values.Add(pvaValueModel);

                        //display price if allowed
                        if (_permissionService.Authorize(StandardPermissionProvider.DisplayPrices))
                        {
                            decimal taxRate = decimal.Zero;
                            decimal priceAdjustmentBase = _taxService.GetProductPrice(productVariant, pvaValue.PriceAdjustment, out taxRate);
                            decimal priceAdjustment = _currencyService.ConvertFromPrimaryStoreCurrency(priceAdjustmentBase, _workContext.WorkingCurrency);
                            if (priceAdjustmentBase > decimal.Zero)
                                pvaValueModel.PriceAdjustment = "+" + _priceFormatter.FormatPrice(priceAdjustment, false, false);
                            else if (priceAdjustmentBase < decimal.Zero)
                                pvaValueModel.PriceAdjustment = "-" + _priceFormatter.FormatPrice(-priceAdjustment, false, false);

                            pvaValueModel.PriceAdjustmentValue = priceAdjustment;
                        }
                    }
                }

                model.ProductVariantAttributes.Add(pvaModel);
            }

            #endregion

            return model;
        }

        private ProductDetailsModel getProductModel(int? productId, int? productVariantId)
        {
            ProductDetailsModel model = null;
            if (productVariantId.HasValue)
            {
                productId = _productService.GetProductVariantById(productVariantId.Value).ProductId;
            }
            if (productId.HasValue)
            {
                var product = _productService.GetProductById(productId.Value);
                model = PrepareProductModel(product);
            }
            return model;
        }

        private ViewResult getProductPicture(ProductDetailsModel model, string picLocation)
        {
            var pageDesignID = Request.QueryString["OrderId"];
            if (!String.IsNullOrEmpty(picLocation) && picLocation.Contains("Order Id:"))
            {
                char[] delimiterChars = { ':' };
                String[] pieces = picLocation.Split(delimiterChars);
                String designID = pieces[1].Trim();
                model.DefaultPictureModel.ImageUrl = String.Format("http://{0}/design_part/get_image.php?thumbnail=false&id={1}", _genesysWidgetSettings.GeneSysUrl, designID);
            }
            else if (!String.IsNullOrEmpty(pageDesignID))
            {
                model.DefaultPictureModel.ImageUrl = String.Format("http://{0}/design_part/get_image.php?thumbnail=false&id={1}", _genesysWidgetSettings.GeneSysUrl, pageDesignID.Replace("thumbs", "original"));
            }
            return View("Nop.Plugin.Widgets.GeneSysWidget.Views.GeneSysWidget.Picture", model);
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
            data.country = address.Country.Name;
            return data;
        }

        private ViewResult checkoutUpdate(int orderId)
        {
            var order = _orderService.GetOrderById(orderId);
            var variants = order.OrderProductVariants;
            int i = 0;
            JavaScriptSerializer serializer = new JavaScriptSerializer();
            foreach (var variant in variants)
            {
                if (variant.AttributeDescription.Contains("Order Id:"))
                {
                    char[] delimiterChars = { ':' };
                    String[] pieces = variant.AttributeDescription.Split(delimiterChars);
                    String designID = pieces[1].Trim();
                    designID = Regex.Match(designID, @"\d+").Value;
                    
                    var parameters = "externalOrderId=" + ((order.Id * 1000) + i) + "&orderItemId=" + designID + "&shippingInfo=" + serializer.Serialize(getShippingData(order.ShippingAddress));
                    string URI = "http://" + _genesysWidgetSettings.GeneSysUrl + "/services/update_order_item.php?confirm=true&" + parameters;
                    string resp;
                    try
                    {
                        using (WebClient wc = new WebClient())
                        {
                            wc.Headers[HttpRequestHeader.ContentType] = "application/x-www-form-urlencoded";
                            resp = wc.DownloadString(URI);
                        }
                        
                        jsonPurchaseData data = serializer.Deserialize<jsonPurchaseData>(resp);
                        if (data.errorCode != 0)
                        {
                            var logger = EngineContext.Current.Resolve<ILogger>();
                            var customer = _workContext.CurrentCustomer;
                            logger.Error(data.errorMessage, null, customer);
                        }
                    }
                    catch (Exception e)
                    {
                        var logger = EngineContext.Current.Resolve<ILogger>();
                        var customer = _workContext.CurrentCustomer;
                        logger.Error("Error completing web request to genesys server", e, customer);
                        this.AddNotification(NotifyType.Error, "There was an error completing your purchase, please contact customer support.", true);
                    }
                    i++;
                }
            }

            return null;
        }

        #endregion

        [AdminAuthorize]
        [ChildActionOnly]
        public ActionResult Configure()
        {
            if (!_permissionService.Authorize(StandardPermissionProvider.ManageExternalAuthenticationMethods))
                return Content("Access denied");

            var model = new ConfigurationModel();
           // model.ReturnToID = Convert.ToInt32(_genesysWidgetSettings.ReturnTo);
            model.GeneSysUrl = _genesysWidgetSettings.GeneSysUrl;
            model.SiteName = _genesysWidgetSettings.SiteName;
            //model.ReturnToValues = _genesysWidgetSettings.ReturnTo.ToSelectList();

            return View("Nop.Plugin.Widgets.GeneSysWidget.Views.GeneSysWidget.Configure", model);
        }

        [HttpPost]
        [AdminAuthorize]
        [ChildActionOnly]
        public ActionResult Configure(ConfigurationModel model)
        {
            if (!_permissionService.Authorize(StandardPermissionProvider.ManageExternalAuthenticationMethods))
                return Content("Access denied");

            if (!ModelState.IsValid)
                return Configure();

            //_genesysWidgetSettings.ReturnTo = (ReturnTo)model.ReturnToID;
            _genesysWidgetSettings.GeneSysUrl = model.GeneSysUrl;
            _genesysWidgetSettings.SiteName = model.SiteName;
            _settingService.SaveSetting(_genesysWidgetSettings);

           // model.ReturnToValues = _genesysWidgetSettings.ReturnTo.ToSelectList();

            return View("Nop.Plugin.Widgets.GeneSysWidget.Views.GeneSysWidget.Configure", model);
        }

               

        [ChildActionOnly]
        public ActionResult PublicInfo(string widgetZone, int? productId, string picLocation, int? productVariantId, int? orderId)
        {
            if (widgetZone == "add_to_cart")
            {
                ProductDetailsModel model = getProductModel(productId, productVariantId);
                return View("Nop.Plugin.Widgets.GeneSysWidget.Views.GeneSysWidget.Product", model);
            }
            else if (widgetZone == "shopping_cart_pic" || widgetZone == "admin_pic" || widgetZone == "order_details_image")
            {
                ProductDetailsModel model = getProductModel(productId, productVariantId);
                return getProductPicture(model, picLocation);
            }
            else if (widgetZone == "checkout_completed_top")
            {
                if (orderId.HasValue)
                {
                    return checkoutUpdate(orderId.Value);
                }
                else
                {
                    var logger = EngineContext.Current.Resolve<ILogger>();
                    var customer = _workContext.CurrentCustomer;
                    logger.Error("Error, checkout tried to complete but an order id was not found, cannot notify genesys", null, customer);
                    this.AddNotification(NotifyType.Error, "There was an error completing your purchase, please contact customer support.", true);
                    return null;
                }
            }
            else if (widgetZone == "productdetails_before_pictures")
            {
                ProductDetailsModel model = getProductModel(productId, productVariantId);
                return getProductPicture(model, "");
            }
            else
            {
                var logger = EngineContext.Current.Resolve<ILogger>();
                var customer = _workContext.CurrentCustomer;
                logger.Error("Genesys plugin was called but could not serve a view", null, customer);
                return null;
            }
        }

        public ActionResult Personalize(int productId)
        {
            var product = _productService.GetProductById(productId);
            ProductDetailsModel model = PrepareProductModel(product);
            var defaultProductVariant = model.ProductVariantModels.Count > 0 ? model.ProductVariantModels[0] : null;
            var designID = Request.QueryString["OrderId"];
            var code = defaultProductVariant.Sku;
            var siteName = HttpUtility.UrlEncode(_genesysWidgetSettings.SiteName);
            string returnUrl;
            if (_genesysWidgetSettings.ReturnTo == ReturnTo.Cart)
            {
                returnUrl = HttpUtility.UrlEncode(Url.RouteUrl("AddProductVariantToCart", new { productVariantId = defaultProductVariant.Id, shoppingCartTypeId = 1, forceredirection = true }, this.Request.Url.Scheme));
            }
            else
            {
                returnUrl = HttpUtility.UrlEncode(Url.RouteUrl("Product", new { SeName = model.SeName }, this.Request.Url.Scheme));
            }
            var furl = String.Format("http://{0}/SetUp.php?code={1}&sName={2}&url={3}&return_url={3}&system_name=NOP&redirect=false", _genesysWidgetSettings.GeneSysUrl, code, siteName, returnUrl);
            JavaScriptSerializer serializer = new JavaScriptSerializer();
            jsonData data;
            using (var w = new WebClient())
            {
                var json_data = w.DownloadString(furl);
                data = serializer.Deserialize<jsonData>(json_data);
            }
            if (!String.IsNullOrEmpty(data.url))
            {
                return Redirect(data.url);
            }
            else
            {
                throw new Exception(data.error);
            }
        }

        //Hackish function to make it so if there is only 1 product we go directly to genesys
        public ActionResult PersonalizeOnly()
        {
            var products = _productService.SearchProducts();
            if (products.Count == 1 && products[0].Published)
            {
                return Personalize(products[0].Id);
            }
            else
            {
                this.ErrorNotification("Error finding product", true);
                return Redirect(Request.UrlReferrer.ToString());
            }
            
        }
    }

    class jsonShipData
    {
        public string first_name { get; set; }
        public string last_name { get; set; }
        public string address_1 { get; set; }
        public string address_2 { get; set; }
        public string city { get; set; }
        public string state_province { get; set; }
        public string zip_postal_code { get; set; }
        public string country { get; set; }
    }

    class jsonData
    {
        public string error { get; set; }
        public string errormessage { get; set; }
        public string sid { get; set; }
        public string url { get; set; }
    }

    class jsonPurchaseData
    {
        public int errorCode { get; set; }
        public string errorMessage { get; set; }
    }
}
