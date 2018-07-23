package model;

import java.io.Serializable;
import java.util.List;

import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.NamedQuery;
import javax.persistence.OneToMany;
import javax.persistence.Table;


/**
 * The persistent class for the order_items_processing_stage_names database table.
 * 
 */
@Entity
@Table(name="order_items_processing_stage_names")
@NamedQuery(name="OrderItemsProcessingStageName.findAll", query="SELECT o FROM OrderItemsProcessingStageName o")
public class OrderItemsProcessingStageName implements Serializable {
	private static final long serialVersionUID = 1L;

	@Id
	@GeneratedValue(strategy=GenerationType.IDENTITY)
	private int value;

	private String name;
	
	@OneToMany(mappedBy="processingStageName")
	private List<OrderItem> orderItems;

	public OrderItemsProcessingStageName() {
	}

	public int getValue() {
		return this.value;
	}

	public void setValue(int value) {
		this.value = value;
	}

	public String getName() {
		return this.name;
	}

	public void setName(String name) {
		this.name = name;
	}


}