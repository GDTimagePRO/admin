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
 * The persistent class for the designs_state_names database table.
 * 
 */
@Entity
@Table(name="designs_state_names")
@NamedQuery(name="DesignsStateName.findAll", query="SELECT d FROM DesignsStateName d")
public class DesignsStateName implements Serializable {
	private static final long serialVersionUID = 1L;

	@Id
	@GeneratedValue(strategy=GenerationType.IDENTITY)
	private int value;

	private String name;

	//bi-directional many-to-one association to Design
	@OneToMany(mappedBy="designsStateName")
	private List<Design> designs;

	public DesignsStateName() {
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

	public List<Design> getDesigns() {
		return this.designs;
	}

	public void setDesigns(List<Design> designs) {
		this.designs = designs;
	}

	public Design addDesign(Design design) {
		getDesigns().add(design);
		design.setDesignsStateName(this);

		return design;
	}

	public Design removeDesign(Design design) {
		getDesigns().remove(design);
		design.setDesignsStateName(null);

		return design;
	}

}