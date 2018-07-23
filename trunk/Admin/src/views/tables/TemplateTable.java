package views.tables;

import java.util.List;

import model.Customer;
import model.DesignTemplate;
import model.DesignTemplateCategory;
import model.User;

import org.tepi.filtertable.FilterGenerator;

import util.GenesysUtilities;
import views.tables.editwindows.TemplateEditWindow;

import com.admin.ui.CurrentUser;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.data.Container.Filter;
import com.vaadin.data.util.BeanContainer;
import com.vaadin.data.util.filter.Compare;
import com.vaadin.data.util.filter.Or;
import com.vaadin.server.ExternalResource;
import com.vaadin.ui.AbstractField;
import com.vaadin.ui.AbstractSelect.ItemCaptionMode;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.ComboBox;
import com.vaadin.ui.CustomComponent;
import com.vaadin.ui.CustomTable;
import com.vaadin.ui.CustomTable.ColumnGenerator;
import com.vaadin.ui.Field;
import com.vaadin.ui.Image;
import com.vaadin.ui.VerticalLayout;

import components.CustomFieldButton;
import components.GenericTable;

public class TemplateTable extends CustomComponent implements ClickListener {

    private class TemplateFilterGenerator implements FilterGenerator {

        public TemplateFilterGenerator() {
            super();
        }

        @Override
        public Filter generateFilter(Object propertyId, Object value) {
            if ("designTemplateCategory.customer.description".equals(propertyId)) {
                if (value != null && value instanceof Integer) {
                    try {
                        return new Compare.Equal("designTemplateCategory.customer.id", value);
                    } catch (Exception e) {

                    }
                }
            } else if ("designTemplateCategory.name".equals(propertyId)) {
                if (value != null && value instanceof Integer) {
                    try {
                        return new Compare.Equal("designTemplateCategory.id", value);
                    } catch (Exception e) {

                    }
                }
            }
            return null;
        }

        @Override
        public AbstractField<?> getCustomFilterComponent(Object propertyId) {
            if ("designTemplateCategory.customer.description".equals(propertyId)) {
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
            } else if ("designTemplateCategory.name".equals(propertyId)) {
                ComboBox cmb = new ComboBox();
                JPAContainer<DesignTemplateCategory> container = JPAContainerFactory.makeJndi(DesignTemplateCategory.class);
                container.addNestedContainerProperty("customer.id");
                cmb.setContainerDataSource(container);
                /*if (!((AdminUI)UI.getCurrent()).isUserInRole("admin")) {
                    container.addContainerFilter(new Compare.Equal("customer.id", ((AdminUI)UI.getCurrent()).getUserCustomers().getId()));
                }*/

                List<Customer> customers = currentUser.getUserCustomers();
                if (customers.size() > 0) {
                    Filter[] filters = new Filter[customers.size()];
                    int i = 0;
                    for (Customer c : customers) {
                        filters[i++] = new Compare.Equal("customer.id", c.getId());
                    }
                    Filter f= new Or(filters);
                    container.addContainerFilter(f);
                }

                cmb.setItemCaptionPropertyId("name");
                cmb.setItemCaptionMode(ItemCaptionMode.PROPERTY);
                cmb.setNullSelectionAllowed(true);
                cmb.setSizeFull();
                return cmb;
            } else if ("Delete".equals(propertyId)) {
                CustomFieldButton addNew = new CustomFieldButton("Add New", new ClickListener() {

                    @Override
                    public void buttonClick(ClickEvent event) {
                    	List<Customer> customers = currentUser.getUserCustomers();
                        EntityItem<DesignTemplate> item = templates.createEntityItem(new DesignTemplate());
                        getUI().addWindow(new TemplateEditWindow(item, customers));
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

    private JPAContainer<DesignTemplate> templates = JPAContainerFactory.makeJndi(DesignTemplate.class);
    private GenericTable table = new GenericTable(templates, this);
    private VerticalLayout vLayout = new VerticalLayout();
    private CurrentUser currentUser;

    public TemplateTable(CurrentUser currentUser) {
        this.currentUser = currentUser;
        initObjects();
        initLayout();
    }

    private void initObjects() {
        templates.addNestedContainerProperty("designTemplateCategory.*");
        templates.addNestedContainerProperty("designTemplateCategory.customer.description");
        templates.addNestedContainerProperty("designTemplateCategory.customer.id");

        List<Customer> customers = currentUser.getUserCustomers();
        if (customers.size() > 0) {
            if (customers.size() > 1) {
                table.setVisibleColumns(new String[]{"id","name","designTemplateCategory.customer.description","designTemplateCategory.name"});
            } else {
                table.setVisibleColumns(new String[]{"id","name","designTemplateCategory.name"});
            }
            Filter[] filters = new Filter[customers.size()];
            int i = 0;
            for (Customer c : customers) {
                filters[i++] = new Compare.Equal("designTemplateCategory.customer.id", c.getId());
            }
            Filter f= new Or(filters);
            templates.addContainerFilter(f);
        } else {
            table.setVisibleColumns(new String[]{"id","name","designTemplateCategory.customer.description","designTemplateCategory.name"});
        }

        table.setColumnHeader("id","Id");
        table.setColumnHeader("name","Name");
        table.setColumnHeader("designTemplateCategory.customer.description","Customer");
        table.setColumnHeader("designTemplateCategory.name","Category");
        table.setColumnHeader("productTypeId","Type");
        table.setFilterGenerator(new TemplateFilterGenerator());

        table.addGeneratedColumn("Image", new ColumnGenerator() {

            @Override
            public Object generateCell(CustomTable source, final Object itemId,
                    Object columnId) {
                Image image = new Image(null, new ExternalResource(GenesysUtilities.getTemplateImageURL(itemId)));
                return image;
            }

        });
        table.setColumnHeader("Image","Image");

        /*table.addGeneratedColumn("OpenTemplate", new ColumnGenerator() {

            @Override
            public Object generateCell(CustomTable source, final Object itemId,
                    Object columnId) {
                Button button = new Button("Open");
                button.addClickListener(new ClickListener() {

                    @Override
                    public void buttonClick(ClickEvent event) {
                        ProductSelectorDialog dialog = new ProductSelectorDialog(templates.getItem(itemId).getEntity());
                        UI.getCurrent().addWindow(dialog);
                    }

                });
                return button;
            }

        });
        table.setColumnHeader("OpenTemplate","Open Template");*/
        table.init();
        /*table.setFilterFieldVisible("OpenTemplate", false);
        table.setColumnWidth("OpenTemplate", 150);*/
        table.setFilterFieldVisible("Image", false);
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
        	List<Customer> customers = currentUser.getUserCustomers();
            EntityItem<DesignTemplate> item = templates.createEntityItem(templates.getItem(event.getButton().getData()).getEntity());
            getUI().addWindow(new TemplateEditWindow(item, customers));
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}