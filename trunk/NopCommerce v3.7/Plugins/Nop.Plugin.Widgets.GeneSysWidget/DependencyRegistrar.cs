using Autofac;
using Autofac.Builder;
using Nop.Core.Infrastructure;
using Nop.Core.Infrastructure.DependencyManagement;
using System.Web.Mvc;
using Nop.Services.Media;
using Nop.Services.Catalog;
using Nop.Web.Controllers;
using Nop.Services.Payments;
using Nop.Core.Events;
using Nop.Core.Domain.Orders;
using Nop.Services.Events;
using Nop.Core.Configuration;

namespace Nop.Plugin.Widgets.GeneSysWidget
{
    public class DependencyRegistrar : IDependencyRegistrar
    {
        public void Register(ContainerBuilder builder, ITypeFinder typeFinder, NopConfig config)
        {
            //builder.RegisterType<Filters.CheckoutFilter>().As<IFilterProvider>().InstancePerRequest();
            builder.RegisterType<Filters.AddToCartFilter>().As<IFilterProvider>().InstancePerRequest();
            builder.RegisterType<Filters.ProductDetailsFilter>().As<IFilterProvider>().InstancePerRequest();
            builder.RegisterType<Filters.ShoppingCartFilter>().As<IFilterProvider>().InstancePerRequest();
            builder.RegisterType<Filters.GenesysProductFilter>().As<IFilterProvider>().InstancePerRequest();
            builder.RegisterType<Services.GenesysProductAttributeParser>().As<IProductAttributeParser>().InstancePerRequest();
            //builder.RegisterType<EventHandlers.GenesysOrderHandler>().As<IConsumer<OrderPaidEvent>>().InstancePerRequest();
        }

        public int Order
        {
            get { return 0; }
        }
    }
}
