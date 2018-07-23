package views.tables.editwindows;

import java.util.Iterator;
import java.util.List;

import org.vaadin.jouni.animator.Animator;
import org.vaadin.jouni.animator.client.CssAnimation;
import org.vaadin.jouni.animator.client.Ease;
import org.vaadin.jouni.dom.client.Css;

import model.Customer;
import model.DesignTemplate;
import model.DesignTemplateCategory;
import model.Product;
import model.TemplateConfig;
import util.FieldBinder;
import util.GenesysUtilities;

import com.google.gson.Gson;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import com.vaadin.addon.jpacontainer.EntityItem;
import com.vaadin.addon.jpacontainer.JPAContainer;
import com.vaadin.addon.jpacontainer.JPAContainerFactory;
import com.vaadin.addon.jpacontainer.fieldfactory.SingleSelectConverter;
import com.vaadin.data.Container.Filter;
import com.vaadin.data.fieldgroup.FieldGroup.CommitException;
import com.vaadin.data.util.filter.Compare;
import com.vaadin.data.util.filter.IsNull;
import com.vaadin.data.util.filter.Or;
import com.vaadin.server.ExternalResource;
import com.vaadin.shared.ui.combobox.FilteringMode;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.Button.ClickListener;
import com.vaadin.ui.ComboBox;
import com.vaadin.ui.Component;
import com.vaadin.ui.FormLayout;
import com.vaadin.ui.HorizontalLayout;
import com.vaadin.ui.Image;
import com.vaadin.ui.Label;
import com.vaadin.ui.Notification;
import com.vaadin.ui.Notification.Type;
import com.vaadin.ui.TextArea;
import com.vaadin.ui.UI;
import com.vaadin.ui.VerticalLayout;
import com.vaadin.ui.Window;
import com.vaadin.ui.themes.ValoTheme;

import components.ProductSelectorDialog;

public class TemplateEditWindow  extends Window {

    private EntityItem<DesignTemplate> item;
    private String message = null;
    List<Customer> customers;

    public TemplateEditWindow(EntityItem<DesignTemplate> item, List<Customer> customers) {
        this.item = item;
        this.customers = customers;
        init();
    }

    public TemplateEditWindow(EntityItem<DesignTemplate> item, String message) {
        this.item = item;
        this.message = message;
        init();
    }

    public void init() {
        this.setModal(true);
        this.center();
        this.setSizeUndefined();
        this.
        setContent(createLayout());
    }

    protected Component createLayout() {
        VerticalLayout root = new VerticalLayout();
        FormLayout layout = new FormLayout();
        root.setMargin(true);
        root.setSizeUndefined();
        Label title = new Label("Template - " + item.getEntity().getId() + " - " + item.getEntity().getName());
        title.setStyleName(ValoTheme.LABEL_H1);
        root.addComponent(title);
        root.addComponent(new Image(null, new ExternalResource(GenesysUtilities.getTemplateImageURL(item.getEntity().getId()))));
        final FieldBinder binder = new FieldBinder(item, customers);

        binder.setBuffered(true);

        final TextArea configJson = new TextArea("Config JSON");
        final TextArea designJSON = new TextArea("Design JSON");
        configJson.setSizeUndefined();
        designJSON.setSizeUndefined();
      
        layout.addComponent(binder.buildAndBind("name"));
        layout.addComponent(binder.buildAndBind("designTemplateCategory"));
        layout.addComponent(designJSON);
        binder.bind(designJSON, "designJson");

        layout.addComponent(configJson);
        binder.bind(configJson, "configJson");

        Button generateConfig = new Button("Generate Config");
        generateConfig.addClickListener(new ClickListener() {
            @Override
            public void buttonClick(ClickEvent event) {
                JsonObject design = new JsonParser().parse(designJSON.getValue()).getAsJsonObject();
                JsonArray elements = design.getAsJsonArray("elements");
                Iterator<JsonElement> itr = elements.iterator();
                String imagePath = null;
                while(itr.hasNext()) {
                    JsonObject element = itr.next().getAsJsonObject();
                    if (element.get("className").getAsString().equals("ImageElement")) {
                        imagePath = element.getAsJsonObject("imageSrc").get("id").getAsString();
                        break;
                    }
                }
                if (imagePath != null) {
                    TemplateConfig config = new Gson().fromJson(configJson.getValue(), TemplateConfig.class);
                    if (config == null) {
                        config = new TemplateConfig();
                    }
                    config.misc.image_palette_1_rid = imagePath;
                    configJson.setValue(new Gson().toJson(config));
                }
            }
        });
        layout.addComponent(generateConfig);

        Button open = new Button("Open");
        open.addClickListener(new ClickListener() {

            @Override
            public void buttonClick(ClickEvent event) {
                if (item.getEntity().getId() == 0) {
                    try {
                        binder.commit();
                        int id = (int) item.getContainer().addEntity(item.getEntity());
                        ProductSelectorDialog dialog = new ProductSelectorDialog(item.getEntity(), customers);
                        UI.getCurrent().addWindow(dialog);
                        item = item.getContainer().createEntityItem(item.getContainer().getItem(id).getEntity());
                        Notification.show("Saved", Type.TRAY_NOTIFICATION);
                    } catch (CommitException e) {
                        Notification.show("Failed to save item", Type.ERROR_MESSAGE);
                        e.printStackTrace();
                    }

                } else {
                    ProductSelectorDialog dialog = new ProductSelectorDialog(item.getEntity(), customers);
                    UI.getCurrent().addWindow(dialog);
                }
            }

        });

        HorizontalLayout buttons = new HorizontalLayout();
        Button save = new Button("Save");
        buttons.addComponent(save);
        Button cancel = new Button("Close");
        buttons.addComponent(cancel);
        buttons.addComponent(open);
        final Label messageLabel = new Label();
        messageLabel.setStyleName("save-label");
        if (message != null) {
            messageLabel.setValue(message);
        }
        buttons.addComponent(messageLabel);
        layout.addComponent(buttons);

        save.addClickListener(new ClickListener(){
            @Override
            public void buttonClick(ClickEvent event) {
                try {
                    binder.commit();
                    int id = (int) item.getContainer().addEntity(item.getEntity());
                    item = item.getContainer().createEntityItem(item.getContainer().getItem(id).getEntity());
                    Notification.show("Saved", Type.TRAY_NOTIFICATION);
                } catch (Exception e) {
                    Notification.show("Failed to save item", Type.ERROR_MESSAGE);
                    e.printStackTrace();
                }
            }
        });

        cancel.addClickListener(new ClickListener(){
            @Override
            public void buttonClick(ClickEvent event) {
                binder.discard();
                TemplateEditWindow.this.close();
            }
        });


        root.addComponent(layout);
        return root;
    }

}