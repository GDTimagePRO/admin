using Nop.Core.Infrastructure;
using Nop.Services.Catalog;
using Nop.Web.Controllers;
using Nop.Web.Models.Catalog;
using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Web.Mvc;

namespace Nop.Plugin.Widgets.GeneSysWidget.Filters
{
    class GenesysProductFilter : ActionFilterAttribute, IFilterProvider
    {
        public IEnumerable<Filter> GetFilters(ControllerContext controllerContext,
            ActionDescriptor actionDescriptor)
        {
            if (controllerContext.Controller is ProductController || controllerContext.Controller is CatalogController)
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
            if (filterContext.Controller.ViewData.Model != null)
            {
                bool genesys = false;
                IProductService _productService = EngineContext.Current.Resolve<IProductService>();
                if (filterContext.Controller.ViewData.Model is Nop.Web.Models.Catalog.ProductDetailsModel)
                {
                    ProductDetailsModel model = (ProductDetailsModel)filterContext.Controller.ViewData.Model;
                    var product = _productService.GetProductById(model.Id);
                    if (product.ProductSpecificationAttributes.Any(a => a.SpecificationAttributeOption.SpecificationAttribute.Name.ToLowerInvariant().Equals("genesysproduct")))
                    {
                        genesys = true;
                    }
                    model.AddToCart.CustomProperties.Add("genesysproduct", genesys);
                }
                else if (filterContext.Controller.ViewData.Model is CategoryModel)
                {
                    CategoryModel model = (CategoryModel)filterContext.Controller.ViewData.Model;
                    foreach (var product in model.Products) {
                        var p = _productService.GetProductById(product.Id);
                        if (p.ProductSpecificationAttributes.Any(a => a.SpecificationAttributeOption.SpecificationAttribute.Name.ToLowerInvariant().Equals("genesysproduct")))
                        {
                            genesys = true;
                        }
                        product.CustomProperties.Add("genesysproduct", genesys);
                    }
                }
                else if (filterContext.Controller.ViewData.Model is SearchModel)
                {
                    SearchModel model = (SearchModel)filterContext.Controller.ViewData.Model;
                    foreach (var product in model.Products)
                    {
                        var p = _productService.GetProductById(product.Id);
                        if (p.ProductSpecificationAttributes.Any(a => a.SpecificationAttributeOption.SpecificationAttribute.Name.ToLowerInvariant().Equals("genesysproduct")))
                        {
                            genesys = true;
                        }
                        product.CustomProperties.Add("genesysproduct", genesys);
                    }
                }
                else if (filterContext.Controller.ViewData.Model is IList)
                {
                    IList modelList = (IList)filterContext.Controller.ViewData.Model;
                    if (modelList[0] is ProductOverviewModel)
                    {
                        ProductOverviewModel model = (ProductOverviewModel)modelList[0];
                        var product = _productService.GetProductById(model.Id);
                        if (product.ProductSpecificationAttributes.Any(a => a.SpecificationAttributeOption.SpecificationAttribute.Name.ToLowerInvariant().Equals("genesysproduct")))
                        {
                            genesys = true;
                        }
                        model.CustomProperties.Add("genesysproduct", genesys);
                    }
                }
            }
        }
    }
}
