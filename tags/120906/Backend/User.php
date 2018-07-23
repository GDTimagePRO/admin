<?php
/*
 * This class contains all of the information about a user.
 */

class User{
	private $id = -1;
	private $email;
	private $name;
	private $contactname;
	private $department;
	private $street;
	private $city;
	private $state;
	private $country;
	private $postalcode;
	private $phone;
	private $fax; 
	private $password;
	
	function __construct(){}
	
	function loadFromDB($id){
		$startup = Startup::getInstance("../");
		$db = $startup->db;
		$this->setValues($db->loadUser($id));
		
	}

	/*
	 * Sets the values for the properties of this class based on an array with all necessary values in it.
	 */
	function setValues(array $vals){
		$this->email = $vals['email'];
		$this->name = $vals['name'];
		$this->contactname = $vals['contactname'];
		$this->department = $vals['department'];
		$this->street = $vals['street'];
		$this->city = $vals['city'];
		$this->state = $vals['statecode'];
		$this->country = $vals['countrycode'];
		$this->postalcode = $vals['postalcode'];
		$this->phone = $vals['phone'];
		$this->fax = $vals['fax'];
		$this->password = $vals['password'];
		if(isset($vals['id'])){
			$this->id = $vals['id'];
		}
		
		
	}	
	
	public function getName(){
		return $this->name;
		
	}
	
	public function getEmail(){
		return $this->email;
	}
	
	public function getContactName(){
		return $this->contactname;
	}
	
	public function getDepartment(){
		return $this->department;
	}
	
	public function getStreet(){
		return $this->street;
	}
	
	public function getCity(){
		return $this->city;
	}
	
	public function getState(){
		return $this->state;
	}
	
	public function getCountry(){
		return $this->country;
	}
	
	public function getPostalCode(){
		return $this->postalcode;
	}
	
	public function getPhone(){
		return $this->phone;
	}
	
	public function getFax(){
		return $this->fax;
	}
	
	public function getPassword(){
		return $this->password;
	}
	
	public function getId(){
		return $this->id;
	}
}
?>