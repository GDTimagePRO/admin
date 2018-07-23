package model;

import java.io.Serializable;
import java.security.Principal;
import java.util.ArrayList;
import java.util.List;

import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.EntityManager;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.JoinTable;
import javax.persistence.ManyToMany;
import javax.persistence.NamedQueries;
import javax.persistence.NamedQuery;
import javax.persistence.OneToOne;
import javax.persistence.Table;
import javax.persistence.TypedQuery;

import com.vaadin.addon.jpacontainer.provider.jndijta.JndiAddresses;
import com.vaadin.cdi.access.JaasAccessControl;


/**
 * The persistent class for the users database table.
 * 
 */
@Entity
@Table(name="users")
@NamedQueries({
	@NamedQuery(name="User.findAll", query="SELECT u FROM User u"),
	@NamedQuery(name="User.GetUser", query="Select u FROM User u WHERE u.username=:username")
})
public class User implements Serializable {
	private static final long serialVersionUID = 1L;

	@Id
	@GeneratedValue(strategy=GenerationType.IDENTITY)
	private int id;

	private String password;
	
	@Column(name="username")
	private String username;
	
	@OneToOne
	@JoinColumn(name="username", referencedColumnName="username", insertable=false, updatable=false)
	private UserGroup userGroup;

	//bi-directional many-to-many association to Customer
	@ManyToMany
	@JoinTable(
		name="users_customers"
		, joinColumns={
			@JoinColumn(name="user_id")
			}
		, inverseJoinColumns={
			@JoinColumn(name="customers_id")
			}
		)
	private List<Customer> customers;

	//bi-directional many-to-many association to Permission
	@ManyToMany
	@JoinTable(
		name="user_permissions"
		, joinColumns={
			@JoinColumn(name="user_id")
			}
		, inverseJoinColumns={
			@JoinColumn(name="permission_id")
			}
		)
	private List<Permission> permissions;

	public User() {
		username = "";
		password = "";
		userGroup = new UserGroup();
		customers = new ArrayList<Customer>();
		permissions = new ArrayList<Permission>();
	}

	public int getId() {
		return this.id;
	}

	public void setId(int id) {
		this.id = id;
	}
	
	public String getUsername() {
		return this.username;
	}
	
	public void setUsername(String username) {
		this.username = username;
	}

	public String getPassword() {
		return this.password;
	}

	public void setPassword(String password) {
		this.password = password;
	}

	public UserGroup getUserGroup() {
		return this.userGroup;
	}

	public void setUserGroup(UserGroup userGroup) {
		this.userGroup = userGroup;
	}

	public List<Customer> getCustomers() {
		return this.customers;
	}

	public void setCustomers(List<Customer> customers) {
		this.customers = customers;
	}

	public List<Permission> getPermissions() {
		return this.permissions;
	}

	public void setPermissions(List<Permission> permissions) {
		this.permissions = permissions;
	}
	
	public List<Permission> getAllPermissions() {
		List<Permission> p = new ArrayList<Permission>();
		for (Customer c : getCustomers()) {
			p.addAll(c.getPermissions());
		}
		p.addAll(getPermissions());
		return p;
	}
	


}