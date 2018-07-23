package model;

import java.io.Serializable;
import java.sql.Timestamp;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.NamedQuery;
import javax.persistence.Table;


/**
 * The persistent class for the workstation_table database table.
 * 
 */
@Entity
@Table(name="workstation_table")
@NamedQuery(name="WorkstationTable.findAll", query="SELECT w FROM WorkstationTable w")
public class WorkstationTable implements Serializable {
	private static final long serialVersionUID = 1L;

	@Column(name="c_description")
	private String cDescription;

	@Column(name="d_date_rendered")
	private Timestamp dDateRendered;

	@Id
	@Column(name="d_id")
	private int dId;

	@Column(name="d_state")
	private int dState;

	@Column(name="dsn_name")
	private String dsnName;

	@Column(name="oi_customer_id")
	private int oiCustomerId;

	@Column(name="oi_date_created")
	private Timestamp oiDateCreated;

	@Column(name="oi_external_order_id")
	private long oiExternalOrderId;

	@Column(name="oi_external_system_name")
	private String oiExternalSystemName;

	@Column(name="oi_id")
	private int oiId;

	@Column(name="oi_processing_stages_id")
	private int oiProcessingStagesId;

	@Column(name="oipsn_name")
	private String oipsnName;

	@Column(name="p_category_id")
	private int pCategoryId;

	@Column(name="pc_name")
	private String pcName;

	public WorkstationTable() {
	}

	public String getCDescription() {
		return this.cDescription;
	}

	public Timestamp getDDateRendered() {
		return this.dDateRendered;
	}

	public int getDId() {
		return this.dId;
	}

	public int getDState() {
		return this.dState;
	}

	public String getDsnName() {
		return this.dsnName;
	}
	
	public int getOiCustomerId() {
		return this.oiCustomerId;
	}

	public Timestamp getOiDateCreated() {
		return this.oiDateCreated;
	}

	public long getOiExternalOrderId() {
		return this.oiExternalOrderId;
	}

	public String getOiExternalSystemName() {
		return this.oiExternalSystemName;
	}

	public int getOiId() {
		return this.oiId;
	}

	public int getOiProcessingStagesId() {
		return this.oiProcessingStagesId;
	}

	public String getOipsnName() {
		return this.oipsnName;
	}

	public int getPCategoryId() {
		return this.pCategoryId;
	}

	public String getPcName() {
		return this.pcName;
	}


}