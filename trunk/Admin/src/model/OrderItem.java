package model;

import java.io.Serializable;
import java.sql.Timestamp;
import java.util.List;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.JoinColumns;
import javax.persistence.Lob;
import javax.persistence.ManyToOne;
import javax.persistence.NamedQuery;
import javax.persistence.OneToMany;
import javax.persistence.OneToOne;
import javax.persistence.Table;

import org.eclipse.persistence.annotations.Cache;


/**
 * The persistent class for the order_items database table.
 * 
 */
@Entity
@Table(name="order_items")
@NamedQuery(name="OrderItem.findAll", query="SELECT o FROM OrderItem o")
@Cache(alwaysRefresh=true, expiry=300000)
public class OrderItem implements Serializable {
	private static final long serialVersionUID = 1L;
	
	public static final int PROCESSING_STAGE_PENDING_CANCELED		= 100;
	public static final int PROCESSING_STAGE_PENDING_CONFIRMATION	= 200;
	public static final int PROCESSING_STAGE_PENDING_CART_ORDER		= 300;
	public static final int PROCESSING_STAGE_PENDING_RENDERING		= 350;
	public static final int PROCESSING_STAGE_READY					= 400;
	public static final int PROCESSING_STAGE_PRINTED				= 425;
	public static final int PROCESSING_STAGE_SHIPPED				= 450;
	public static final int PROCESSING_STAGE_ARCHIVED				= 500;

	@Id
	@GeneratedValue(strategy=GenerationType.IDENTITY)
	private int id;

	@JoinColumns(value = { @JoinColumn(name="barcode", referencedColumnName="barcode", updatable=false, insertable=false), @JoinColumn(name="customer_id", referencedColumnName="customer_id", updatable=false, insertable=false) })
	private Barcode barcode;

	@Lob
	@Column(name="config_json")
	private String configJson;

	@ManyToOne
	@JoinColumn(name="customer_id")
	private Customer customer;

	@Column(name="date_created")
	private Timestamp dateCreated;

	@Column(name="external_order_id")
	private long externalOrderId;

	@Column(name="external_order_status")
	private int externalOrderStatus;
	
	@Column(name="external_order_options")
	private String externalOrderOptions;

	@Column(name="external_system_name")
	private String externalSystemName;

	@Column(name="external_user_id")
	private int externalUserId;

	//bi-directional many-to-one association to Design
	@OneToMany(mappedBy="orderItem")
	private List<Design> designs;

	//bi-directional one-to-one association to ShippingInformation
	@OneToOne(mappedBy="orderItem")
	private ShippingInformation shippingInformation;

	@ManyToOne
	@JoinColumn(name="processing_stages_id")
	private OrderItemsProcessingStageName processingStageName;

	public OrderItem() {
	}

	public int getId() {
		return this.id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public Barcode getBarcode() {
		return this.barcode;
	}

	public void setBarcode(Barcode barcode) {
		this.barcode = barcode;
	}

	public String getConfigJson() {
		return this.configJson;
	}

	public void setConfigJson(String configJson) {
		this.configJson = configJson;
	}

	public Customer getCustomer() {
		return this.customer;
	}

	public void setCustomer(Customer customer) {
		this.customer = customer;
	}

	public Timestamp getDateCreated() {
		return this.dateCreated;
	}

	public void setDateCreated(Timestamp dateCreated) {
		this.dateCreated = dateCreated;
	}

	public long getExternalOrderId() {
		return this.externalOrderId;
	}

	public void setExternalOrderId(long externalOrderId) {
		this.externalOrderId = externalOrderId;
	}

	public int getExternalOrderStatus() {
		return this.externalOrderStatus;
	}

	public void setExternalOrderStatus(int externalOrderStatus) {
		this.externalOrderStatus = externalOrderStatus;
	}
	
	public String getExternalOrderOptions() {
		return this.externalOrderOptions;
	}
	
	public void setExternalOrderOptions(String externalOrderOptions) {
		this.externalOrderOptions = externalOrderOptions;
	}

	public String getExternalSystemName() {
		return this.externalSystemName;
	}

	public void setExternalSystemName(String externalSystemName) {
		this.externalSystemName = externalSystemName;
	}

	public int getExternalUserId() {
		return this.externalUserId;
	}

	public void setExternalUserId(int externalUserId) {
		this.externalUserId = externalUserId;
	}

	public List<Design> getDesigns() {
		return this.designs;
	}

	public void setDesigns(List<Design> designs) {
		this.designs = designs;
	}

	public Design addDesign(Design design) {
		getDesigns().add(design);
		design.setOrderItem(this);

		return design;
	}

	public Design removeDesign(Design design) {
		getDesigns().remove(design);
		design.setOrderItem(null);

		return design;
	}

	public ShippingInformation getShippingInformation() {
		return this.shippingInformation;
	}

	public void setShippingInformation(ShippingInformation shippingInformation) {
		this.shippingInformation = shippingInformation;
	}

	public OrderItemsProcessingStageName getProcessingStagesId() {
		return this.processingStageName;
	}

	public void setProcessingStagesId(OrderItemsProcessingStageName processingStageName) {
		this.processingStageName = processingStageName;
	}

}