package redemption;

import java.sql.Timestamp;

import com.google.gson.Gson;

public class RedemptionCodeGroup {
	private int id;
	private int customer_id;
	private Timestamp date_created;
	private String description;
	private String config_json;
	private String barCode;
	
	private Gson gson;
	
	public RedemptionCodeGroup()
	{
		this.id = 0;
		this.customer_id = 0;
		this.date_created = null;
		this.description = null;
		this.config_json = null;
	}
	
	public RedemptionCodeGroup(int id, int customer_id, Timestamp date_created, String description,
			String config_json) {
		super();
		this.id = id;
		this.customer_id = customer_id;
		this.date_created = date_created;
		this.description = description;
		this.config_json = config_json;

		gson = new Gson();
		this.barCode = gson.fromJson(config_json, BarCodeConfig.class).getGenesis().getCode();
	}
	
	public int getId() {
		return id;
	}
	
	public void setId(int id) {
		this.id = id;
	}
	
	public int getCustomer_id() {
		return customer_id;
	}
	
	public void setCustomer_id(int customer_id) {
		this.customer_id = customer_id;
	}
	
	public Timestamp getDate_created() {
		return date_created;
	}
	
	public void setDate_created(Timestamp date_created) {
		this.date_created = date_created;
	}
	
	public String getDescription() {
		return description;
	}
	
	public void setDescription(String description) {
		this.description = description;
	}
	
	public String getConfig_json() {
		return config_json;
	}
	
	public void setConfig_json(String config_json) {
		this.config_json = config_json;
	}

	public String getBarCode() {
		return barCode;
	}

	public void setBarCode(String barCode) {
		this.barCode = barCode;
	}
}
