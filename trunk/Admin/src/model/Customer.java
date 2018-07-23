package model;

import java.io.Serializable;
import java.util.List;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.JoinTable;
import javax.persistence.Lob;
import javax.persistence.ManyToMany;
import javax.persistence.NamedQuery;
import javax.persistence.OneToMany;
import javax.persistence.Table;


/**
 * The persistent class for the customers database table.
 * 
 */
@Entity
@Table(name="customers")
@NamedQuery(name="Customer.findAll", query="SELECT c FROM Customer c")
public class Customer implements Serializable {
	private static final long serialVersionUID = 1L;

	@Id
	@GeneratedValue(strategy=GenerationType.IDENTITY)
	private int id;

	private String address;

	@Lob
	@Column(name="config_json")
	private String configJson;

	private String description;

	private String domain;

	@Column(name="email_address")
	private String emailAddress;

	@Column(name="id_key")
	private String idKey;

	private String logo;

	//bi-directional many-to-one association to Barcode
	@OneToMany(mappedBy="customer")
	private List<Barcode> barcodes;

	//bi-directional many-to-one association to DesignTemplateCategory
	@OneToMany(mappedBy="customer")
	private List<DesignTemplateCategory> designTemplateCategories;

	//bi-directional many-to-many association to User
	@ManyToMany(mappedBy="customers")
	private List<User> users;
	
	@OneToMany(mappedBy="customer")
	private List<OrderItem> orderItems;
	
	@OneToMany(mappedBy="productCustomer")
	private List<Product> products;

	//bi-directional many-to-many association to Permission
	@ManyToMany
	@JoinTable(
		name="customer_permissions"
		, joinColumns={
			@JoinColumn(name="customer_id")
			}
		, inverseJoinColumns={
			@JoinColumn(name="permission_id")
			}
		)
	private List<Permission> permissions;

	public Customer() {
	}

	public int getId() {
		return this.id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public String getAddress() {
		return this.address;
	}

	public void setAddress(String address) {
		this.address = address;
	}

	public String getConfigJson() {
		return this.configJson;
	}

	public void setConfigJson(String configJson) {
		this.configJson = configJson;
	}

	public String getDescription() {
		return this.description;
	}

	public void setDescription(String description) {
		this.description = description;
	}

	public String getDomain() {
		return this.domain;
	}

	public void setDomain(String domain) {
		this.domain = domain;
	}

	public String getEmailAddress() {
		return this.emailAddress;
	}

	public void setEmailAddress(String emailAddress) {
		this.emailAddress = emailAddress;
	}

	public String getIdKey() {
		return this.idKey;
	}

	public void setIdKey(String idKey) {
		this.idKey = idKey;
	}

	public String getLogo() {
		return this.logo;
	}

	public void setLogo(String logo) {
		this.logo = logo;
	}

	public List<Barcode> getBarcodes() {
		return this.barcodes;
	}

	public void setBarcodes(List<Barcode> barcodes) {
		this.barcodes = barcodes;
	}

	public Barcode addBarcode(Barcode barcode) {
		getBarcodes().add(barcode);
		barcode.setCustomer(this);

		return barcode;
	}

	public Barcode removeBarcode(Barcode barcode) {
		getBarcodes().remove(barcode);
		barcode.setCustomer(null);

		return barcode;
	}

	public List<DesignTemplateCategory> getDesignTemplateCategories() {
		return this.designTemplateCategories;
	}

	public void setDesignTemplateCategories(List<DesignTemplateCategory> designTemplateCategories) {
		this.designTemplateCategories = designTemplateCategories;
	}

	public DesignTemplateCategory addDesignTemplateCategory(DesignTemplateCategory designTemplateCategory) {
		getDesignTemplateCategories().add(designTemplateCategory);
		designTemplateCategory.setCustomer(this);

		return designTemplateCategory;
	}

	public DesignTemplateCategory removeDesignTemplateCategory(DesignTemplateCategory designTemplateCategory) {
		getDesignTemplateCategories().remove(designTemplateCategory);
		designTemplateCategory.setCustomer(null);

		return designTemplateCategory;
	}
	
	public List<Product> getProducts() {
		return this.products;
	}

	public void setProducts(List<Product> products) {
		this.products = products;
	}

	public Product addProduct(Product product) {
		getProducts().add(product);
		product.setCustomer(this);

		return product;
	}

	public Product removeProduct(Product product) {
		getProducts().remove(product);
		product.setCustomer(null);

		return product;
	}

	public List<User> getUsers() {
		return this.users;
	}

	public void setUsers(List<User> users) {
		this.users = users;
	}

	public List<Permission> getPermissions() {
		return this.permissions;
	}

	public void setPermissions(List<Permission> permissions) {
		this.permissions = permissions;
	}

}