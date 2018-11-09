package redemption;

import java.sql.Timestamp;

public class Design {
	private int id;
	private int order_item_id;
	private int product_type_id;
	private String config_json;
	private String design_json;
	private String external_design_options;
	private Timestamp date_changed;
	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public int getOrder_item_id() {
		return order_item_id;
	}

	public void setOrder_item_id(int order_item_id) {
		this.order_item_id = order_item_id;
	}

	public int getProduct_type_id() {
		return product_type_id;
	}

	public void setProduct_type_id(int product_type_id) {
		this.product_type_id = product_type_id;
	}

	public String getConfig_json() {
		return config_json;
	}

	public void setConfig_json(String config_json) {
		this.config_json = config_json;
	}

	public String getDesign_json() {
		return design_json;
	}

	public void setDesign_json(String design_json) {
		this.design_json = design_json;
	}

	public String getExternal_design_options() {
		return external_design_options;
	}

	public void setExternal_design_options(String external_design_options) {
		this.external_design_options = external_design_options;
	}

	public Timestamp getDate_changed() {
		return date_changed;
	}

	public void setDate_changed(Timestamp date_changed) {
		this.date_changed = date_changed;
	}

	public int getState() {
		return state;
	}

	public void setState(int state) {
		this.state = state;
	}

	public int getProduct_id() {
		return product_id;
	}

	public void setProduct_id(int product_id) {
		this.product_id = product_id;
	}

	public Timestamp getDate_rendered() {
		return date_rendered;
	}

	public void setDate_rendered(Timestamp date_rendered) {
		this.date_rendered = date_rendered;
	}

	private int state;
	private int product_id;
	private Timestamp date_rendered;

	public Design() {
		// TODO Auto-generated constructor stub
	}

}
