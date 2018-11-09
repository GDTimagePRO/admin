package redemption;

import java.sql.Timestamp;

public class RedemptionCode {
	private int id;
	private int customer_id;
	private String code;
	private int group_id;
	private String usage;
	private Timestamp date_used;
	private int external_order_id;
	private String external_order_details;
	private String shipping_email;
	private String shipping_details;
	
	public RedemptionCode() {
		super();
		this.id = 0;
		this.customer_id = 0;
		this.code = null;
		this.group_id = 0;
		this.date_used = null;
		this.external_order_id = 0;
		this.external_order_details = null;
		this.shipping_email = null;
		this.shipping_details = null;
	}

	public RedemptionCode(int id, int customer_id, String code, int group_id, Timestamp date_used,
			int external_order_id, String external_order_details, String shipping_email, String shipping_details) {
		super();
		this.id = id;
		this.customer_id = customer_id;
		this.code = code;
		this.group_id = group_id;
		if(date_used == null)
		{
			this.usage = "Unused";
		} else
		{
			this.usage = "Used";
		}
		this.date_used = date_used;
		this.external_order_id = external_order_id;
		this.external_order_details = external_order_details;
		this.shipping_email = shipping_email;
		this.shipping_details = shipping_details;
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
	
	public String getCode() {
		return code;
	}
	
	public void setCode(String code) {
		this.code = code;
	}
	
	public int getGroup_id() {
		return group_id;
	}
	
	public void setGroup_id(int group_id) {
		this.group_id = group_id;
	}
	
	public String getUsage() {
		return usage;
	}

	public void setUsage(String usage) {
		this.usage = usage;
	}
	
	public Timestamp getDate_used() {
		return date_used;
	}
	
	public void setDate_used(Timestamp date_used) {
		this.date_used = date_used;
	}
	
	public int getExternal_order_id() {
		return external_order_id;
	}
	
	public void setExternal_order_id(int external_order_id) {
		this.external_order_id = external_order_id;
	}
	
	public String getExternal_order_details() {
		return external_order_details;
	}
	
	public void setExternal_order_details(String external_order_details) {
		this.external_order_details = external_order_details;
	}
	
	public String getShipping_email() {
		return shipping_email;
	}
	
	public void setShipping_email(String shipping_email) {
		this.shipping_email = shipping_email;
	}
	
	public String getShipping_details() {
		return shipping_details;
	}
	
	public void setShipping_details(String shipping_details) {
		this.shipping_details = shipping_details;
	}
	
	public String print(StringBuilder stringBuilder)
	{
		stringBuilder.append(group_id);
		stringBuilder.append(external_order_id);
		stringBuilder.append(code);
		stringBuilder.append(usage);
		stringBuilder.append(date_used);
		stringBuilder.append(shipping_email);
		stringBuilder.append("\n");
		return stringBuilder.toString();
	}
}
