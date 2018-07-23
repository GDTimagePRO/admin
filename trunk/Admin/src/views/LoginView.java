package views;

import java.io.File;

import javax.annotation.PostConstruct;
import javax.inject.Inject;

import com.vaadin.cdi.CDIView;
import com.vaadin.event.ShortcutAction.KeyCode;
import com.vaadin.navigator.View;
import com.vaadin.navigator.ViewChangeListener.ViewChangeEvent;
import com.vaadin.server.FileResource;
import com.vaadin.server.VaadinService;
import com.vaadin.shared.ui.MarginInfo;
import com.vaadin.ui.Alignment;
import com.vaadin.ui.Button;
import com.vaadin.ui.Component;
import com.vaadin.ui.CustomComponent;
import com.vaadin.ui.Image;
import com.vaadin.ui.PasswordField;
import com.vaadin.ui.TextField;
import com.vaadin.ui.VerticalLayout;

@CDIView(LoginView.NAME)
public class LoginView extends CustomComponent implements View {

	public class LoginEvent {
		private final String username;
		private final String password;

		public LoginEvent(String username, String password) {
			this.username = username;
			this.password = password;
		}

		public String getUsername() {
			return username;
		}

		public String getPassword() {
			return password;
		}
	}
	
	public static final String NAME = "login";
	private TextField user;
	private PasswordField password;
	private Button loginButton;

	@PostConstruct
	public void init() {
		setSizeFull();
		setCompositionRoot(createCompositionRoot());
	}

	@Inject
	private javax.enterprise.event.Event<LoginEvent> loginEvent;

	protected Component createCompositionRoot() {
		setSizeFull();
		
		Image image = new Image(null, new FileResource(new File(VaadinService.getCurrent().getBaseDirectory().getAbsolutePath() + "/WEB-INF/images/gdt_logo.png")));

        // Create the user input field
        user = new TextField();
        user.setWidth("300px");
        user.setInputPrompt("Username");
        user.setInvalidAllowed(false);
        user.focus();

        // Create the password input field
        password = new PasswordField();
        password.setWidth("300px");
        password.setValue("");
        password.setInputPrompt("Password");
        password.setNullRepresentation("");

        // Create login button
        loginButton = new Button("Login");
        loginButton.addClickListener(createLoginButtonListener());
        loginButton.setClickShortcut(KeyCode.ENTER);

        
        // Add both to a panel
        VerticalLayout fields = new VerticalLayout(user, password, loginButton);
        fields.setSpacing(true);
        fields.setMargin(new MarginInfo(true, true, true, false));
        fields.setSizeUndefined();
        fields.setComponentAlignment(loginButton, Alignment.BOTTOM_RIGHT);

        // The view root layout
        VerticalLayout center = new VerticalLayout(image, fields);
        center.setComponentAlignment(image, Alignment.MIDDLE_CENTER);
        center.setComponentAlignment(fields, Alignment.MIDDLE_CENTER);
        VerticalLayout viewLayout = new VerticalLayout(center);
        viewLayout.setSizeFull();
        viewLayout.setComponentAlignment(center, Alignment.MIDDLE_CENTER);
        return viewLayout;
	}

	protected Button.ClickListener createLoginButtonListener() {
		return new Button.ClickListener() {
			private static final long serialVersionUID = 3424514570135131495L;

			@Override
			public void buttonClick(Button.ClickEvent event) {
				loginEvent.fire(new LoginEvent(user.getValue(),
						password.getValue()));
			}
		};
	}

	@Override
	public void enter(ViewChangeEvent event) {
		// TODO Auto-generated method stub
	}

}
