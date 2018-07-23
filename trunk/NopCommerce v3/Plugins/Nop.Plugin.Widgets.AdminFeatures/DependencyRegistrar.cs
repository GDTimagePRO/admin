using Autofac;
using Autofac.Builder;
using Autofac.Core;
using Autofac.Integration.Mvc;
using Nop.Core.Infrastructure;
using Nop.Core.Infrastructure.DependencyManagement;
using Nop.Services.Common;
using Nop.Services.Messages;

public class DependencyRegistrar : IDependencyRegistrar
{
    public virtual void Register(ContainerBuilder builder, ITypeFinder typeFinder)
    {
        builder.RegisterType<Nop.Plugin.Widgets.AdminFeatures.Services.AdminPdfServices>().As<IPdfService>().InstancePerHttpRequest();
        builder.RegisterType<Nop.Plugin.Widgets.AdminFeatures.Services.AdminMessageTokenProvider>().As<IMessageTokenProvider>().InstancePerHttpRequest();
    }

    public int Order
    {
        get { return 10; }
    }
}