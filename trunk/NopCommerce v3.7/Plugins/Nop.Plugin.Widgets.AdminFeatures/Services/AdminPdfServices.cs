using iTextSharp.text;
using iTextSharp.text.pdf;
using Nop.Core;
using Nop.Core.Domain.Common;
using Nop.Core.Domain.Localization;
using Nop.Core.Domain.Orders;
using Nop.Services.Localization;
using Nop.Services.Payments;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using Nop.Services.Orders;
using Nop.Services.Helpers;
using Nop.Services.Configuration;
using Nop.Plugin.Widgets.GeneSysWidget;
using System.Globalization;
using System.Web;
using Nop.Services.Catalog;
using Nop.Services.Directory;
using Nop.Services.Media;
using Nop.Services.Stores;
using Nop.Core.Domain.Catalog;
using Nop.Core.Domain.Directory;
using Nop.Core.Domain.Tax;
using Nop.Core.Html;
using Nop.Core.Domain.Shipping;
using Nop.Services.Logging;
using System.Web.Script.Serialization;
using Nop.Services.Common;

namespace Nop.Plugin.Widgets.AdminFeatures.Services
{
    class AdminPdfServices : Nop.Services.Common.PdfService
    {

        private readonly IWebHelper _webHelper;
        private readonly PdfSettings _pdfSettings;
        private readonly ILocalizationService _localizationService;
        private readonly ILanguageService _languageService;
        private readonly IWorkContext _workContext;
        private readonly AddressSettings _addressSettings;
        private readonly IPaymentService _paymentService;
        private readonly IDateTimeHelper _dateTimeHelper;
        private readonly GeneSysWidgetSettings _genesysSettings;
        private readonly IOrderService _orderService;
        private readonly IPriceFormatter _priceFormatter;
        private readonly ICurrencyService _currencyService;
        private readonly IMeasureService _measureService;
        private readonly IPictureService _pictureService;
        private readonly IProductService _productService;
        private readonly IProductAttributeParser _productAttributeParser;
        private readonly IStoreService _storeService;
        private readonly ISettingService _settingContext;
        private readonly IStoreContext _storeContext;
        private readonly ILogger _logger;
        private readonly CatalogSettings _catalogSettings;
        private readonly CurrencySettings _currencySettings;
        private readonly MeasureSettings _measureSettings;
        private readonly TaxSettings _taxSettings;

        public AdminPdfServices(ILocalizationService localizationService,
            ILanguageService languageService,
            IWorkContext workContext,
            IOrderService orderService,
            IPaymentService paymentService,
            IDateTimeHelper dateTimeHelper,
            IPriceFormatter priceFormatter,
            ICurrencyService currencyService,
            IMeasureService measureService,
            IPictureService pictureService,
            IProductService productService,
            IProductAttributeParser productAttributeParser,
            IStoreService storeService,
            IStoreContext storeContext,
            ISettingService settingContext,
            IWebHelper webHelper,
            IAddressAttributeFormatter addressAttributeFormatter,
            CatalogSettings catalogSettings,
            CurrencySettings currencySettings,
            MeasureSettings measureSettings,
            PdfSettings pdfSettings,
            TaxSettings taxSettings,
            AddressSettings addressSettings,
            GeneSysWidgetSettings genesysSettings, ILogger logger) : base(localizationService, 
            languageService,
            workContext,
            orderService,
            paymentService,
            dateTimeHelper,
            priceFormatter,
            currencyService, 
            measureService,
            pictureService,
            productService, 
            productAttributeParser,
            storeService,
            storeContext,
            settingContext,
            webHelper,
            addressAttributeFormatter,
            catalogSettings, 
            currencySettings,
            measureSettings,
            pdfSettings,
            taxSettings,
            addressSettings)
        {
            
            this._genesysSettings = genesysSettings;
            this._localizationService = localizationService;
            this._languageService = languageService;
            this._workContext = workContext;
            this._orderService = orderService;
            this._paymentService = paymentService;
            this._dateTimeHelper = dateTimeHelper;
            this._priceFormatter = priceFormatter;
            this._currencyService = currencyService;
            this._measureService = measureService;
            this._pictureService = pictureService;
            this._productService = productService;
            this._productAttributeParser = productAttributeParser;
            this._storeService = storeService;
            this._storeContext = storeContext;
            this._webHelper = webHelper;
            this._settingContext = settingContext;
            this._currencySettings = currencySettings;
            this._catalogSettings = catalogSettings;
            this._measureSettings = measureSettings;
            this._pdfSettings = pdfSettings;
            this._taxSettings = taxSettings;
            this._addressSettings = addressSettings;
            this._logger = logger;
        }

        /// <summary>
        /// Print an order to PDF
        /// </summary>
        /// <param name="stream">Stream</param>
        /// <param name="orders">Orders</param>
        /// <param name="languageId">Language identifier; 0 to use a language used when placing an order</param>
        public override void PrintOrdersToPdf(Stream stream, IList<Order> orders, int languageId = 0)
        {
            if (stream == null)
                throw new ArgumentNullException("stream");

            if (orders == null)
                throw new ArgumentNullException("orders");

            var lang = _languageService.GetLanguageById(languageId);
            if (lang == null)
                throw new ArgumentException(string.Format("Cannot load language. ID={0}", languageId));

            var pageSize = PageSize.A4;

            if (_pdfSettings.LetterPageSizeEnabled)
            {
                pageSize = PageSize.LETTER;
            }


            var doc = new Document(pageSize);
            PdfWriter.GetInstance(doc, stream);
            doc.Open();

            //fonts
            var titleFont = GetFont();
            titleFont.SetStyle(Font.BOLD);
            titleFont.Color = BaseColor.BLACK;
            var font = GetFont();
            var attributesFont = GetFont();
            attributesFont.SetStyle(Font.ITALIC);

            int ordCount = orders.Count;
            int ordNum = 0;

            String v3url =  _genesysSettings.GenesysDesignUrl + "/design_part/get_image.php?thumbnail=true&id=";

            foreach (var order in orders)
            {
                if (languageId == 0)
                {
                    lang = _languageService.GetLanguageById(order.CustomerLanguageId);
                    if (lang == null || !lang.Published)
                        lang = _workContext.WorkingLanguage;
                }

                #region Header

                //logo
                var logoPicture = _pictureService.GetPictureById(_pdfSettings.LogoPictureId);
                var logoExists = logoPicture != null;

                //header
                var headerTable = new PdfPTable(logoExists ? 2 : 1);
                headerTable.WidthPercentage = 100f;
                if (logoExists)
                    headerTable.SetWidths(new[] { 50, 50 });

                //logo
                if (logoExists)
                {
                    var logoFilePath = _pictureService.GetThumbLocalPath(logoPicture, 0, false);
                    var cellLogo = new PdfPCell(Image.GetInstance(logoFilePath));
                    cellLogo.Border = Rectangle.NO_BORDER;
                    headerTable.AddCell(cellLogo);
                }
                //store info
                var cell = new PdfPCell();
                cell.Border = Rectangle.NO_BORDER;
                cell.AddElement(new Paragraph(String.Format(_localizationService.GetResource("PDFInvoice.Order#", lang.Id), order.Id), titleFont));
                var store = _storeService.GetStoreById(order.StoreId) ?? _storeContext.CurrentStore;
                var anchor = new Anchor(store.Url.Trim(new char[] { '/' }), font);
                anchor.Reference = store.Url;
                cell.AddElement(new Paragraph(anchor));
                cell.AddElement(new Paragraph(String.Format(_localizationService.GetResource("PDFInvoice.OrderDate", lang.Id), _dateTimeHelper.ConvertToUserTime(order.CreatedOnUtc, DateTimeKind.Utc).ToString("D", new CultureInfo(lang.LanguageCulture))), font));
                headerTable.AddCell(cell);
                doc.Add(headerTable);

                #endregion

                #region Addresses

                var addressTable = new PdfPTable(2);
                addressTable.WidthPercentage = 100f;
                addressTable.SetWidths(new[] { 50, 50 });

                //billing info
                cell = new PdfPCell();
                cell.Border = Rectangle.NO_BORDER;
                cell.AddElement(new Paragraph(_localizationService.GetResource("PDFInvoice.BillingInformation", lang.Id), titleFont));

                if (_addressSettings.CompanyEnabled && !String.IsNullOrEmpty(order.BillingAddress.Company))
                    cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.Company", lang.Id), order.BillingAddress.Company), font));

                cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.Name", lang.Id), order.BillingAddress.FirstName + " " + order.BillingAddress.LastName), font));
                if (_addressSettings.PhoneEnabled)
                    cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.Phone", lang.Id), order.BillingAddress.PhoneNumber), font));
                if (_addressSettings.FaxEnabled && !String.IsNullOrEmpty(order.BillingAddress.FaxNumber))
                    cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.Fax", lang.Id), order.BillingAddress.FaxNumber), font));
                if (_addressSettings.StreetAddressEnabled)
                    cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.Address", lang.Id), order.BillingAddress.Address1), font));
                if (_addressSettings.StreetAddress2Enabled && !String.IsNullOrEmpty(order.BillingAddress.Address2))
                    cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.Address2", lang.Id), order.BillingAddress.Address2), font));
                if (_addressSettings.CityEnabled || _addressSettings.StateProvinceEnabled || _addressSettings.ZipPostalCodeEnabled)
                    cell.AddElement(new Paragraph("   " + String.Format("{0}, {1} {2}", order.BillingAddress.City, order.BillingAddress.StateProvince != null ? order.BillingAddress.StateProvince.GetLocalized(x => x.Name, lang.Id) : "", order.BillingAddress.ZipPostalCode), font));
                if (_addressSettings.CountryEnabled && order.BillingAddress.Country != null)
                    cell.AddElement(new Paragraph("   " + String.Format("{0}", order.BillingAddress.Country != null ? order.BillingAddress.Country.GetLocalized(x => x.Name, lang.Id) : ""), font));

                //VAT number
                if (!String.IsNullOrEmpty(order.VatNumber))
                    cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.VATNumber", lang.Id), order.VatNumber), font));

                //payment method
                var paymentMethod = _paymentService.LoadPaymentMethodBySystemName(order.PaymentMethodSystemName);
                string paymentMethodStr = paymentMethod != null ? paymentMethod.GetLocalizedFriendlyName(_localizationService, lang.Id) : order.PaymentMethodSystemName;
                if (!String.IsNullOrEmpty(paymentMethodStr))
                {
                    cell.AddElement(new Paragraph(" "));
                    cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.PaymentMethod", lang.Id), paymentMethodStr), font));
                    cell.AddElement(new Paragraph());
                }

                addressTable.AddCell(cell);

                //shipping info
                if (order.ShippingStatus != ShippingStatus.ShippingNotRequired)
                {
                    if (order.ShippingAddress == null)
                        throw new NopException(string.Format("Shipping is required, but address is not available. Order ID = {0}", order.Id));
                    cell = new PdfPCell();
                    cell.Border = Rectangle.NO_BORDER;

                    cell.AddElement(new Paragraph(_localizationService.GetResource("PDFInvoice.ShippingInformation", lang.Id), titleFont));
                    if (!String.IsNullOrEmpty(order.ShippingAddress.Company))
                        cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.Company", lang.Id), order.ShippingAddress.Company), font));
                    cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.Name", lang.Id), order.ShippingAddress.FirstName + " " + order.ShippingAddress.LastName), font));
                    if (_addressSettings.PhoneEnabled)
                        cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.Phone", lang.Id), order.ShippingAddress.PhoneNumber), font));
                    if (_addressSettings.FaxEnabled && !String.IsNullOrEmpty(order.ShippingAddress.FaxNumber))
                        cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.Fax", lang.Id), order.ShippingAddress.FaxNumber), font));
                    if (_addressSettings.StreetAddressEnabled)
                        cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.Address", lang.Id), order.ShippingAddress.Address1), font));
                    if (_addressSettings.StreetAddress2Enabled && !String.IsNullOrEmpty(order.ShippingAddress.Address2))
                        cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.Address2", lang.Id), order.ShippingAddress.Address2), font));
                    if (_addressSettings.CityEnabled || _addressSettings.StateProvinceEnabled || _addressSettings.ZipPostalCodeEnabled)
                        cell.AddElement(new Paragraph("   " + String.Format("{0}, {1} {2}", order.ShippingAddress.City, order.ShippingAddress.StateProvince != null ? order.ShippingAddress.StateProvince.GetLocalized(x => x.Name, lang.Id) : "", order.ShippingAddress.ZipPostalCode), font));
                    if (_addressSettings.CountryEnabled && order.ShippingAddress.Country != null)
                        cell.AddElement(new Paragraph("   " + String.Format("{0}", order.ShippingAddress.Country != null ? order.ShippingAddress.Country.GetLocalized(x => x.Name, lang.Id) : ""), font));
                    cell.AddElement(new Paragraph(" "));
                    cell.AddElement(new Paragraph("   " + String.Format(_localizationService.GetResource("PDFInvoice.ShippingMethod", lang.Id), order.ShippingMethod), font));
                    cell.AddElement(new Paragraph());

                    addressTable.AddCell(cell);
                }
                else
                {
                    cell = new PdfPCell(new Phrase(" "));
                    cell.Border = Rectangle.NO_BORDER;
                    addressTable.AddCell(cell);
                }

                doc.Add(addressTable);
                doc.Add(new Paragraph(" "));

                #endregion

                #region Products
                //products
                doc.Add(new Paragraph(_localizationService.GetResource("PDFInvoice.Product(s)", lang.Id), titleFont));
                doc.Add(new Paragraph(" "));


                var orderProductVariants = _orderService.GetAllOrderItems(order.Id, null, null, null, null, null, null);

                var productsTable = new PdfPTable(_catalogSettings.ShowProductSku ? 5 : 4);
                productsTable.WidthPercentage = 100f;
                productsTable.SetWidths(_catalogSettings.ShowProductSku ? new[] { 40, 15, 15, 15, 15 } : new[] { 40, 20, 20, 20 });

                //product name
                cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFInvoice.ProductName", lang.Id), font));
                cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                cell.HorizontalAlignment = Element.ALIGN_CENTER;
                productsTable.AddCell(cell);

                //SKU
                if (_catalogSettings.ShowProductSku)
                {
                    cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFInvoice.SKU", lang.Id), font));
                    cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                    cell.HorizontalAlignment = Element.ALIGN_CENTER;
                    productsTable.AddCell(cell);
                }

                //price
                cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFInvoice.ProductPrice", lang.Id), font));
                cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                cell.HorizontalAlignment = Element.ALIGN_CENTER;
                productsTable.AddCell(cell);

                //qty
                cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFInvoice.ProductQuantity", lang.Id), font));
                cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                cell.HorizontalAlignment = Element.ALIGN_CENTER;
                productsTable.AddCell(cell);

                //total
                cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFInvoice.ProductTotal", lang.Id), font));
                cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                cell.HorizontalAlignment = Element.ALIGN_CENTER;
                productsTable.AddCell(cell);

                for (int i = 0; i < orderProductVariants.Count; i++)
                {
                    var orderProductVariant = orderProductVariants[i];
                    var pv = orderProductVariant.Product;

                    //product name
                    string name = "";
                    if (!String.IsNullOrEmpty(pv.GetLocalized(x => x.Name, lang.Id)))
                        name = string.Format("{0} ({1})", pv.GetLocalized(x => x.Name, lang.Id), pv.GetLocalized(x => x.Name, lang.Id));
                    else
                        name = pv.GetLocalized(x => x.Name, lang.Id);
                    cell = new PdfPCell();
                    cell.AddElement(new Paragraph(name, font));
                    cell.HorizontalAlignment = Element.ALIGN_LEFT;

                    if (!String.IsNullOrEmpty(orderProductVariant.AttributesXml))
                    {
                        string attrValue = _productAttributeParser.ParseValues(orderProductVariant.AttributesXml, 0).FirstOrDefault();

                        if (attrValue != null)
                        {
                            GeneSysWidget.Helpers.OrderDataHelper.OrderDetails details = GeneSysWidget.Helpers.OrderDataHelper.getOrderDetails(attrValue);
                            iTextSharp.text.Image productImage = null;
                            try
                            {
                                productImage = iTextSharp.text.Image.GetInstance(new Uri(v3url + details.imageId_S));
                                productImage.SpacingBefore = 5;
                                productImage.ScaleToFit(productImage.Width, productImage.Height);
                                cell.AddElement(productImage);
                            }
                            catch (Exception e)
                            {
                                _logger.Error("Couldn't get image from genesys for design " + details.orderItemId, e);
                            }
                        }
                        else
                        {
                            var attributesParagraph = new Paragraph(HtmlHelper.ConvertHtmlToPlainText(orderProductVariant.AttributeDescription, true, true), attributesFont);
                            cell.AddElement(attributesParagraph);
                        }
                    }

                    productsTable.AddCell(cell);

                    //SKU
                    if (_catalogSettings.ShowProductSku)
                    {
                        var sku = pv.FormatSku(orderProductVariant.AttributesXml, _productAttributeParser);
                        cell = new PdfPCell(new Phrase(sku ?? String.Empty, font));
                        cell.HorizontalAlignment = Element.ALIGN_CENTER;
                        productsTable.AddCell(cell);
                    }

                    //price
                    string unitPrice = string.Empty;
                    switch (order.CustomerTaxDisplayType)
                    {
                        case TaxDisplayType.ExcludingTax:
                            {
                                var opvUnitPriceExclTaxInCustomerCurrency = _currencyService.ConvertCurrency(orderProductVariant.UnitPriceExclTax, order.CurrencyRate);
                                unitPrice = _priceFormatter.FormatPrice(opvUnitPriceExclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, false);
                            }
                            break;
                        case TaxDisplayType.IncludingTax:
                            {
                                var opvUnitPriceInclTaxInCustomerCurrency = _currencyService.ConvertCurrency(orderProductVariant.UnitPriceInclTax, order.CurrencyRate);
                                unitPrice = _priceFormatter.FormatPrice(opvUnitPriceInclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, true);
                            }
                            break;
                    }
                    cell = new PdfPCell(new Phrase(unitPrice, font));
                    cell.HorizontalAlignment = Element.ALIGN_LEFT;
                    productsTable.AddCell(cell);

                    //qty
                    cell = new PdfPCell(new Phrase(orderProductVariant.Quantity.ToString(), font));
                    cell.HorizontalAlignment = Element.ALIGN_LEFT;
                    productsTable.AddCell(cell);

                    //total
                    string subTotal = string.Empty;
                    switch (order.CustomerTaxDisplayType)
                    {
                        case TaxDisplayType.ExcludingTax:
                            {
                                var opvPriceExclTaxInCustomerCurrency = _currencyService.ConvertCurrency(orderProductVariant.PriceExclTax, order.CurrencyRate);
                                subTotal = _priceFormatter.FormatPrice(opvPriceExclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, false);
                            }
                            break;
                        case TaxDisplayType.IncludingTax:
                            {
                                var opvPriceInclTaxInCustomerCurrency = _currencyService.ConvertCurrency(orderProductVariant.PriceInclTax, order.CurrencyRate);
                                subTotal = _priceFormatter.FormatPrice(opvPriceInclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, true);
                            }
                            break;
                    }
                    cell = new PdfPCell(new Phrase(subTotal, font));
                    cell.HorizontalAlignment = Element.ALIGN_LEFT;
                    productsTable.AddCell(cell);
                }
                doc.Add(productsTable);

                #endregion

                #region Checkout attributes

                if (!String.IsNullOrEmpty(order.CheckoutAttributeDescription))
                {
                    doc.Add(new Paragraph(" "));
                    string attributes = HtmlHelper.ConvertHtmlToPlainText(order.CheckoutAttributeDescription, true, true);
                    var pCheckoutAttributes = new Paragraph(attributes, font);
                    pCheckoutAttributes.Alignment = Element.ALIGN_RIGHT;
                    doc.Add(pCheckoutAttributes);
                    doc.Add(new Paragraph(" "));
                }

                #endregion

                #region Totals

                //subtotal
                doc.Add(new Paragraph(" "));
                switch (order.CustomerTaxDisplayType)
                {
                    case TaxDisplayType.ExcludingTax:
                        {
                            var orderSubtotalExclTaxInCustomerCurrency = _currencyService.ConvertCurrency(order.OrderSubtotalExclTax, order.CurrencyRate);
                            string orderSubtotalExclTaxStr = _priceFormatter.FormatPrice(orderSubtotalExclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, false);

                            var p = new Paragraph(String.Format("{0} {1}", _localizationService.GetResource("PDFInvoice.Sub-Total", lang.Id), orderSubtotalExclTaxStr), font);
                            p.Alignment = Element.ALIGN_RIGHT;
                            doc.Add(p);
                        }
                        break;
                    case TaxDisplayType.IncludingTax:
                        {
                            var orderSubtotalInclTaxInCustomerCurrency = _currencyService.ConvertCurrency(order.OrderSubtotalInclTax, order.CurrencyRate);
                            string orderSubtotalInclTaxStr = _priceFormatter.FormatPrice(orderSubtotalInclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, true);

                            var p = new Paragraph(String.Format("{0} {1}", _localizationService.GetResource("PDFInvoice.Sub-Total", lang.Id), orderSubtotalInclTaxStr), font);
                            p.Alignment = Element.ALIGN_RIGHT;
                            doc.Add(p);
                        }
                        break;
                }
                //discount (applied to order subtotal)
                if (order.OrderSubTotalDiscountExclTax > decimal.Zero)
                {
                    switch (order.CustomerTaxDisplayType)
                    {
                        case TaxDisplayType.ExcludingTax:
                            {
                                var orderSubTotalDiscountExclTaxInCustomerCurrency = _currencyService.ConvertCurrency(order.OrderSubTotalDiscountExclTax, order.CurrencyRate);
                                string orderSubTotalDiscountInCustomerCurrencyStr = _priceFormatter.FormatPrice(-orderSubTotalDiscountExclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, false);

                                var p = new Paragraph(String.Format("{0} {1}", _localizationService.GetResource("PDFInvoice.Discount", lang.Id), orderSubTotalDiscountInCustomerCurrencyStr), font);
                                p.Alignment = Element.ALIGN_RIGHT;
                                doc.Add(p);
                            }
                            break;
                        case TaxDisplayType.IncludingTax:
                            {
                                var orderSubTotalDiscountInclTaxInCustomerCurrency = _currencyService.ConvertCurrency(order.OrderSubTotalDiscountInclTax, order.CurrencyRate);
                                string orderSubTotalDiscountInCustomerCurrencyStr = _priceFormatter.FormatPrice(-orderSubTotalDiscountInclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, true);

                                var p = new Paragraph(String.Format("{0} {1}", _localizationService.GetResource("PDFInvoice.Discount", lang.Id), orderSubTotalDiscountInCustomerCurrencyStr), font);
                                p.Alignment = Element.ALIGN_RIGHT;
                                doc.Add(p);
                            }
                            break;
                    }
                }

                //shipping
                if (order.ShippingStatus != ShippingStatus.ShippingNotRequired)
                {
                    switch (order.CustomerTaxDisplayType)
                    {
                        case TaxDisplayType.ExcludingTax:
                            {
                                var orderShippingExclTaxInCustomerCurrency = _currencyService.ConvertCurrency(order.OrderShippingExclTax, order.CurrencyRate);
                                string orderShippingExclTaxStr = _priceFormatter.FormatShippingPrice(orderShippingExclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, false);

                                var p = new Paragraph(String.Format("{0} {1}", _localizationService.GetResource("PDFInvoice.Shipping", lang.Id), orderShippingExclTaxStr), font);
                                p.Alignment = Element.ALIGN_RIGHT;
                                doc.Add(p);
                            }
                            break;
                        case TaxDisplayType.IncludingTax:
                            {
                                var orderShippingInclTaxInCustomerCurrency = _currencyService.ConvertCurrency(order.OrderShippingInclTax, order.CurrencyRate);
                                string orderShippingInclTaxStr = _priceFormatter.FormatShippingPrice(orderShippingInclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, true);

                                var p = new Paragraph(String.Format("{0} {1}", _localizationService.GetResource("PDFInvoice.Shipping", lang.Id), orderShippingInclTaxStr), font);
                                p.Alignment = Element.ALIGN_RIGHT;
                                doc.Add(p);
                            }
                            break;
                    }
                }

                //payment fee
                if (order.PaymentMethodAdditionalFeeExclTax > decimal.Zero)
                {
                    switch (order.CustomerTaxDisplayType)
                    {
                        case TaxDisplayType.ExcludingTax:
                            {
                                var paymentMethodAdditionalFeeExclTaxInCustomerCurrency = _currencyService.ConvertCurrency(order.PaymentMethodAdditionalFeeExclTax, order.CurrencyRate);
                                string paymentMethodAdditionalFeeExclTaxStr = _priceFormatter.FormatPaymentMethodAdditionalFee(paymentMethodAdditionalFeeExclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, false);

                                var p = new Paragraph(String.Format("{0} {1}", _localizationService.GetResource("PDFInvoice.PaymentMethodAdditionalFee", lang.Id), paymentMethodAdditionalFeeExclTaxStr), font);
                                p.Alignment = Element.ALIGN_RIGHT;
                                doc.Add(p);
                            }
                            break;
                        case TaxDisplayType.IncludingTax:
                            {
                                var paymentMethodAdditionalFeeInclTaxInCustomerCurrency = _currencyService.ConvertCurrency(order.PaymentMethodAdditionalFeeInclTax, order.CurrencyRate);
                                string paymentMethodAdditionalFeeInclTaxStr = _priceFormatter.FormatPaymentMethodAdditionalFee(paymentMethodAdditionalFeeInclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, true);

                                var p = new Paragraph(String.Format("{0} {1}", _localizationService.GetResource("PDFInvoice.PaymentMethodAdditionalFee", lang.Id), paymentMethodAdditionalFeeInclTaxStr), font);
                                p.Alignment = Element.ALIGN_RIGHT;
                                doc.Add(p);
                            }
                            break;
                    }
                }

                //tax
                string taxStr = string.Empty;
                var taxRates = new SortedDictionary<decimal, decimal>();
                bool displayTax = true;
                bool displayTaxRates = true;
                if (_taxSettings.HideTaxInOrderSummary && order.CustomerTaxDisplayType == TaxDisplayType.IncludingTax)
                {
                    displayTax = false;
                }
                else
                {
                    if (order.OrderTax == 0 && _taxSettings.HideZeroTax)
                    {
                        displayTax = false;
                        displayTaxRates = false;
                    }
                    else
                    {
                        taxRates = order.TaxRatesDictionary;

                        displayTaxRates = _taxSettings.DisplayTaxRates && taxRates.Count > 0;
                        displayTax = !displayTaxRates;

                        var orderTaxInCustomerCurrency = _currencyService.ConvertCurrency(order.OrderTax, order.CurrencyRate);
                        taxStr = _priceFormatter.FormatPrice(orderTaxInCustomerCurrency, true, order.CustomerCurrencyCode, false, lang);
                    }
                }
                if (displayTax)
                {
                    var p = new Paragraph(String.Format("{0} {1}", _localizationService.GetResource("PDFInvoice.Tax", lang.Id), taxStr), font);
                    p.Alignment = Element.ALIGN_RIGHT;
                    doc.Add(p);
                }
                if (displayTaxRates)
                {
                    foreach (var item in taxRates)
                    {
                        string taxRate = String.Format(_localizationService.GetResource("PDFInvoice.TaxRate", lang.Id), _priceFormatter.FormatTaxRate(item.Key));
                        string taxValue = _priceFormatter.FormatPrice(_currencyService.ConvertCurrency(item.Value, order.CurrencyRate), true, order.CustomerCurrencyCode, false, lang);

                        var p = new Paragraph(String.Format("{0} {1}", taxRate, taxValue), font);
                        p.Alignment = Element.ALIGN_RIGHT;
                        doc.Add(p);
                    }
                }

                //discount (applied to order total)
                if (order.OrderDiscount > decimal.Zero)
                {
                    var orderDiscountInCustomerCurrency = _currencyService.ConvertCurrency(order.OrderDiscount, order.CurrencyRate);
                    string orderDiscountInCustomerCurrencyStr = _priceFormatter.FormatPrice(-orderDiscountInCustomerCurrency, true, order.CustomerCurrencyCode, false, lang);

                    var p = new Paragraph(String.Format("{0} {1}", _localizationService.GetResource("PDFInvoice.Discount", lang.Id), orderDiscountInCustomerCurrencyStr), font);
                    p.Alignment = Element.ALIGN_RIGHT;
                    doc.Add(p);
                }

                //gift cards
                foreach (var gcuh in order.GiftCardUsageHistory)
                {
                    string gcTitle = string.Format(_localizationService.GetResource("PDFInvoice.GiftCardInfo", lang.Id), gcuh.GiftCard.GiftCardCouponCode);
                    string gcAmountStr = _priceFormatter.FormatPrice(-(_currencyService.ConvertCurrency(gcuh.UsedValue, order.CurrencyRate)), true, order.CustomerCurrencyCode, false, lang);

                    var p = new Paragraph(String.Format("{0} {1}", gcTitle, gcAmountStr), font);
                    p.Alignment = Element.ALIGN_RIGHT;
                    doc.Add(p);
                }

                //reward points
                if (order.RedeemedRewardPointsEntry != null)
                {
                    string rpTitle = string.Format(_localizationService.GetResource("PDFInvoice.RewardPoints", lang.Id), -order.RedeemedRewardPointsEntry.Points);
                    string rpAmount = _priceFormatter.FormatPrice(-(_currencyService.ConvertCurrency(order.RedeemedRewardPointsEntry.UsedAmount, order.CurrencyRate)), true, order.CustomerCurrencyCode, false, lang);

                    var p = new Paragraph(String.Format("{0} {1}", rpTitle, rpAmount), font);
                    p.Alignment = Element.ALIGN_RIGHT;
                    doc.Add(p);
                }

                //order total
                var orderTotalInCustomerCurrency = _currencyService.ConvertCurrency(order.OrderTotal, order.CurrencyRate);
                string orderTotalStr = _priceFormatter.FormatPrice(orderTotalInCustomerCurrency, true, order.CustomerCurrencyCode, false, lang);


                var pTotal = new Paragraph(String.Format("{0} {1}", _localizationService.GetResource("PDFInvoice.OrderTotal", lang.Id), orderTotalStr), titleFont);
                pTotal.Alignment = Element.ALIGN_RIGHT;
                doc.Add(pTotal);

                #endregion

                #region Order notes

                if (_pdfSettings.RenderOrderNotes)
                {
                    var orderNotes = order.OrderNotes
                        .Where(on => on.DisplayToCustomer)
                        .OrderByDescending(on => on.CreatedOnUtc)
                        .ToList();
                    if (orderNotes.Count > 0)
                    {
                        doc.Add(new Paragraph(_localizationService.GetResource("PDFInvoice.OrderNotes", lang.Id), titleFont));

                        doc.Add(new Paragraph(" "));

                        var notesTable = new PdfPTable(2);
                        notesTable.WidthPercentage = 100f;
                        notesTable.SetWidths(new[] { 30, 70 });

                        //created on
                        cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFInvoice.OrderNotes.CreatedOn", lang.Id), font));
                        cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                        cell.HorizontalAlignment = Element.ALIGN_CENTER;
                        notesTable.AddCell(cell);

                        //note
                        cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFInvoice.OrderNotes.Note", lang.Id), font));
                        cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                        cell.HorizontalAlignment = Element.ALIGN_CENTER;
                        notesTable.AddCell(cell);

                        foreach (var orderNote in orderNotes)
                        {
                            cell = new PdfPCell();
                            cell.AddElement(new Paragraph(_dateTimeHelper.ConvertToUserTime(orderNote.CreatedOnUtc, DateTimeKind.Utc).ToString(), font));
                            cell.HorizontalAlignment = Element.ALIGN_LEFT;
                            notesTable.AddCell(cell);

                            cell = new PdfPCell();
                            cell.AddElement(new Paragraph(HtmlHelper.ConvertHtmlToPlainText(orderNote.FormatOrderNoteText(), true, true), font));
                            cell.HorizontalAlignment = Element.ALIGN_LEFT;
                            notesTable.AddCell(cell);
                        }
                        doc.Add(notesTable);
                    }
                }

                #endregion

                ordNum++;
                if (ordNum < ordCount)
                {
                    doc.NewPage();
                }
            }
            doc.Close();
        }

        /// <summary>
        /// Print picking slips to PDF
        /// </summary>
        /// <param name="stream">Stream</param>
        /// <param name="orders">Orders</param>
        /// <param name="lang">Language</param>
        public void PrintPickingSlipsToPdf(Stream stream, IList<Order> orders, Language lang)
        {
            if (stream == null)
                throw new ArgumentNullException("stream");

            if (orders == null)
                throw new ArgumentNullException("orders");

            var pageSize = PageSize.A4;

            var doc = new Document(pageSize);
            PdfWriter.GetInstance(doc, stream);
            doc.Open();

            //fonts
            var titleFont = GetFont();
            titleFont.SetStyle(Font.BOLD);
            titleFont.Color = BaseColor.BLACK;
            titleFont.Size = 20.0f;
            var subtitleFont = GetFont();
            subtitleFont.SetStyle(Font.BOLD);
            subtitleFont.Color = BaseColor.BLACK;
            subtitleFont.Size = 13.0f;
            var font = GetFont();
            var attributesFont = GetFont();
            attributesFont.SetStyle(Font.ITALIC);

            int orderCount = orders.Count;
            int orderNum = 0;
            iTextSharp.text.Image image = iTextSharp.text.Image.GetInstance(HttpContext.Current.Request.PhysicalApplicationPath + @"\Plugins\Widgets.AdminFeatures\Content\images\logo.gif");
            String v3url = _genesysSettings.GenesysDesignUrl + "/design_part/get_image.php?thumbnail=true&id=";

            foreach (var order in orders)
            {
                PdfPTable titleTable = new PdfPTable(2);
                titleTable.HorizontalAlignment = 0;
                titleTable.SpacingBefore = 10;
                titleTable.SpacingAfter = 10;
                titleTable.DefaultCell.PaddingTop = 0;
                titleTable.DefaultCell.Border = 0;
                Paragraph titleParagraph = new Paragraph();
                titleTable.AddCell(image);
                PdfPCell titleCell = new PdfPCell();
                titleCell.Border = 0;
                titleCell.PaddingTop = 20;
                titleCell.PaddingLeft = 20;
                titleParagraph.Add(new Phrase("Picking Slip", titleFont));
                titleParagraph.Add(new Phrase("\nOrder #: " + order.Id + "\n"));
                titleParagraph.Add(new Phrase(String.Format(_localizationService.GetResource("PDFInvoice.OrderDate", lang.Id), _dateTimeHelper.ConvertToUserTime(order.CreatedOnUtc, DateTimeKind.Utc).ToString("D", new CultureInfo(lang.LanguageCulture)))));
                titleCell.AddElement(titleParagraph);
                titleTable.AddCell(titleCell);
                titleTable.SpacingAfter = 26.0f;
                doc.Add(titleTable);
                PdfPTable infoTable = new PdfPTable(2);
                infoTable.HorizontalAlignment = 0;
                infoTable.SpacingBefore = 10;
                infoTable.SpacingAfter = 10;
                infoTable.DefaultCell.Border = 0;
                //infoTable.SetWidths(new int[] { 1, 4 });
                infoTable.AddCell(new Phrase("Billing Information", subtitleFont));
                infoTable.AddCell(new Phrase("Shipping Information", subtitleFont));
                if (_addressSettings.CompanyEnabled && !String.IsNullOrEmpty(order.BillingAddress.Company))
                    infoTable.AddCell(new Phrase("Company: " + order.BillingAddress.Company));
                else
                    infoTable.AddCell(new Phrase(""));

                if (order.ShippingAddress != null && _addressSettings.CompanyEnabled && !String.IsNullOrEmpty(order.ShippingAddress.Company))
                    infoTable.AddCell(new Phrase("Company: " + order.ShippingAddress.Company));
                else
                    infoTable.AddCell(new Phrase(""));

                infoTable.AddCell(new Phrase(String.Format(_localizationService.GetResource("PDFPackagingSlip.Name", lang.Id), order.BillingAddress.FirstName + " " + order.BillingAddress.LastName)));
                if (order.ShippingAddress != null) { 
                    infoTable.AddCell(new Phrase(String.Format(_localizationService.GetResource("PDFPackagingSlip.Name", lang.Id), order.ShippingAddress.FirstName + " " + order.ShippingAddress.LastName)));
                }
                else
                {
                    infoTable.AddCell(new Phrase(""));
                }
                infoTable.AddCell(new Phrase(String.Format(_localizationService.GetResource("PDFPackagingSlip.Phone", lang.Id), order.BillingAddress.PhoneNumber)));
                if (order.ShippingAddress != null) { 
                    infoTable.AddCell(new Phrase(String.Format(_localizationService.GetResource("PDFPackagingSlip.Phone", lang.Id), order.ShippingAddress.PhoneNumber)));
                }
                else
                {
                    infoTable.AddCell(new Phrase(""));
                }
                infoTable.AddCell(new Phrase(String.Format(_localizationService.GetResource("PDFPackagingSlip.Address", lang.Id), order.BillingAddress.Address1)));
                if (order.ShippingAddress != null) { 
                    infoTable.AddCell(new Phrase(String.Format(_localizationService.GetResource("PDFPackagingSlip.Address", lang.Id), order.ShippingAddress.Address1)));
                }
                else
                {
                    infoTable.AddCell(new Phrase(""));
                }

                if (_addressSettings.StreetAddress2Enabled && !String.IsNullOrEmpty(order.BillingAddress.Address2))
                    infoTable.AddCell(new Phrase(String.Format(_localizationService.GetResource("PDFPackagingSlip.Address2", lang.Id), order.BillingAddress.Address2)));
                else
                    infoTable.AddCell(new Phrase(""));

                if (order.ShippingAddress != null && _addressSettings.StreetAddress2Enabled && !String.IsNullOrEmpty(order.ShippingAddress.Address2))
                    infoTable.AddCell(new Phrase(String.Format(_localizationService.GetResource("PDFPackagingSlip.Address2", lang.Id), order.ShippingAddress.Address2)));
                else
                    infoTable.AddCell(new Phrase(""));

                infoTable.AddCell(new Phrase(String.Format("{0}, {1} {2}", order.BillingAddress.City, order.BillingAddress.StateProvince != null ? order.BillingAddress.StateProvince.GetLocalized(x => x.Name, lang.Id) : "", order.BillingAddress.ZipPostalCode)));
                if (order.ShippingAddress != null) { 
                    infoTable.AddCell(new Phrase(String.Format("{0}, {1} {2}", order.ShippingAddress.City, order.ShippingAddress.StateProvince != null ? order.ShippingAddress.StateProvince.GetLocalized(x => x.Name, lang.Id) : "", order.ShippingAddress.ZipPostalCode)));
                }
                else
                {
                    infoTable.AddCell(new Phrase(""));
                }
                infoTable.AddCell(new Phrase(String.Format("{0}", order.BillingAddress.Country != null ? order.BillingAddress.Country.GetLocalized(x => x.Name, lang.Id) : "")));
                if (order.ShippingAddress != null) { 
                    infoTable.AddCell(new Phrase(String.Format("{0}", order.ShippingAddress.Country != null ? order.ShippingAddress.Country.GetLocalized(x => x.Name, lang.Id) : "")));
                }
                else
                {
                    infoTable.AddCell(new Phrase(""));
                }
                var paymentMethod = _paymentService.LoadPaymentMethodBySystemName(order.PaymentMethodSystemName);
                string paymentMethodStr = paymentMethod != null ? paymentMethod.GetLocalizedFriendlyName(_localizationService, lang.Id) : order.PaymentMethodSystemName;
                infoTable.AddCell(new Phrase(String.Format(_localizationService.GetResource("PDFInvoice.PaymentMethod", lang.Id), paymentMethodStr)));
                infoTable.AddCell(new Phrase(String.Format(_localizationService.GetResource("PDFPackagingSlip.ShippingMethod", lang.Id), order.ShippingMethod)));
                doc.Add(infoTable);
                if (order.DiscountUsageHistory.Count > 0)
                    doc.Add(new Paragraph("Voucher: " + order.DiscountUsageHistory.First().Discount.CouponCode));

                PdfPTable itemTable = new PdfPTable(5);
                itemTable.HeaderRows = 0;
                itemTable.SpacingBefore = 10;
                itemTable.WidthPercentage = 100.0f;
                itemTable.AddCell(new Phrase("Name"));
                itemTable.AddCell(new Phrase("Category"));
                itemTable.AddCell(new Phrase("SKU"));
                itemTable.AddCell(new Phrase("Order Qty"));
                itemTable.AddCell(new Phrase("Qty Shipped"));


                foreach (var item in order.OrderItems)
                {
                    string attrValue = _productAttributeParser.ParseValues(item.AttributesXml, 0).FirstOrDefault();

                    if (attrValue != null)
                    {
                        GeneSysWidget.Helpers.OrderDataHelper.OrderDetails details = GeneSysWidget.Helpers.OrderDataHelper.getOrderDetails(attrValue);
                        PdfPCell nameParagraph = new PdfPCell();
                        nameParagraph.AddElement(new Phrase(item.Product.Name));                        
                        iTextSharp.text.Image productImage = null;
                        try
                        {
                            productImage = iTextSharp.text.Image.GetInstance(new Uri(v3url + details.imageId_S));
                            productImage.SpacingBefore = 5;
                            nameParagraph.AddElement(productImage);
                        }
                        catch (Exception e)
                        {
                            //Logging?
                        }
                        itemTable.AddCell(nameParagraph);
                    }
                    else
                    {
                        itemTable.AddCell(new Phrase(item.Product.Name));
                    }
                    
                    if (item.Product.ProductCategories.Count > 0)
                    {
                        itemTable.AddCell(new Phrase(item.Product.ProductCategories.First(cat => cat.Category.Deleted == false).Category.Name));
                    }
                    else
                    {
                        itemTable.AddCell(new Phrase(""));
                    }
                    itemTable.AddCell(new Phrase(item.Product.ManufacturerPartNumber));
                    itemTable.AddCell(new Phrase(item.Quantity.ToString()));
                    itemTable.AddCell(new Phrase(item.GetTotalNumberOfShippedItems().ToString()));
                }
                doc.Add(itemTable);

                orderNum++;
                if (orderNum < orderCount)
                {
                    doc.NewPage();
                }
            }
            if (orderCount == 0)
            {
                doc.Add(new Phrase("No more orders meet criteria"));
                doc.NewPage();
            }
            doc.Close();
        }

        /// <summary>
        /// Print packaging slips to PDF
        /// </summary>
        /// <param name="stream">Stream</param>
        /// <param name="shipments">Shipments</param>
        /// <param name="languageId">Language identifier; 0 to use a language used when placing an order</param>
        public override void PrintPackagingSlipsToPdf(Stream stream, IList<Shipment> shipments, int languageId = 0)
        {
            if (stream == null)
                throw new ArgumentNullException("stream");

            if (shipments == null)
                throw new ArgumentNullException("shipments");
            var cell = new PdfPCell();
            var lang = _languageService.GetLanguageById(languageId);
            if (lang == null)
                throw new ArgumentException(string.Format("Cannot load language. ID={0}", languageId));

            var pageSize = PageSize.A4;

            if (_pdfSettings.LetterPageSizeEnabled)
            {
                pageSize = PageSize.LETTER;
            }

            var doc = new Document(pageSize);
            PdfWriter.GetInstance(doc, stream);
            doc.Open();

            //fonts
            var titleFont = GetFont();
            titleFont.SetStyle(Font.BOLD);
            titleFont.Color = BaseColor.BLACK;
            var font = GetFont();
            var attributesFont = GetFont();
            attributesFont.SetStyle(Font.ITALIC);

            var packFont = GetFont();
            packFont.SetStyle(Font.BOLD);
            packFont.Color = BaseColor.BLACK;
            packFont.Size = 16.0f;

            var logoPicture = _pictureService.GetPictureById(_pdfSettings.LogoPictureId);
            var logoExists = logoPicture != null;


            int shipmentCount = shipments.Count;
            int shipmentNum = 0;

            foreach (var shipment in shipments)
            {
                //header

                //header
                var headerTable = new PdfPTable(1);
                headerTable.HorizontalAlignment = Element.ALIGN_CENTER;
                headerTable.WidthPercentage = 100f;

                //logo
                if (logoExists)
                {
                    var logoFilePath = _pictureService.GetThumbLocalPath(logoPicture, 0, false);
                    var cellLogo = new PdfPCell(Image.GetInstance(logoFilePath));
                    cellLogo.Border = Rectangle.NO_BORDER;
                    cellLogo.HorizontalAlignment = Element.ALIGN_CENTER;
                    headerTable.AddCell(cellLogo);
                }

                var packParagraph = new Paragraph("Packing Slip", packFont);
                packParagraph.Alignment = Element.ALIGN_CENTER;

                var cellTitle = new PdfPCell();
                cellTitle.HorizontalAlignment = Element.ALIGN_CENTER;
                cellTitle.AddElement(packParagraph);
                cellTitle.Border = Rectangle.NO_BORDER;

                headerTable.AddCell(cellTitle);

                doc.Add(headerTable);

                var order = shipment.Order;

                if (languageId == 0)
                {
                    lang = _languageService.GetLanguageById(order.CustomerLanguageId);
                    if (lang == null || !lang.Published)
                        lang = _workContext.WorkingLanguage;
                }
                String v3url = _genesysSettings.GenesysDesignUrl + "/design_part/get_image.php?thumbnail=true&id=";

                if (order.ShippingAddress != null)
                {
                    //doc.Add(new Paragraph(String.Format(_localizationService.GetResource("PDFPackagingSlip.Shipment", lang.Id), shipment.Id), titleFont));
                    //doc.Add(new Paragraph(String.Format(_localizationService.GetResource("PDFPackagingSlip.Order", lang.Id), order.Id), titleFont));
                    doc.Add(new Paragraph("Billing Address", titleFont));
                    if (_addressSettings.CompanyEnabled && !String.IsNullOrEmpty(order.BillingAddress.Company))
                        doc.Add(new Paragraph(String.Format(_localizationService.GetResource("PDFPackagingSlip.Company", lang.Id), order.BillingAddress.Company), font));

                    doc.Add(new Paragraph(String.Format(_localizationService.GetResource("PDFPackagingSlip.Name", lang.Id), order.BillingAddress.FirstName + " " + order.BillingAddress.LastName), font));
                    if (_addressSettings.PhoneEnabled)
                        doc.Add(new Paragraph(String.Format(_localizationService.GetResource("PDFPackagingSlip.Phone", lang.Id), order.BillingAddress.PhoneNumber), font));
                    if (_addressSettings.StreetAddressEnabled)
                        doc.Add(new Paragraph(String.Format(_localizationService.GetResource("PDFPackagingSlip.Address", lang.Id), order.BillingAddress.Address1), font));

                    if (_addressSettings.StreetAddress2Enabled && !String.IsNullOrEmpty(order.BillingAddress.Address2))
                        doc.Add(new Paragraph(String.Format(_localizationService.GetResource("PDFPackagingSlip.Address2", lang.Id), order.BillingAddress.Address2), font));

                    if (_addressSettings.CityEnabled || _addressSettings.StateProvinceEnabled || _addressSettings.ZipPostalCodeEnabled)
                        doc.Add(new Paragraph(String.Format("{0}, {1} {2}", order.BillingAddress.City, order.BillingAddress.StateProvince != null ? order.BillingAddress.StateProvince.GetLocalized(x => x.Name, lang.Id) : "", order.BillingAddress.ZipPostalCode), font));

                    if (_addressSettings.CountryEnabled && order.BillingAddress.Country != null)
                        doc.Add(new Paragraph(String.Format("{0}", order.BillingAddress.Country != null ? order.BillingAddress.Country.GetLocalized(x => x.Name, lang.Id) : ""), font));

                    doc.Add(new Paragraph(" "));

                    doc.Add(new Paragraph("Shipping Address", titleFont));
                    if (_addressSettings.CompanyEnabled && !String.IsNullOrEmpty(order.ShippingAddress.Company))
                        doc.Add(new Paragraph(String.Format(_localizationService.GetResource("PDFPackagingSlip.Company", lang.Id), order.ShippingAddress.Company), font));

                    doc.Add(new Paragraph(String.Format(_localizationService.GetResource("PDFPackagingSlip.Name", lang.Id), order.ShippingAddress.FirstName + " " + order.ShippingAddress.LastName), font));
                    if (_addressSettings.PhoneEnabled)
                        doc.Add(new Paragraph(String.Format(_localizationService.GetResource("PDFPackagingSlip.Phone", lang.Id), order.ShippingAddress.PhoneNumber), font));
                    if (_addressSettings.StreetAddressEnabled)
                        doc.Add(new Paragraph(String.Format(_localizationService.GetResource("PDFPackagingSlip.Address", lang.Id), order.ShippingAddress.Address1), font));

                    if (_addressSettings.StreetAddress2Enabled && !String.IsNullOrEmpty(order.ShippingAddress.Address2))
                        doc.Add(new Paragraph(String.Format(_localizationService.GetResource("PDFPackagingSlip.Address2", lang.Id), order.ShippingAddress.Address2), font));

                    if (_addressSettings.CityEnabled || _addressSettings.StateProvinceEnabled || _addressSettings.ZipPostalCodeEnabled)
                        doc.Add(new Paragraph(String.Format("{0}, {1} {2}", order.ShippingAddress.City, order.ShippingAddress.StateProvince != null ? order.ShippingAddress.StateProvince.GetLocalized(x => x.Name, lang.Id) : "", order.ShippingAddress.ZipPostalCode), font));

                    if (_addressSettings.CountryEnabled && order.ShippingAddress.Country != null)
                        doc.Add(new Paragraph(String.Format("{0}", order.ShippingAddress.Country != null ? order.ShippingAddress.Country.GetLocalized(x => x.Name, lang.Id) : ""), font));

                    doc.Add(new Paragraph(" "));

                    doc.Add(new Paragraph(String.Format(_localizationService.GetResource("PDFPackagingSlip.ShippingMethod", lang.Id), order.ShippingMethod), font));
                    doc.Add(new Paragraph(" "));

                    // var productsTable = new PdfPTable(1);
                    // productsTable.WidthPercentage = 100f;
                    // //productsTable.SetWidths(new[] { 60, 20, 20 });

                    // //product name
                    // var cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFPackagingSlip.ProductName", lang.Id), font));
                    // cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                    // cell.HorizontalAlignment = Element.ALIGN_CENTER;
                    // productsTable.AddCell(cell);

                    ///* //SKU
                    // cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFPackagingSlip.SKU", lang.Id), font));
                    // cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                    // cell.HorizontalAlignment = Element.ALIGN_CENTER;
                    // productsTable.AddCell(cell);

                    // //qty
                    // cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFPackagingSlip.QTY", lang.Id), font));
                    // cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                    // cell.HorizontalAlignment = Element.ALIGN_CENTER;
                    // productsTable.AddCell(cell);*/

                    // foreach (var sopv in shipment.ShipmentOrderProductVariants)
                    // {
                    //     //product name
                    //     var opv = _orderService.GetOrderProductVariantById(sopv.OrderProductVariantId);
                    //     if (opv == null)
                    //         continue;

                    //     var pv = opv.ProductVariant;
                    //     string name = "";
                    //     if (!String.IsNullOrEmpty(pv.GetLocalized(x => x.Name, lang.Id)))
                    //         name = string.Format("{0} ({1})", pv.Product.GetLocalized(x => x.Name, lang.Id), pv.GetLocalized(x => x.Name, lang.Id));
                    //     else
                    //         name = pv.Product.GetLocalized(x => x.Name, lang.Id);
                    //     cell = new PdfPCell();
                    //     cell.AddElement(new Paragraph(name, font));
                    //     cell.HorizontalAlignment = Element.ALIGN_LEFT;
                    //     if (opv.AttributeDescription.Contains(":"))
                    //     {
                    //         char[] delimiterChars = { ':' };
                    //         String[] pieces = opv.AttributeDescription.Split(delimiterChars);
                    //         String designID = pieces[1].Trim();
                    //         iTextSharp.text.Image productImage = null;
                    //         try
                    //         {
                    //             productImage = iTextSharp.text.Image.GetInstance(new Uri(v3url + designID));
                    //             productImage.SpacingBefore = 5;
                    //             productImage.ScaleToFit(productImage.Width, productImage.Height);
                    //             cell.AddElement(productImage);
                    //         }
                    //         catch (Exception e)
                    //         {
                    //             _logger.Error("Couldn't get image from genesys for design " + designID, e);
                    //         }
                    //     }
                    //     else
                    //     {
                    //         var attributesParagraph = new Paragraph(HtmlHelper.ConvertHtmlToPlainText(opv.AttributeDescription, true, true), attributesFont);
                    //         cell.AddElement(attributesParagraph);
                    //     }
                    //     productsTable.AddCell(cell);

                    //     /*//SKU
                    //     var sku = pv.FormatSku(opv.AttributesXml, _productAttributeParser);
                    //     cell = new PdfPCell(new Phrase(sku ?? String.Empty, font));
                    //     cell.HorizontalAlignment = Element.ALIGN_CENTER;
                    //     productsTable.AddCell(cell);

                    //     //qty
                    //     cell = new PdfPCell(new Phrase(sopv.Quantity.ToString(), font));
                    //     cell.HorizontalAlignment = Element.ALIGN_CENTER;
                    //     productsTable.AddCell(cell);*/
                    // }
                    // doc.Add(productsTable);
                    #region Products
                    //products
                    doc.Add(new Paragraph(_localizationService.GetResource("PDFInvoice.Product(s)", lang.Id), titleFont));
                    doc.Add(new Paragraph(" "));


                    var orderProductVariants = _orderService.GetAllOrderItems(order.Id, null, null, null, null, null, null);

                    var productsTable = new PdfPTable(_catalogSettings.ShowProductSku ? 3 : 2);
                    productsTable.WidthPercentage = 100f;
                    productsTable.SetWidths(_catalogSettings.ShowProductSku ? new[] { 40, 15, 15 } : new[] { 40, 20 });

                    //product name
                    cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFInvoice.ProductName", lang.Id), font));
                    cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                    cell.HorizontalAlignment = Element.ALIGN_CENTER;
                    productsTable.AddCell(cell);

                    //SKU
                    if (_catalogSettings.ShowProductSku)
                    {
                        cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFInvoice.SKU", lang.Id), font));
                        cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                        cell.HorizontalAlignment = Element.ALIGN_CENTER;
                        productsTable.AddCell(cell);
                    }

                    //price
                    //cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFInvoice.ProductPrice", lang.Id), font));
                    //cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                    //cell.HorizontalAlignment = Element.ALIGN_CENTER;
                    //productsTable.AddCell(cell);

                    //qty
                    cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFInvoice.ProductQuantity", lang.Id), font));
                    cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                    cell.HorizontalAlignment = Element.ALIGN_CENTER;
                    productsTable.AddCell(cell);

                    //total
                    //cell = new PdfPCell(new Phrase(_localizationService.GetResource("PDFInvoice.ProductTotal", lang.Id), font));
                    //cell.BackgroundColor = BaseColor.LIGHT_GRAY;
                    //cell.HorizontalAlignment = Element.ALIGN_CENTER;
                    //productsTable.AddCell(cell);

                    for (int i = 0; i < orderProductVariants.Count; i++)
                    {
                        var orderProductVariant = orderProductVariants[i];
                        var pv = orderProductVariant.Product;

                        //product name
                        string name = "";
                        if (!String.IsNullOrEmpty(pv.GetLocalized(x => x.Name, lang.Id)))
                            name = string.Format("{0} ({1})", pv.GetLocalized(x => x.Name, lang.Id), pv.GetLocalized(x => x.Name, lang.Id));
                        else
                            name = pv.GetLocalized(x => x.Name, lang.Id);
                        cell = new PdfPCell();
                        cell.AddElement(new Paragraph(name, font));
                        cell.HorizontalAlignment = Element.ALIGN_LEFT;

                        if (!String.IsNullOrEmpty(orderProductVariant.AttributesXml))
                        {
                            string attrValue = _productAttributeParser.ParseValues(orderProductVariant.AttributesXml, 0).FirstOrDefault();

                            if (attrValue != null)
                            {
                                GeneSysWidget.Helpers.OrderDataHelper.OrderDetails details = GeneSysWidget.Helpers.OrderDataHelper.getOrderDetails(attrValue);
                                iTextSharp.text.Image productImage = null;
                                try
                                {
                                    productImage = iTextSharp.text.Image.GetInstance(new Uri(v3url + details.imageId_S));
                                    productImage.SpacingBefore = 5;
                                    productImage.ScaleToFit(productImage.Width, productImage.Height);
                                    cell.AddElement(productImage);
                                }
                                catch (Exception e)
                                {
                                    _logger.Error("Couldn't get image from genesys for design " + details.orderItemId, e);
                                }
                            }
                            else
                            {
                                var attributesParagraph = new Paragraph(HtmlHelper.ConvertHtmlToPlainText(orderProductVariant.AttributeDescription, true, true), attributesFont);
                                cell.AddElement(attributesParagraph);
                            }
                        }

                        productsTable.AddCell(cell);

                        //SKU
                        if (_catalogSettings.ShowProductSku)
                        {
                            var sku = pv.FormatSku(orderProductVariant.AttributesXml, _productAttributeParser);
                            cell = new PdfPCell(new Phrase(sku ?? String.Empty, font));
                            cell.HorizontalAlignment = Element.ALIGN_CENTER;
                            productsTable.AddCell(cell);
                        }

                        //price
                        //string unitPrice = string.Empty;
                        //switch (order.CustomerTaxDisplayType)
                        //{
                        //    case TaxDisplayType.ExcludingTax:
                        //        {
                        //            var opvUnitPriceExclTaxInCustomerCurrency = _currencyService.ConvertCurrency(orderProductVariant.UnitPriceExclTax, order.CurrencyRate);
                        //            unitPrice = _priceFormatter.FormatPrice(opvUnitPriceExclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, false);
                        //        }
                        //        break;
                        //    case TaxDisplayType.IncludingTax:
                        //        {
                        //            var opvUnitPriceInclTaxInCustomerCurrency = _currencyService.ConvertCurrency(orderProductVariant.UnitPriceInclTax, order.CurrencyRate);
                        //            unitPrice = _priceFormatter.FormatPrice(opvUnitPriceInclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, true);
                        //        }
                        //        break;
                        //}
                        //cell = new PdfPCell(new Phrase(unitPrice, font));
                        //cell.HorizontalAlignment = Element.ALIGN_LEFT;
                        //productsTable.AddCell(cell);

                        //qty
                        cell = new PdfPCell(new Phrase(orderProductVariant.Quantity.ToString(), font));
                        cell.HorizontalAlignment = Element.ALIGN_LEFT;
                        productsTable.AddCell(cell);

                        //total
                        //string subTotal = string.Empty;
                        //switch (order.CustomerTaxDisplayType)
                        //{
                        //    case TaxDisplayType.ExcludingTax:
                        //        {
                        //            var opvPriceExclTaxInCustomerCurrency = _currencyService.ConvertCurrency(orderProductVariant.PriceExclTax, order.CurrencyRate);
                        //            subTotal = _priceFormatter.FormatPrice(opvPriceExclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, false);
                        //        }
                        //        break;
                        //    case TaxDisplayType.IncludingTax:
                        //        {
                        //            var opvPriceInclTaxInCustomerCurrency = _currencyService.ConvertCurrency(orderProductVariant.PriceInclTax, order.CurrencyRate);
                        //            subTotal = _priceFormatter.FormatPrice(opvPriceInclTaxInCustomerCurrency, true, order.CustomerCurrencyCode, lang, true);
                        //        }
                        //        break;
                        //}
                        //cell = new PdfPCell(new Phrase(subTotal, font));
                        //cell.HorizontalAlignment = Element.ALIGN_LEFT;
                        //productsTable.AddCell(cell);
                    }
                    doc.Add(productsTable);

                    #endregion

                    doc.Add(new Paragraph(order.ShippingAddress.FirstName + " " + order.ShippingAddress.LastName + ",\nThank you for your order.", font));
                    doc.Add(new Paragraph("Date Ordered: " + order.CreatedOnUtc.ToShortDateString(), font));
                    doc.Add(new Paragraph("Order Number: " + order.Id, font));
                    doc.Add(new Paragraph("Should you need to contact us regarding your order, please email cs@peerlesstamp.com", font));
                }

                shipmentNum++;
                if (shipmentNum < shipmentCount)
                {
                    doc.NewPage();
                }
            }


            doc.Close();
        }
    }
}
