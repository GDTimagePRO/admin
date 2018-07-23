package com.example.vaadindata;

import java.io.Serializable;
import java.util.Random;

import javax.servlet.annotation.WebServlet;

import com.vaadin.annotations.PreserveOnRefresh;
import com.vaadin.annotations.Push;
import com.vaadin.annotations.Theme;
import com.vaadin.annotations.VaadinServletConfiguration;
import com.vaadin.data.Property;
import com.vaadin.data.Property.ValueChangeEvent;
import com.vaadin.data.fieldgroup.FieldGroup;
import com.vaadin.data.util.BeanItem;
import com.vaadin.data.util.BeanItemContainer;
import com.vaadin.data.util.ObjectProperty;
import com.vaadin.server.VaadinRequest;
import com.vaadin.server.VaadinServlet;
import com.vaadin.ui.Button;
import com.vaadin.ui.Button.ClickEvent;
import com.vaadin.ui.MenuBar.MenuItem;
import com.vaadin.ui.Component;
import com.vaadin.ui.FormLayout;
import com.vaadin.ui.HorizontalLayout;
import com.vaadin.ui.MenuBar;
import com.vaadin.ui.Notification;
import com.vaadin.ui.TabSheet;
import com.vaadin.ui.Table;
import com.vaadin.ui.TextField;
import com.vaadin.ui.UI;
import com.vaadin.ui.VerticalLayout;

@Push
@SuppressWarnings("serial")
@Theme("vaadindata")
@PreserveOnRefresh
public class VaadinDataUI extends UI 
{
	private static final String[] NAME_LIST = new String[] { "MARY","PATRICIA","LINDA","BARBARA","ELIZABETH","JENNIFER","MARIA","SUSAN","MARGARET","DOROTHY","LISA","NANCY","KAREN","BETTY","HELEN","SANDRA","DONNA","CAROL","RUTH","SHARON","MICHELLE","LAURA","SARAH","KIMBERLY","DEBORAH","JESSICA","SHIRLEY","CYNTHIA","ANGELA","MELISSA","BRENDA","AMY","ANNA","REBECCA","VIRGINIA","KATHLEEN","PAMELA","MARTHA","DEBRA","AMANDA","STEPHANIE","CAROLYN","CHRISTINE","MARIE","JANET","CATHERINE","FRANCES","ANN","JOYCE","DIANE","ALICE","JULIE","HEATHER","TERESA","DORIS","GLORIA","EVELYN","JEAN","CHERYL","MILDRED","KATHERINE","JOAN","ASHLEY","JUDITH","ROSE","JANICE","KELLY","NICOLE","JUDY","CHRISTINA","KATHY","THERESA","BEVERLY","DENISE","TAMMY","IRENE","JANE","LORI","RACHEL","MARILYN","ANDREA","KATHRYN","LOUISE","SARA","ANNE","JACQUELINE","WANDA","BONNIE" };

	@WebServlet(value = "/*", asyncSupported = true)
	@VaadinServletConfiguration(productionMode = false, ui = VaadinDataUI.class)
	public static class Servlet extends VaadinServlet {}

	public static final class TableRowBean implements Serializable
	{
		private final int _id; 
		public int getId() { return _id; }
		
		private String _firstName;
		public String getFirstName() { return _firstName; }
		public void setFirstName(String value) { _firstName = value; }

		private String _lastName;
		public String getLastName() { return _lastName; }
		public void setLastName(String value) { _lastName = value; }

		private int _age;
		public int getAge() { return _age; }
		public void setAge(int value) { _age = value; }
		
		public TableRowBean(int id, String firstName, String lastName, int age)
		{
			_id = id;
			_firstName = firstName;
			_lastName = lastName;
			_age = age;
		}
	}
	
	private static BeanItemContainer<TableRowBean> createTableData()
	{
		BeanItemContainer<TableRowBean> container = new BeanItemContainer<TableRowBean>(TableRowBean.class);
		Random rnd = new Random();
		for(int i = 0; i<1000; i++)
		{
			container.addBean(new TableRowBean(
					i,
					NAME_LIST[rnd.nextInt(NAME_LIST.length)],
					NAME_LIST[rnd.nextInt(NAME_LIST.length)],
					rnd.nextInt(80) + 19
				));
		}
		return container;
	}

	private Component createTableTabUI()
	{
		final BeanItemContainer<TableRowBean> tableData = createTableData();
		
		final HorizontalLayout rootLayout = new HorizontalLayout();
		rootLayout.setMargin(true);
		rootLayout.setSpacing(true);
		rootLayout.setHeight("100%");

		final Table table = new Table();
		table.setContainerDataSource(tableData);
		table.setSelectable(true);
		table.setImmediate(true);
		rootLayout.addComponent(table);
		rootLayout.setExpandRatio(table, 1F);

		final FormLayout formLayout = new FormLayout();
		formLayout.setMargin(true);
		formLayout.setSpacing(true);
		rootLayout.addComponent(formLayout);
		
		table.addValueChangeListener(new Table.ValueChangeListener()
		{
			public void valueChange(ValueChangeEvent event)
			{				
				TableRowBean selectedBean = (TableRowBean)table.getValue();
				if(selectedBean == null)
				{
					formLayout.setVisible(false);
					return;
				}
				
				formLayout.setVisible(true);
				BeanItem<TableRowBean> item = tableData.getItem(selectedBean);
				final FieldGroup fieldGroup = new FieldGroup(item);
				formLayout.removeAllComponents();				
				formLayout.addComponent(fieldGroup.buildAndBind("First Name", "firstName"));
				formLayout.addComponent(fieldGroup.buildAndBind("Last Name", "lastName"));
				formLayout.addComponent(fieldGroup.buildAndBind("Age", "age"));
				
				final Button commit = new Button("Commit");
				formLayout.addComponent(commit);
				commit.addClickListener(new Button.ClickListener()
				{
					public void buttonClick(ClickEvent event)
					{
						try
						{
							fieldGroup.commit();
						}
						catch(Exception e)
						{
							Notification.show(":_(");
						};
					}
				});
				
				formLayout.setImmediate(true);
			}
		});		

		return rootLayout; 
	}
	
	
	private Component createEmptyUIPage()
	{
		VerticalLayout rootLayout = new VerticalLayout();
		
		Property<String> myProperty = new ObjectProperty<String>("Hello World");
				
		TextField textFieldA = new TextField("A", myProperty);
		textFieldA.setImmediate(true);
		
		TextField textFieldB = new TextField("B" ,myProperty);		
		textFieldB.setImmediate(true);
		
		rootLayout.addComponent(textFieldA);
		rootLayout.addComponent(textFieldB);
		return rootLayout;
	}
	
	@Override
	protected void init(VaadinRequest request)
	{
		final VerticalLayout rootLayout = new VerticalLayout();
		rootLayout.setMargin(false);
		rootLayout.setSpacing(false);
		rootLayout.setSizeFull();
		setContent(rootLayout);
		
		
		final MenuBar menuBar = new MenuBar();
		rootLayout.addComponent(menuBar); 

		
		final TabSheet tabSheet = new TabSheet();
		rootLayout.addComponent(tabSheet);
		rootLayout.setExpandRatio(tabSheet, 1F);
		
		
		final MenuBar.MenuItem demoMenuItem = menuBar.addItem("Tools", null, null);		
		demoMenuItem.addItem("Add Table", new MenuBar.Command()
		{
			@Override
			public void menuSelected(MenuItem selectedItem)
			{
				Notification.show("Not implemented.");
				tabSheet.addTab(createTableTabUI(), "Table");
			}
		});
		demoMenuItem.addItem("Add Empty Page", new MenuBar.Command()
		{
			@Override
			public void menuSelected(MenuItem selectedItem)
			{
				tabSheet.addTab(createEmptyUIPage(), "Blah");
			}
		});
	}
}