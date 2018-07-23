package model;

import java.io.Serializable;
import java.util.List;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.Lob;
import javax.persistence.ManyToOne;
import javax.persistence.NamedQuery;
import javax.persistence.Table;
import javax.validation.constraints.NotNull;


/**
 * The persistent class for the products database table.
 * 
 */
@Entity
@Table(name="products")
@NamedQuery(name="Product.findAll", query="SELECT p FROM Product p")
public class Product implements Serializable {
	private static final long serialVersionUID = 1L;

	@Id
	@GeneratedValue(strategy=GenerationType.IDENTITY)
	private int id;

	@Column(name="allow_graphics")
	private boolean allowGraphics;

	@Column(name="code")
	@NotNull
	private String code;

	@NotNull
	@Column(name="color_model")
	private String colorModel;

	@Lob
	@Column(name="config_json")
	@NotNull
	private String configJson;

	@Column(name="frame_height")
	private float frameHeight;

	@Column(name="frame_width")
	private float frameWidth;

	@Column(name="height")
	private float height;

	@Column(name="long_name")
	@NotNull
	private String longName;

	@Column(name="product_type_id")
	private int productTypeId;

	@Column(name="shape_id")
	@NotNull
	private String shapeId;

	private float width;

	//bi-directional many-to-one association to ProductsCategory
	@ManyToOne
	@JoinColumn(name="category_id")
	@NotNull
	private ProductsCategory productsCategory;
	
	@ManyToOne
	@JoinColumn(name="customer", nullable = true)
	private Customer productCustomer;

	public Product() {
		code = "";
		shapeId = "Rectangular";
		longName = "";
		configJson = "";
		colorModel = "1_BIT";
		productCustomer = null;
		productTypeId = 1;
	}
	
	public Product(Product p) {
		this.code = p.code;
		this.shapeId = p.shapeId;
		this.allowGraphics = p.allowGraphics;
		this.colorModel = p.colorModel;
		this.configJson = p.configJson;
		this.frameHeight = p.frameHeight;
		this.frameWidth = p.frameWidth;
		this.height = p.height;
		this.longName = p.longName;
		this.productsCategory = p.productsCategory;
		this.productTypeId = p.productTypeId;
		this.width = p.width;
	}

	public int getId() {
		return this.id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public boolean getAllowGraphics() {
		return this.allowGraphics;
	}

	public void setAllowGraphics(boolean allowGraphics) {
		this.allowGraphics = allowGraphics;
	}

	public String getCode() {
		return this.code;
	}

	public void setCode(String code) {
		this.code = code;
	}

	public String getColorModel() {
		return this.colorModel;
	}

	public void setColorModel(String colorModel) {
		this.colorModel = colorModel;
	}

	public String getConfigJson() {
		return this.configJson;
	}

	public void setConfigJson(String configJson) {
		this.configJson = configJson;
	}

	public float getFrameHeight() {
		return this.frameHeight;
	}

	public void setFrameHeight(float frameHeight) {
		this.frameHeight = frameHeight;
	}

	public float getFrameWidth() {
		return this.frameWidth;
	}

	public void setFrameWidth(float frameWidth) {
		this.frameWidth = frameWidth;
	}

	public float getHeight() {
		return this.height;
	}

	public void setHeight(float height) {
		this.height = height;
	}

	public String getLongName() {
		return this.longName;
	}

	public void setLongName(String longName) {
		this.longName = longName;
	}

	public int getProductTypeId() {
		return this.productTypeId;
	}

	public void setProductTypeId(int productTypeId) {
		this.productTypeId = productTypeId;
	}

	public String getShapeId() {
		return this.shapeId;
	}

	public void setShapeId(String shapeId) {
		this.shapeId = shapeId;
	}

	public float getWidth() {
		return this.width;
	}

	public void setWidth(float width) {
		this.width = width;
	}

	public ProductsCategory getProductsCategory() {
		return this.productsCategory;
	}

	public void setProductsCategory(ProductsCategory productsCategory) {
		this.productsCategory = productsCategory;
	}
	
	public String getFullname() {
		return getId() + ": " + getCode();
	}
	
	public Customer getCustomer() {
		return this.productCustomer;
	}

	public void setCustomer(Customer productCustomer) {
		this.productCustomer = productCustomer;
	}


}