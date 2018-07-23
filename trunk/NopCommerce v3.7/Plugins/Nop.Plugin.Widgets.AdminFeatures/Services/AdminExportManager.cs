using Nop.Core.Domain.Orders;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Xml.Linq;

namespace Nop.Plugin.Widgets.AdminFeatures.Services
{
    class AdminExportManager
    {

        public string ExportOrdersToUPSXML(IList<Order> orders)
        {
            XNamespace ns = "x-schema: OpenShipments.xdr";
            XElement OpenShipments = new XElement(ns + "OpenShipments");

            foreach (var o in orders)
            {
                if (o.ShippingAddress != null)
                {
                    XElement OpenShipment = new XElement("OpenShipment", new XAttribute("ShipmentOption", ""), new XAttribute("ProcessStatus", ""));


                    //Address Information (ShipTo);
                    XElement ShipTo = new XElement("ShipTo");
                    XElement CustomerID = new XElement("CustomerID", o.Id);
                    ShipTo.Add(CustomerID);
                    XElement CompanyOrName = new XElement("CompanyOrName", !String.IsNullOrEmpty(o.ShippingAddress.Company) ? o.ShippingAddress.Company : o.ShippingAddress.FirstName + " " + o.ShippingAddress.LastName);
                    ShipTo.Add(CompanyOrName);
                    XElement Attention = new XElement("Attention", o.ShippingAddress.FirstName + " " + o.ShippingAddress.LastName);
                    ShipTo.Add(Attention);
                    XElement Address1 = new XElement("Address1", o.ShippingAddress.Address1);
                    ShipTo.Add(Address1);
                    XElement Address2 = new XElement("Address2", !String.IsNullOrEmpty(o.ShippingAddress.Address2) ? o.ShippingAddress.Address2 : "");
                    ShipTo.Add(Address2);
                    XElement CityOrTown = new XElement("CityOrTown", o.ShippingAddress.City);
                    ShipTo.Add(CityOrTown);
                    XElement StateProvinceCounty = new XElement("StateProvinceCounty", o.ShippingAddress.StateProvince != null ? o.ShippingAddress.StateProvince.Abbreviation : "");
                    ShipTo.Add(StateProvinceCounty);
                    XElement PostalCode = new XElement("PostalCode", o.ShippingAddress.ZipPostalCode);
                    ShipTo.Add(PostalCode);
                    XElement CountryTerritory = new XElement("CountryTerritory", o.ShippingAddress.Country.TwoLetterIsoCode);
                    ShipTo.Add(CountryTerritory);
                    XElement Telephone = new XElement("Telephone", o.ShippingAddress.PhoneNumber);
                    ShipTo.Add(Telephone);
                    XElement EmailAddress1 = new XElement("EmailAddress", o.ShippingAddress.Email);
                    ShipTo.Add(EmailAddress1);
                    OpenShipment.Add(ShipTo);

                    //Shipment Information (ShipmentInformation)

                    /*<ShipmentInformation> 
                    <ServiceLevel>ES</ServiceLevel> 
                    <PackageType>CP</PackageType> 
                        <NumberOfPackages>1</NumberOfPackages> 
                    <ShipmentActualWeight>70</ShipmentActualWeight> 
                    <DescriptionOfGoods>Computer
                    Hardware</DescriptionOfGoods> 
                    <BillingOption>PP</BillingOption> 
                    </ShipmentInformation> */
                    var totalWeight = 0.0f;
                    foreach (var variant in o.OrderItems)
                    {
                        if (variant != null && variant.ItemWeight != null)
                        {
                            totalWeight += (float)variant.ItemWeight;
                        }
                    }
                    var shiptype = "USL";
                    if (totalWeight > 1.0f)
                    {
                        shiptype = "USG";
                    }
                    else
                    {
                        totalWeight *= 16.0f;
                    }
                    XElement ShipmentInformation = new XElement("ShipmentInformation");
                    XElement ServiceLevel = new XElement("ServiceType", shiptype);
                    ShipmentInformation.Add(ServiceLevel);
                    XElement PackageType = new XElement("PackageType", "CP");
                    ShipmentInformation.Add(PackageType);
                    XElement NumberOfPackages = new XElement("NumberOfPackages", "1");
                    ShipmentInformation.Add(NumberOfPackages);
                    XElement ShipmentActualWeight = new XElement("ShipmentActualWeight", totalWeight);
                    ShipmentInformation.Add(ShipmentActualWeight);
                    XElement BillingOption = new XElement("BillingOption", "PP");
                    ShipmentInformation.Add(BillingOption);
                    XElement DescriptionOfGoods = new XElement("DescriptionOfGoods", "Stamps");
                    ShipmentInformation.Add(DescriptionOfGoods);
                    XElement Reference1 = new XElement("Reference1", "Order ID: " + o.Id);
                    ShipmentInformation.Add(Reference1);
                    OpenShipment.Add(ShipmentInformation);



                    OpenShipments.Add(OpenShipment);
                }
            }

            return OpenShipments.ToString().Replace("xmlns=\"\"", "");
        }
    }
}
