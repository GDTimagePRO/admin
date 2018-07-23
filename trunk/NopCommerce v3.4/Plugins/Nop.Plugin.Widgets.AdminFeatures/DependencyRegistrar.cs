using Autofac;
using Autofac.Builder;
using Autofac.Core;
using Nop.Core.Infrastructure;
using Nop.Core.Infrastructure.DependencyManagement;
using Nop.Services.Common;
using Nop.Services.Messages;
using System.Web.Mvc;

public class DependencyRegistrar : IDependencyRegistrar
{
    public virtual void Register(ContainerBuilder builder, ITypeFinder typeFinder)
    {
        builder.RegisterType<Nop.Plugin.Widgets.AdminFeatures.Services.AdminPdfServices>().As<IPdfService>().InstancePerRequest();
        builder.RegisterType<Nop.Plugin.Widgets.AdminFeatures.Filters.ProductOrderFilter>().As<IFilterProvider>().InstancePerRequest();
        builder.RegisterType<Nop.Plugin.Widgets.AdminFeatures.Services.AdminMessageTokenProvider>().As<IMessageTokenProvider>().InstancePerRequest();
    }

    public int Order
    {
        get { return 10; }
    }
}