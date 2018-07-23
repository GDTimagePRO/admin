package model;

import java.io.Serializable;

import javax.persistence.Column;
import javax.persistence.Embeddable;

/**
 * The primary key class for the barcodes database table.
 * 
 */
@Embeddable
public class BarcodePK implements Serializable {
	//default serial version id, required for serializable classes.
	private static final long serialVersionUID = 1L;

	@Column(name="customer_id")
	private int customerId;

	private String barcode;

	public BarcodePK() {
		
	}
	
	public BarcodePK(String barcode, int customerId) {
		this.barcode = barcode;
		this.customerId = customerId;
	}
	
	public int getCustomerId() {
		return customerId;
	}

	public void setCustomerId(int customerId) {
		this.customerId = customerId;
	}

	public String getBarcode() {
		return barcode;
	}

	public void setBarcode(String barcode) {
		this.barcode = barcode;
	}

	public boolean equals(Object other) {
		if (this == other) {
			return true;
		}
		if (!(other instanceof BarcodePK)) {
			return false;
		}
		BarcodePK castOther = (BarcodePK)other;
		return 
			(this.customerId == castOther.customerId)
			&& this.barcode.equals(castOther.barcode);
	}

	public int hashCode() {
		final int prime = 31;
		int hash = 17;
		hash = hash * prime + this.customerId;
		hash = hash * prime + this.barcode.hashCode();
		
		return hash;
	}
}