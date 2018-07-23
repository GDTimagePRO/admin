package components;

import java.io.IOException;

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;

import model.Customer;
import model.DesignTemplate;
import model.Product;
import util.HTTPHelper;

import com.admin.ui.AdminSerlvetListener;
import com.google.gson.Gson;
import com.google.gwt.safehtml.shared.UriUtils;

import java.util.List;

import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.data.Container.Filter;
import com.vaadin.data.util.filter.Compare;
import com.vaadin.data.util.filter.IsNull;
import com.vaadin.data.util.filter.Or;
import com.vaadin.shared.ui.combobox.FilteringMode;
import com.vaadin.ui.Alignment;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.ComboBox;
import com.vaadin.ui.Label;
import com.vaadin.ui.Notification;
import com.vaadin.ui.Notification.Type;
import com.vaadin.ui.VerticalLayout;
import com.vaadin.ui.Window;
import com.vaadin.ui.themes.ValoTheme;

public class ProductSelectorDialog extends Window {

    private class SetupResponse {
        public String error;
        public String sid;
        public String url;
    }

    public ProductSelectorDialog(final DesignTemplate template, List<Customer> customers) {
        Label text = new Label("Select a product:");
        final JPAContainer<Product> products = JPAContainerFactory.makeJndi(Product.class);
        final ComboBox productSelection = new ComboBox();
        Button open = new Button("Open");
        Label title = new Label(template.getName());
        title.setStyleName(ValoTheme.LABEL_H1);

        setModal(true);
        setSizeUndefined();
        VerticalLayout root = new VerticalLayout();
        root.setSizeUndefined();
        root.setSpacing(true);
        root.setMargin(true);

        root.addComponent(title);
        root.addComponent(text);
        root.addComponent(productSelection);
        root.addComponent(open);
        root.setComponentAlignment(open, Alignment.MIDDLE_RIGHT);
        setContent(root);
        center();
        setResizable(false);
        
        //building filter
        if ( customers.size() > 0 ) {
        	Filter[] filters = new Filter[customers.size() + 1];
    		int i = 0;
    		for (Customer c : customers) {
    			filters[i++] = new Compare.Equal("productCustomer.id", c.getId());
    		}
    		filters[i++] = new IsNull("productCustomer");
    		Filter f= new Or(filters);
    		products.addContainerFilter(f);
        }
        
        productSelection.setContainerDataSource(products);
        productSelection.setItemCaptionPropertyId("code");
        productSelection.setFilteringMode(FilteringMode.CONTAINS);
        productSelection.setNullSelectionAllowed(false);
        productSelection.setWidth("300px");
        open.addClickListener(new ClickListener() {

            @Override
            public void buttonClick(ClickEvent event) {
                String customerId = UriUtils.encode(template.getDesignTemplateCategory().getCustomer().getIdKey());
                int templateId = template.getId();
                int productId = products.getItem(productSelection.getValue()).getEntity().getId();

                Gson g = new Gson();
                try {
                    String genesys_api = "http://www.genesys.in-stamp.com";
                    try {
                        Context context = new InitialContext();
                        genesys_api = (String) context.lookup(AdminSerlvetListener.APIURL);
                    } catch (NamingException e) {
                        e.printStackTrace();
                    }
                    String output = HTTPHelper.getOutputFromURL(genesys_api + "/SetUpAdmin.php?redirect=false&templateId=" + templateId + "&productId=" + productId + "&sName=" + customerId);
                    SetupResponse response = g.fromJson(output, SetupResponse.class);
                    if (response != null && (response.error == null || response.error.isEmpty())) {
                        getUI().getPage().open(response.url, "_blank");
                    } else {
                        Notification.show("Error opening template", Type.ERROR_MESSAGE);
                    }
                } catch (IOException e) {
                    e.printStackTrace();
                }
                close();
            }

        });
    }
}