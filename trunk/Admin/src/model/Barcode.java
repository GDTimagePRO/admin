package model;

import java.io.Serializable;
import java.sql.Timestamp;

import javax.persistence.Column;
import javax.persistence.EmbeddedId;
import javax.persistence.Entity;
import javax.persistence.JoinColumn;
import javax.persistence.Lob;
import javax.persistence.ManyToOne;
import javax.persistence.NamedQuery;
import javax.persistence.Table;
import javax.persistence.Transient;

import com.google.gson.Gson;


/**
 * The persistent class for the barcodes database table.
 * 
 */
@Entity
@Table(name="barcodes")
@NamedQuery(name="Barcode.findAll", query="SELECT b FROM Barcode b")
public class Barcode implements Serializable {
	private static final long serialVersionUID = 1L;

	@EmbeddedId
	private BarcodePK id;

	@Lob
	@Column(name="config_json")
	private String configJson;

	@Column(name="date_created")
	private Timestamp dateCreated;

	@Column(name="date_used")
	private Timestamp dateUsed;

	private String master;
	
	@Transient
	private BarcodeConfig config;

	//bi-directional many-to-one association to Customer
	@ManyToOne
	@JoinColumn(name="customer_id", referencedColumnName="id", insertable=false, updatable=false)
	private Customer customer;

	public Barcode() {
		id = new BarcodePK("", 0);
		configJson = "";
		master = "Y";
		config = new BarcodeConfig();
	}
	
	public Barcode(Barcode b) {
		this.config = b.config;
		this.configJson = b.configJson;
		this.customer = b.customer;
		this.dateCreated = b.dateCreated;
		this.dateUsed = b.dateUsed;
		this.master = b.master;
		this.id = new BarcodePK();
		id.setBarcode("");
		id.setCustomerId(b.id.getCustomerId());
	}

	public BarcodePK getBarcode() {
		return this.id;
	}

	public void setBarcode(BarcodePK id) {
		this.id = id;
	}

	public String getConfigJson() {
		return this.configJson;
	}

	public void setConfigJson(String configJson) {
		this.configJson = configJson;
		config = getBarcodeConfigFromString(this.configJson);
	}

	public Timestamp getDateCreated() {
		return this.dateCreated;
	}

	public void setDateCreated(Timestamp dateCreated) {
		this.dateCreated = dateCreated;
	}

	public Timestamp getDateUsed() {
		return this.dateUsed;
	}

	public void setDateUsed(Timestamp dateUsed) {
		this.dateUsed = dateUsed;
	}

	public String getMaster() {
		return this.master;
	}

	public void setMaster(String master) {
		this.master = master;
	}

	public Customer getCustomer() {
		return this.customer;
	}

	public void setCustomer(Customer customer) {
		this.customer = customer;
	}
	
	public BarcodeConfig getBarcodeConfig() {
		return config;
	}
	
	public void setBarcodeConfig(BarcodeConfig config) {
		Gson g = new Gson();
		this.config = config;
		configJson = g.toJson(config);
	}
	
	public static BarcodeConfig getBarcodeConfigFromString(String config) {
		if (config == null || config.isEmpty()) {
			return new BarcodeConfig();
		} else {
			Gson g = new Gson();
			return g.fromJson(config, BarcodeConfig.class);
		}
	}
	
	public static String getStringFromBarcodeConfig(BarcodeConfig config) {
		Gson g = new Gson();
		return g.toJson(config);
	}

}