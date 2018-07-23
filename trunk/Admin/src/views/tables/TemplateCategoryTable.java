package views.tables;

import java.util.List;

import model.Customer;
import model.DesignTemplateCategory;
import model.User;

import org.tepi.filtertable.FilterGenerator;

import views.tables.editwindows.TemplateCategoryEditWindow;

import com.admin.ui.CurrentUser;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.data.Container.Filter;
import com.vaadin.data.util.BeanContainer;
import com.vaadin.data.util.filter.Compare;
import com.vaadin.data.util.filter.Or;
import com.vaadin.ui.AbstractField;
import com.vaadin.ui.AbstractSelect.ItemCaptionMode;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.ComboBox;
import com.vaadin.ui.CustomComponent;
import com.vaadin.ui.Field;
import com.vaadin.ui.VerticalLayout;

import components.CustomFieldButton;
import components.GenericTable;

public class TemplateCategoryTable extends CustomComponent implements ClickListener {

    private class TemplateCategoryFilterGenerator implements FilterGenerator {

        public TemplateCategoryFilterGenerator() {
            super();
        }

        @Override
        public Filter generateFilter(Object propertyId, Object value) {
            if ("customer.description".equals(propertyId)) {
                if (value != null && value instanceof Integer) {
                    try {
                        return new Compare.Equal("customer.id", value);
                    } catch (Exception e) {

                    }
                }
            }
            return null;
        }

        @Override
        public AbstractField<?> getCustomFilterComponent(Object propertyId) {
            if ("customer.description".equals(propertyId)) {
                ComboBox cmb = new ComboBox();
                List<Customer> customers = currentUser.getUserCustomers();
                if (customers.size() > 0) {
                    BeanContainer<Integer, Customer> container = new BeanContainer<Integer, Customer>(Customer.class);
                    container.setBeanIdProperty("id");
                    container.addAll(customers);
                    cmb.setContainerDataSource(container);
                } else {
                    cmb.setContainerDataSource(JPAContainerFactory.makeJndi(Customer.class));
                }
                cmb.setItemCaptionPropertyId("description");
                cmb.setItemCaptionMode(ItemCaptionMode.PROPERTY);
                cmb.setNullSelectionAllowed(true);
                cmb.setSizeFull();
                return cmb;
            } else if ("Delete".equals(propertyId)) {
                CustomFieldButton addNew = new CustomFieldButton("Add New", new ClickListener() {

                    @Override
                    public void buttonClick(ClickEvent event) {
                        EntityItem<DesignTemplateCategory> item = templatecategories.createEntityItem(new DesignTemplateCategory());
                        getUI().addWindow(new TemplateCategoryEditWindow(item, currentUser));
                    }

                });
                return addNew;
            } else if ("Edit".equals(propertyId)) {
                CustomFieldButton filter = new CustomFieldButton("Filter", new ClickListener() {

                    @Override
                    public void buttonClick(ClickEvent event) {
                        Button filter = event.getButton();
                        if (filter.getData() == null || filter.getData().equals("filter")) {
                            filter.setData("clear");
                            filter.setCaption("Clear");
                        } else {
                            table.clearFilters();
                            filter.setData("filter");
                            filter.setCaption("Filter");
                        }
                    }

                });
                return filter;
            }
            return null;
        }

        @Override
        public Filter generateFilter(Object propertyId,
                Field<?> originatingField) {
            // TODO Auto-generated method stub
            return null;
        }

        @Override
        public void filterRemoved(Object propertyId) {
            // TODO Auto-generated method stub

        }

        @Override
        public void filterAdded(Object propertyId,
                Class<? extends Filter> filterType, Object value) {
            // TODO Auto-generated method stub

        }

        @Override
        public Filter filterGeneratorFailed(Exception reason,
                Object propertyId, Object value) {
            // TODO Auto-generated method stub
            return null;
        }
    }

    public static String PERMISSION_ACCESS = "admin_access";
    public static String PERMISSION_EDIT = "";
    public static String PERMISSION_DELETE = "";

    private JPAContainer<DesignTemplateCategory> templatecategories = JPAContainerFactory.makeJndi(DesignTemplateCategory.class);
    private GenericTable table = new GenericTable(templatecategories, this);
    private VerticalLayout vLayout = new VerticalLayout();
    private CurrentUser currentUser;

    public TemplateCategoryTable(CurrentUser currentUser) {
        this.currentUser = currentUser;
        initObjects();
        initLayout();
    }

    private void initObjects() {
        templatecategories.addNestedContainerProperty("customer.description");
        templatecategories.addNestedContainerProperty("customer.id");

        List<Customer> customers = currentUser.getUserCustomers();
        if (customers.size() > 0) {
            if (customers.size() > 1) {
                table.setVisibleColumns(new String[]{"id","name","customer.description"});
            } else {
                table.setVisibleColumns(new String[]{"id","name"});
            }
            Filter[] filters = new Filter[customers.size()];
            int i = 0;
            for (Customer c : customers) {
                filters[i++] = new Compare.Equal("customer.id", c.getId());
            }
            Filter f= new Or(filters);
            templatecategories.addContainerFilter(f);
        } else {
            table.setVisibleColumns(new String[]{"id","name","customer.description"});
        }

        table.setColumnHeader("id","Id");
        table.setColumnHeader("name","Name");
        table.setColumnHeader("customer.description","Customer");
        table.setFilterGenerator(new TemplateCategoryFilterGenerator());

        table.init();
        table.setFilterFieldVisible("OpenTemplate", false);
        table.setColumnWidth("OpenTemplate", 150);
    }

    private void initLayout() {
        vLayout.addComponent(table);
        vLayout.setSizeFull();
        setCompositionRoot(vLayout);
        setSizeFull();
    }

    @Override
    public void buttonClick(ClickEvent event) {
        try {
            EntityItem<DesignTemplateCategory> item = templatecategories.createEntityItem(templatecategories.getItem(event.getButton().getData()).getEntity());
            getUI().addWindow(new TemplateCategoryEditWindow(item, currentUser));
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}