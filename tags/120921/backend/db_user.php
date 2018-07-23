<?php
	
	class User
	{
		public $id = -1;
		public $dateCreated;
		public $isWorkplaceAddress;
		public $name;
		public $contactName;
		public $department;
		public $street;
		public $city;
		public $stateCode;
		public $postalCode;
		public $countryCode;
		public $email;
		public $phone;
		public $fax;
		public $password;
	
		function __construct(){}
	}
	
	class UserDB
	{
		const DEBUG = TRUE;
		
		const ENCRYPT_PASSWORD = "lksdlksjdflkajsdflsakjfkalsdf"; //this constant is the password for encrypting and decrypting passwords
		const USER_FETCH_FIELDS = "id, date_created, is_workplace_address, name, contact_name, department, street, city, state_code, postal_code, country_code, email, phone, fax, AES_DECRYPT(password,'lksdlksjdflkajsdflsakjfkalsdf') as 'password'";
		private $connection = NULL;
		
		function __construct($connection)
		{
			$this->connection = $connection;
		}
	
		
		function createUser(User $user)
		{
			$query = sprintf(
					"INSERT INTO users( date_created, is_workplace_address, name, contact_name, department, street, city, state_code, postal_code, country_code, email, phone, fax, password )".
					"VALUES ( NOW(), %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', AES_ENCRYPT('%s','%s') )",
					$user->isWorkplaceAddress,
					mysql_escape_string($user->name),
					mysql_escape_string($user->contactName),
					mysql_escape_string($user->department),
					mysql_escape_string($user->street),
					mysql_escape_string($user->city),
					mysql_escape_string($user->stateCode),
					mysql_escape_string($user->postalCode),
					mysql_escape_string($user->countryCode),
					mysql_escape_string($user->email),
					mysql_escape_string($user->phone),
					mysql_escape_string($user->fax),
					$user->password,
					UserDB::ENCRYPT_PASSWORD
				);				
				
			$result = mysql_query($query,$this->connection);		
			if($result)
			{
				$user->id = mysql_insert_id($this->connection);
				return true;  
			}
			else
			{
				$user->id = -1;
				if(UserDB::DEBUG) echo mysql_error();
				return false;
			}
		}
		
		
		function loadUser($row)
		{
			$user = new User();
	
			$user->id = $row['id'];
			$user->dateCreated = strtotime($row['date_created']);
			$user->isWorkplaceAddress = $row['is_workplace_address'];
			$user->name = $row['name'];
			$user->contactName = $row['contact_name'];
			$user->department = $row['department'];
			$user->street = $row['street'];
			$user->city = $row['city'];
			$user->stateCode = $row['state_code'];
			$user->postalCode = $row['postal_code'];
			$user->countryCode = $row['country_code'];
			$user->email = $row['email'];
			$user->phone = $row['phone'];
			$user->fax = $row['fax'];
			$user->password = $row['password'];
	
			return $user;
		}
	
		
		function getUserById($id)
		{
			$query = sprintf("SELECT ".UserDB::USER_FETCH_FIELDS." FROM users WHERE id=%d",$id);
	
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
			
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
					
			return $this->loadUser($row);
		}
	
		
		function getUserByEmail($email)
		{
			$query = sprintf("SELECT ".UserDB::USER_FETCH_FIELDS." FROM users WHERE email='%s'", mysql_escape_string($email));
	
			$result = mysql_query($query,$this->connection);
			if(!$result) return NULL;
			
			$row = mysql_fetch_assoc($result);
			if(!$row) return NULL;
			
			return $this->loadUser($row);
		}	
	}
?>