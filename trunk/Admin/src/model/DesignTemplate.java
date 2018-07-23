package model;

import java.io.Serializable;

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


/**
 * The persistent class for the design_templates database table.
 * 
 */
@Entity
@Table(name="design_templates")
@NamedQuery(name="DesignTemplate.findAll", query="SELECT d FROM DesignTemplate d")
public class DesignTemplate implements Serializable {
	private static final long serialVersionUID = 1L;

	@Id
	@GeneratedValue(strategy=GenerationType.IDENTITY)
	private int id;

	@Lob
	@Column(name="config_json")
	private String configJson;

	@Lob
	@Column(name="design_json")
	private String designJson;

	private String name;

	@Column(name="product_type_id")
	private int productTypeId;

	//bi-directional many-to-one association to DesignTemplateCategory
	@ManyToOne
	@JoinColumn(name="category_id")
	private DesignTemplateCategory designTemplateCategory;

	public DesignTemplate() {
		configJson = "";
		designJson = "";
		name = "";
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

	public String getDesignJson() {
		return this.designJson;
	}

	public void setDesignJson(String designJson) {
		this.designJson = designJson;
	}

	public String getName() {
		return this.name;
	}

	public void setName(String name) {
		this.name = name;
	}

	public int getProductTypeId() {
		return this.productTypeId;
	}

	public void setProductTypeId(int productTypeId) {
		this.productTypeId = productTypeId;
	}

	public DesignTemplateCategory getDesignTemplateCategory() {
		return this.designTemplateCategory;
	}

	public void setDesignTemplateCategory(DesignTemplateCategory designTemplateCategory) {
		this.designTemplateCategory = designTemplateCategory;
	}
	
	public String getFullname() {
		return getId() + ": " + getName();
	}

}