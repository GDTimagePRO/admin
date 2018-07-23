package model;

import java.io.Serializable;
import java.sql.Timestamp;

import javax.persistence.CascadeType;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.Lob;
import javax.persistence.ManyToOne;
import javax.persistence.NamedQueries;
import javax.persistence.NamedQuery;
import javax.persistence.Table;

import org.eclipse.persistence.annotations.Cache;
import org.eclipse.persistence.annotations.CacheType;

import com.google.gson.Gson;


/**
 * The persistent class for the designs database table.
 * 
 */
@Entity
@Table(name="designs")
@NamedQueries({
	@NamedQuery(name="Design.findAll", query="SELECT d FROM Design d"),
	@NamedQuery(name="Design.getDesigns", query="SELECT d FROM Design d WHERE d.id IN :designids")
})
@Cache(alwaysRefresh=true, expiry=300000)
public class Design implements Serializable {
	
	public static final int DESIGN_STATE_EDITING = 10;
	public static final int DESIGN_STATE_INQUEUE = 20;
	public static final int DESIGN_STATE_RENDERED = 30;
	public static final int DESIGN_STATE_ARCHIVED = 40;
	
	
	public static final class DesignColor
	{
		public String name;
		public String value;
	}

	public static final class DesignColorPalette
	{
		public DesignColor ink;
	}
	
	public static final class ParsedDesignSceneData
	{
		public DesignColorPalette colors;
	}

	public static final class ParsedDesignData
	{
		public ParsedDesignSceneData scene;
	}
	
	private static final long serialVersionUID = 1L;

	@Id
	@GeneratedValue(strategy=GenerationType.IDENTITY)
	private int id;

	@Lob
	@Column(name="config_json")
	private String configJson;

	@Column(name="date_changed")
	private Timestamp dateChanged;

	@Column(name="date_rendered")
	private Timestamp dateRendered;

	@Lob
	@Column(name="design_json")
	private String designJson;
	
	@Column(name="external_design_options")
	private String externalDesignOptions;

	@Column(name="product_type_id")
	private int productTypeId;

	//bi-directional many-to-one association to OrderItem
	@ManyToOne(cascade = CascadeType.ALL)
	@JoinColumn(name="order_item_id")
	private OrderItem orderItem;

	//bi-directional many-to-one association to Product
	@ManyToOne
	@JoinColumn(name="product_id")
	private Product product;

	//bi-directional many-to-one association to DesignsStateName
	@ManyToOne
	@JoinColumn(name="state")
	private DesignsStateName designsStateName;

	public Design() {
	}

	public int getId() {
		return this.id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public String getConfigJson() {
		return this.configJson;
	}

	public void setConfigJson(String configJson) {
		this.configJson = configJson;
	}

	public Timestamp getDateChanged() {
		return this.dateChanged;
	}

	public void setDateChanged(Timestamp dateChanged) {
		this.dateChanged = dateChanged;
	}

	public Timestamp getDateRendered() {
		return this.dateRendered;
	}

	public void setDateRendered(Timestamp dateRendered) {
		this.dateRendered = dateRendered;
	}

	public String getDesignJson() {
		return this.designJson;
	}

	public void setDesignJson(String designJson) {
		this.designJson = designJson;
	}
	
	public String getExternalDesignOptions() {
		return this.externalDesignOptions;
	}

	public void setExternalDesignOptions(String externalDesignOptions) {
		this.externalDesignOptions = externalDesignOptions;
	}

	public int getProductTypeId() {
		return this.productTypeId;
	}

	public void setProductTypeId(int productTypeId) {
		this.productTypeId = productTypeId;
	}

	public OrderItem getOrderItem() {
		return this.orderItem;
	}

	public void setOrderItem(OrderItem orderItem) {
		this.orderItem = orderItem;
	}

	public Product getProduct() {
		return this.product;
	}

	public void setProduct(Product product) {
		this.product = product;
	}

	public DesignsStateName getDesignsStateName() {
		return this.designsStateName;
	}

	public void setDesignsStateName(DesignsStateName designsStateName) {
		this.designsStateName = designsStateName;
	}
	
	public ParsedDesignData getDesignData() {
		Gson gson = new Gson();
		return gson.fromJson(this.getDesignJson(), ParsedDesignData.class);
	}
	
	public String getDesignImageId_Preview()
	{
		return  "designs/" + this.getId() + "_prev.png" ;
	}

	public String getDesignImageId_Thumbnail()
	{
		return "thumbs." + getDesignImageId_Preview();
	}
	
	public String getDesignImageId_HD()
	{
		return  "designs/" + this.getId() + "_hd.png" ;
	}

	public String getDesignImageId_EmbosserM()
	{
		return "embosser_m." + getDesignImageId_HD();
	}

	public String getDesignImageId_EmbosserF()
	{
		return "embosser_f." + getDesignImageId_HD();
	}

	public String getDesignImageId_SVG()
	{
		return  "designs/" + this.getId() + "_hd.svg" ;
	}

}