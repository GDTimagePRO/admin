package model;

import java.io.Serializable;
import java.util.List;

import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.ManyToOne;
import javax.persistence.NamedQuery;
import javax.persistence.OneToMany;
import javax.persistence.Table;


/**
 * The persistent class for the design_template_categories database table.
 * 
 */
@Entity
@Table(name="design_template_categories")
@NamedQuery(name="DesignTemplateCategory.findAll", query="SELECT d FROM DesignTemplateCategory d")
public class DesignTemplateCategory implements Serializable {
	private static final long serialVersionUID = 1L;

	@Id
	@GeneratedValue(strategy=GenerationType.IDENTITY)
	private int id;

	private String name;

	//bi-directional many-to-one association to Customer
	@ManyToOne
	@JoinColumn(name="customer_id")
	private Customer customer;

	//bi-directional many-to-one association to DesignTemplate
	@OneToMany(mappedBy="designTemplateCategory")
	private List<DesignTemplate> designTemplates;

	public DesignTemplateCategory() {
	}

	public int getId() {
		return this.id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public String getName() {
		return this.name;
	}

	public void setName(String name) {
		this.name = name;
	}

	public Customer getCustomer() {
		return this.customer;
	}

	public void setCustomer(Customer customer) {
		this.customer = customer;
	}

	public List<DesignTemplate> getDesignTemplates() {
		return this.designTemplates;
	}

	public void setDesignTemplates(List<DesignTemplate> designTemplates) {
		this.designTemplates = designTemplates;
	}

	public DesignTemplate addDesignTemplate(DesignTemplate designTemplate) {
		getDesignTemplates().add(designTemplate);
		designTemplate.setDesignTemplateCategory(this);

		return designTemplate;
	}

	public DesignTemplate removeDesignTemplate(DesignTemplate designTemplate) {
		getDesignTemplates().remove(designTemplate);
		designTemplate.setDesignTemplateCategory(null);

		return designTemplate;
	}

}