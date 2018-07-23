package model;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.List;

import com.google.gson.Gson;

public class BarcodeConfig implements Serializable {

	public static class Items {
		public Integer prod_id;
		public Integer templ_id;
		public String tc_id;
		
		public String getCaption() {
			if (templ_id != null) {
				return "Product: " + prod_id + " Template: " + templ_id; 
			} else {
				return "Product: " + prod_id + " Category: " + tc_id;
			}
		}
	}
	
	private String ui_mode;
	private List<Items> items;
	private String theme;
	
	public BarcodeConfig() {
		items = new ArrayList<Items>();
	}
	
	public List<Items> getItems() {
		return this.items;
	}
	
	public void setItems(List<Items> items) {
		this.items = items;
	}
	
	public void addItem(Items item) {
		this.items.add(item);
	}
	
	public String getUIMode() {
		return ui_mode;
	}
	
	public void setUIMode(String ui_mode) {
		this.ui_mode = ui_mode;
	}
	
	public String getTheme() {
		return theme;
	}
	
	public void setTheme(String theme) {
		this.theme = theme;
	}
	
	public String toString() {
		Gson g = new Gson();
		return g.toJson(this);
	}
	
}
