<?php
/*
 * This class controls database access
 * all database access must be run through this class (for ease of use as well as security)
 */
class Database{
	/*
	 * These constants store connection information for the database. Will need to be changed for each
	 * server that is running them.
	 */
	
	const ENCRYPT_PASSWORD = "lksdlksjdflkajsdflsakjfkalsdf"; //this constant is the password for encrypting and decrypting passwords
	
	//constants to be returned for login check
	const LOGIN_OK = 0;
	const EMAIL_FAIL = 1;
	const PASSWORD_FAIL = 2;
	
	//constants to be returned for code check
	const CODE_OK = 0;
	const CODE_UNKNOWN = 1;
	const CODE_USED = 2;
	
	const USER_UPLOADED = 1;
	
	private $connection = NULL; //this stores the connection to the database
	
	/*
	 * The constructor of the class. It connects to the database
	 * 
	 */
	function __construct($server,$username,$password,$dbname){
		if($this->connection == NULL){
			$this->connection = mysql_connect($server,$username,$password);
			mysql_select_db($dbname);
		}
	}
	
	/*
	 * This function adds the newly created user to the database after they have registered
	 * It doesn't check to see if they are valid, that will be done on the registration page.
	 * $user: the User object that contains all necessary values
	 */
	function register(User $user){
		$query = sprintf("INSERT INTO users(createtime,email,password,name,contactname,department,street,city,statecode,countrycode,postalcode,phone,fax)".
				"VALUES (NOW(),'%s',AES_ENCRYPT('%s','%s'),'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
				$user->getEmail(),$user->getPassword(),Database::ENCRYPT_PASSWORD,mysql_escape_string($user->getName()),mysql_escape_string($user->getContactName()),
				mysql_escape_string($user->getDepartment()),mysql_escape_string($user->getStreet()),mysql_escape_string($user->getCity()),mysql_escape_string($user->getState()),
				mysql_escape_string($user->getCountry()),mysql_escape_string($user->getPostalCode()),mysql_escape_string($user->getPhone()),mysql_escape_string($user->getFax()));
				
		if(!mysql_query($query,$this->connection)){
			echo "Error: ".mysql_error();
			
		}
		else{
			login($user->getEmail(),$user->getPassword());
		}
		
	}
	
	/**
	 * Log the user in 
	 *
	 * This function checks to see if the email is in the database and then checks if the password is correct.
	 * If the email is not in the database then it returns EMAIL_FAIL, if the passwords don't match it returns PASSWORD_FAIL.
	 * If it all works ok then it returns LOGIN_OK.
	 * @param string $email The email address the user entered into the login form.
	 * @param string $password The password the user entered into the login form.
	 * @return int either EMAIL_FAIL if the email address is not in the database already, PASSWORD_FAIL if the password
	 * the provided doesn't match up with the email address or LOGIN_OK if everything worked.  
	 */
	function login($email,$password){
		$query = sprintf("SELECT count(id) FROM users WHERE email='%s'",$email);
		$result = mysql_query($query,$this->connection);
		$num = mysql_fetch_array($result,MYSQL_NUM);
		if($num[0]==0){
			return Database::EMAIL_FAIL;
		}
		$query = sprintf("SELECT AES_DECRYPT(password,'%s') FROM users WHERE email='%s'",Database::ENCRYPT_PASSWORD,$email);
		$result = mysql_query($query,$this->connection);
		$pass = mysql_fetch_array($result,MYSQL_NUM);
		if($password != $pass[0]){
			return Database::PASSWORD_FAIL;
		}
		$startup = Startup::getInstance("../");
		$s = $startup->session;
		$userId = $this->getUserId($_POST['email']);
		$s->setUserId($userId);
		/*
		 * Check if there are any active orders and / or items
		 */
		$query = sprintf("SELECT id FROM orders WHERE user_id='%d' and processingstages_id='%d'",$userId,
					$startup->processingstages[$startup->settings['default order processing stage']]);
		$result = mysql_query($query,$this->connection);
		$num = mysql_num_rows($result);
		if($num!=0){
			$row = mysql_fetch_assoc($result);
			$order_id = $row['id'];
			$s->setCurrentOrder($order_id);
			$query = sprintf("SELECT id FROM orderitems WHERE order_id='%d' and processingstages_id='%d'",$order_id,
						$startup->processingstages[$startup->settings['default order item processing stage']]);
			if(!$result = mysql_query($query,$this->connection)){
				echo "ERROR: ".mysql_error();
			}
			else{
				$num = mysql_num_rows($result);
				if($num!=0){
					$row = mysql_fetch_assoc($result);
					$item_id = $row['id'];
					$s->setCurrentItem($item_id);
				}
			}
			
			
		}
		return Database::LOGIN_OK;
	}
	
	/*
	 * This function returns the User Id from the database based on the email address
	 */
	function getUserId($email){
		$query = sprintf("SELECT id FROM users WHERE email='%s'",$email);
		$result = mysql_query($query,$this->connection);
		$result = mysql_fetch_assoc($result);
		return $result['id'];
		
	}
	
	/*
	 * This function loads all of the user information into an associative array when given a user id
	 */
	function loadUser($id){
		$query = sprintf("SELECT * from users WHERE id=%d",$id);
		$result = mysql_query($query,$this->connection);
		$result = mysql_fetch_assoc($result);
		return $result;
		
	}
	
	/**
	 * This function checks to see if a code is valid
	 * @param string $code the code to be checked
	 * @return int either CODE_OK, CODE_UNKNOWN, or CODE_USED
	 * 
	 */
	public function checkCode($code){
		$query = sprintf("SELECT date,master FROM barcodes WHERE barcode='%s'",$code);
		$result = mysql_query($query,$this->connection);
		$num = mysql_affected_rows($this->connection);
		if($num<=0){
			return Database::CODE_UNKNOWN;
		}
		$result = mysql_fetch_assoc($result);
		if($result['date']!="0000-00-00 00:00:00"&&$result['master']!="Y"){
			return Database::CODE_USED;
		}
		
		return Database::CODE_OK;
	}
	
	/**
	 * Checks whether the code provided is a master code or not.
	 * 
	 * This function takes a barcode as a parameter and then returns whether or not
	 * the code is a master code which it reads from the database.
	 * 
	 * @param string $code The barcode to be checked.
	 * @return boolean TRUE if master code, FALSE if not.
	 */
	public function isMasterCode($code){
		if($code == "ECOM"){
			return TRUE;
		}
		$query = sprintf("SELECT master FROM barcodes WHERE barcode='%s'",$code);
		$result = mysql_query($query,$this->connection);
		$master = mysql_fetch_row($result);
		if($master[0] == "Y"){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	
	public function getBarCode($itemId){
		$query = sprintf("SELECT barcode FROM orderitems WHERE id='%d'",$itemId);
		$result = mysql_query($query,$this->connection);
		$row = mysql_fetch_assoc($result);
		return $row['barcode'];
	}
	
	public function getProductId($barcode){
		$query = sprintf("SELECT product_id FROM barcodes WHERE barcode='%s'",$barcode);
		$result = mysql_query($query,$this->connection);
		$row = mysql_fetch_assoc($result);
		return $row['product_id'];
		
	}
	
	public function getProduct($id){
		$query = sprintf("SELECT * FROM products WHERE id='%d'",$id);
		$result = mysql_query($query,$this->connection);
		return mysql_fetch_assoc($result);
	}
	
	public function getProducts(){
		$query = "SELECT id,longname FROM products";
		$result = mysql_query($query,$this->connection);
		$return = array();
		while(($row = mysql_fetch_assoc($result)) != FALSE){
			$return[] = $row;	
		}
		return $return;
	}
	
	/**
	 * Returns associative array of processing stages and their ids.
	 * 
	 * This function reads the processing stages table in the database into an associative
	 * array where the keys are the keynames and the entries are the ids in the table. 
	 * This is primarily used to set stages in other tables (Orders, OrderItems).
	 * 
	 * @return array an associative array of processing stages and their ids
	 */
	public function getProcessingStages(){
		$query = "SELECT id,keyname FROM processingstages";
		$result = mysql_query($query,$this->connection);
		$return = array();
		while(($row = mysql_fetch_assoc($result)) != FALSE){
			$return[$row['keyname']] = $row['id'];
		}
		return $return;
	}
	
	/**
	 * Create a new order for a certain user.
	 * @param int $userId the user to create the order for.
	 * @return int the id of the order just created
	 */
	public function newOrder($userId){
		//echo "USERID: ".$userId;
		$startup = Startup::getInstance("../");
		$query = sprintf("INSERT INTO orders(user_id,processingstages_id,startdate) VALUES ('%d','%d',NOW())",$userId,
					$startup->processingstages[$startup->settings['default order processing stage']]);
		if(!$result = mysql_query($query,$this->connection)){
			echo "ERROR: ".mysql_error();
		}
		$order_id = mysql_insert_id($this->connection);
		$startup->session->setCurrentOrder($order_id);
		return $order_id;
	}
	
	
	/**
	 * Creates a new order if there isn't an order currently open. Then adds new item based on barcode
	 * to the current order and returns the item number.
	 * @param string $code the barcode of the item being added to the order
	 * @return int the item number from the database
	 */
	public function newOrderItem($code){
		$startup = Startup::getInstance("../");	
		$s = $startup->session;	
		$userId = $s->getUserId();
		/*
		 * Check to see if the user has an open order. If not then make one, otherwise get the order id
		 */
		$query = sprintf("SELECT id FROM orders WHERE user_id = '%d' and processingstages_id='%d'",$userId,
					$startup->processingstages[$startup->settings['default order processing stage']]);
		$result = mysql_query($query,$this->connection);
		$order_id = 0;
		
		if(mysql_affected_rows($this->connection)==0){
			$order_id = $this->newOrder($userId);
		}
		else{
			$row = mysql_fetch_assoc($result);
			$order_id = $row['id'];
		}	
		/*
		 * Set the barcode to be used if it's not a master code
		 */ 
		 if(!$this->isMasterCode($code)){
		 	$query = sprintf("UPDATE barcodes SET date=NOW() WHERE barcode='%s'",$code);
			 mysql_query($query,$this->connection);
		 }
		/*
		 * Add a new order item and get the order item id and return it.
		 */
		$query = sprintf("INSERT INTO orderitems (order_id,processingstages_id,barcode) VALUES ('%d','%d','%s')", $order_id,
					$startup->processingstages[$startup->settings['default order item processing stage']],$code);
		$result = mysql_query($query,$this->connection);
		$orderitem_id = mysql_insert_id($this->connection);
		return $orderitem_id;
	}

	public function newTextLine($orderitem_id){
		$query = sprintf("INSERT INTO textlines (orderitem_id) VALUES (%d)",$orderitem_id);
		$result = mysql_query($query,$this->connection);
		$line_id = mysql_insert_id($this->connection);
		return $line_id;
	}
	
	public function updateTextLine($textline){
		$query = sprintf("UPDATE textlines SET text='%s', font='%s', fontsize=%d, x=%d, y=%d, 
					bold='%s', italic='%s', underline='%s', type='%s',radius=%d, align='%s' WHERE id='%d'", mysql_escape_string($textline['text']),$textline['font'],$textline['fontsize'],
					$textline['x'],$textline['y'],$textline['bold'],$textline['italic'],$textline['underline'],$textline['type'],$textline['radius'],$textline['align'],$textline['id']);
		//$result = mysql_query($query,$this->connection);
		echo "Updating Line in database.php <br/>";
		if(!$result = mysql_query($query,$this->connection)){
			echo "Got an error <br/>";
			echo "ERROR: ".mysql_error($this->connection);	
		}
		else{
			echo "didn't get an error <br/>";
			return "UPDATE ".$textline['text']+" ".mysql_affected_rows($this->connection). "  OK   ";
		}
		return "Did nothing";
	}
	
	public function newGraphic($orderitem_id,$image_id){
		$query = sprintf("INSERT INTO images (orderitem_id,image_id) VALUES (%d,%d)",$orderitem_id,$image_id);
		$result = mysql_query($query,$this->connection);
		$line_id = mysql_insert_id($this->connection);
		return $line_id;
	}
	
	public function newBorder($orderitem_id,$x,$y,$width,$height,$type_id,$style_id,$line_width,$radius,$sides){
		$query = sprintf("INSERT INTO borders(orderitem_id,x,y,width,height,type_id,style_id,line_width,radius,sides)
				VALUES (%d,%d,%d,%d,%d,'%s',%d,%d,%d,'%s')",$orderitem_id,$x,$y,$width,$height,$type_id,$style_id,$line_width,$radius,$sides);
		$result = mysql_query($query,$this->connection);
		$line_id = mysql_insert_id($this->connection);
		return $line_id;
	}
	
	public function newLine($orderitem_id,$x,$y,$x2,$y2,$type_id,$line_width){
		$query = sprintf("INSERT INTO `lines`(orderitem_id,x,y,x2,y2,type_id,line_width) VALUES (%d,%d,%d,%d,%d,%d,%d)",$orderitem_id,$x,$y,$x2,$y2,$type_id,$line_width);
		if(!$result = mysql_query($query,$this->connection)){
			return $query;
		}
		$line_id = mysql_insert_id($this->connection);
		return $line_id;
	}
	
	public function newTable($orderitem_id,$x,$y,$width,$height,$rows,$columns,$border){
		$query = sprintf("INSERT INTO `tables`(orderitem_id,x,y,width,height,rows,columns,border)
				VALUES (%d,%d,%d,%d,%d,%d,%d,%d)",$orderitem_id,$x,$y,$width,$height,$rows,$columns,$border);
		$result = mysql_query($query,$this->connection);
		$line_id = mysql_insert_id($this->connection);
		return $line_id;
	}
	
	public function updateImage($image){
		$query = sprintf("UPDATE images SET image_id='%d', x=%d, y=%d, width=%d, height=%d  WHERE id='%d'", $image['image_id'],$image['x'],
							$image['y'],$image['width'],$image['height'],$image['id']);
		//$result = mysql_query($query,$this->connection);
		if(!$result = mysql_query($query,$this->connection)){
			return "ERROR: "+mysql_error($this->connection);	
		}
		else{
			return "UPDATE image ".$image['id']."  OK   ";
		}
	}
	
	public function updateBorder($border){
		$query = sprintf("UPDATE borders SET type_id='%s',style_id=%d, x=%d, y=%d, width=%d, height=%d, line_width=%d, sides='%s', radius=%d  WHERE id='%d'", $border['type_id'],$border['style_id'],$border['x'],
							$border['y'],$border['width'],$border['height'],$border['line_width'],$border['sides'],$border['radius'],$border['id']);
		//$result = mysql_query($query,$this->connection);
		echo $query;
		if(!$result = mysql_query($query,$this->connection)){
			echo "No result";
			return "ERROR: "+mysql_error($this->connection);	
		}
		else{
			echo "result";
			return "UPDATE border ".$border['id']."  OK   ".$query;
		}
	}
	
	public function updateLine($border){
		$query = sprintf("UPDATE `lines` SET type_id='%d', x=%d, y=%d, x2=%d, y2=%d, line_width=%d WHERE id='%d'", $border['type_id'],$border['x'],
							$border['y'],$border['x2'],$border['y2'],$border['line_width'],$border['id']);
		//$result = mysql_query($query,$this->connection);
		echo $query;
		if(!$result = mysql_query($query,$this->connection)){
			echo "No result";
			return "ERROR: "+mysql_error($this->connection);	
		}
		else{
			echo "result";
			return "UPDATE line ".$border['id']."  OK   ".$query;
		}
	}
	
	public function updateTable($table){
		$query = sprintf("UPDATE `tables` SET x=%d, y=%d, width=%d, height=%d, rows=%d, columns=%d, border=%d  WHERE id='%d'", $table['x'],
							$table['y'],$table['width'],$table['height'],$table['rows'],$table['columns'],$table['border'],$table['id']);
		//$result = mysql_query($query,$this->connection);
		echo $query;
		if(!$result = mysql_query($query,$this->connection)){
			echo "No result";
			return "ERROR: "+mysql_error($this->connection);	
		}
		else{
			echo "result";
			return "UPDATE table ".$table['id']."  OK   ".$query;
		}
	}
	
	public function updateColor($color,$orderitem_id){
		$query = sprintf("UPDATE orderitems SET color='%s' WHERE id='%d'",$color,$orderitem_id);
		$result = mysql_query($query,$this->connection);
	}
	
	public function deleteTextLine($id){
		$query = sprintf("DELETE FROM textlines WHERE id='%d'",$id);
		$result = mysql_query($query,$this->connection);
	}
	
	public function deleteGraphic($id){
		$query = sprintf("DELETE FROM images WHERE id='%d'",$id);
		$result = mysql_query($query,$this->connection);
	}
	
	public function deleteBorder($id){
		$query = sprintf("DELETE FROM borders WHERE id='%d'",$id);
		$result = mysql_query($query,$this->connection);
	}
	
	public function deleteLine($id){
		$query = sprintf("DELETE FROM `lines` WHERE id='%d'",$id);
		$result = mysql_query($query,$this->connection);
	}
	
	public function deleteTable($id){
		$query = sprintf("DELETE FROM `tables` WHERE id='%d'",$id);
		$result = mysql_query($query,$this->connection);
	}
	
	public function getTextLines($orderitem_id){
		$query = sprintf('SELECT * FROM textlines WHERE orderitem_id=%d',$orderitem_id);
		$result = mysql_query($query,$this->connection);
		$lines = array();
		while($row=mysql_fetch_assoc($result)){
			$lines[] = $row;
			//echo $row['radius'];
		}
		
		return $lines;
	}
	
	public function getImages($orderitem_id){
		$query = sprintf('SELECT * FROM images WHERE orderitem_id=%d',$orderitem_id);
		$result = mysql_query($query,$this->connection);
		$images = array();
		while($row=mysql_fetch_assoc($result)){
			if($row['x']==0) $row['x'] = 10;
			if($row['y']==0) $row['y'] = 10;
			if($row['width']==0) $row['width'] = 100;
			if($row['height']==0) $row['height'] = 100;		
			$images[] = $row;
			//echo $row['radius'];
		}
		
		return $images;
	}
	
	public function getBorders($orderitem_id){
		$query = sprintf('SELECT * FROM borders WHERE orderitem_id=%d',$orderitem_id);
		$result = mysql_query($query,$this->connection);
		$borders = array();
		while($row=mysql_fetch_assoc($result)){
			if($row['x']==0) $row['x'] = 10;
			if($row['y']==0) $row['y'] = 10;
			if($row['width']==0) $row['width'] = 100;
			if($row['height']==0) $row['height'] = 100;		
			$borders[] = $row;
			//echo $row['radius'];
		}
		
		return $borders;
		
	}
	
	public function getLines($orderitem_id){
		$query = sprintf('SELECT * FROM `lines` WHERE orderitem_id=%d',$orderitem_id);
		$result = mysql_query($query,$this->connection);
		$lines = array();
		while($row=mysql_fetch_assoc($result)){
			if($row['x']==0) $row['x'] = 10;
			if($row['y']==0) $row['y'] = 10;
			if($row['x2']==0) $row['x2'] = 100;
			if($row['y2']==0) $row['x2'] = 100;		
			$lines[] = $row;
			//echo $row['radius'];
		}
		
		return $lines;
		
	}
	
	public function getTables($orderitem_id){
		$query = sprintf('SELECT * FROM `tables` WHERE orderitem_id=%d',$orderitem_id);
		$result = mysql_query($query,$this->connection);
		$tables = array();
		while($row=mysql_fetch_assoc($result)){
			if($row['x']==0) $row['x'] = 10;
			if($row['y']==0) $row['y'] = 10;
			if($row['width']==0) $row['width'] = 100;
			if($row['height']==0) $row['height'] = 100;		
			$tables[] = $row;
			//echo $row['radius'];
		}
		
		return $tables;
		
	}
	
	public function getColor($orderitem_id){
		$query = sprintf("SELECT color FROM orderitems WHERE id=%d",$orderitem_id);
		$result = mysql_query($query,$this->connection);
		$row = mysql_fetch_row($result);
		echo $row[0];
	}
	
	
	public function getImage($id){
		$query = sprintf('SELECT data FROM imagelibrary WHERE id=%d',$id);
		$result = mysql_query($query,$this->connection);		
		$row = mysql_fetch_assoc($result);
		return $row['data'];
	
	}
	
	public function getImageCategories(){
		$query = "SELECT * FROM imagecategories ORDER BY category";
		$result = mysql_query($query,$this->connection);
		$return = array();
		while($row = mysql_fetch_assoc($result)){
			$return[] = $row;
		}
		return $return;
	}
	
	public function getImagesFromCategory($category_id,$user_id){
		if($category_id == Database::USER_UPLOADED){
			$query = sprintf('SELECT id FROM imagelibrary WHERE category_id=%d and user_id=%d',$category_id,$user_id);
		}
		else{
			$query = sprintf('SELECT id FROM imagelibrary WHERE category_id=%d',$category_id);
		}
		$result = mysql_query($query,$this->connection);
		$return = array();
		while($row = mysql_fetch_row($result)){
			$return[] = $row[0];
		}
		return $return;
	}
	
	public function getTemplatesFromCategory($category_id,$user_id){
		/*if($category_id == Database::USER_UPLOADED){
			$query = sprintf('SELECT id FROM imagelibrary WHERE category_id=%d and user_id=%d',$category_id,$user_id);
		}
		else{*/
			$query = sprintf('SELECT id FROM templates WHERE category=%d',$category_id);
		//}
		if(!$result = mysql_query($query,$this->connection)){
			echo mysql_error();
		}
		$return = array();
		while($row = mysql_fetch_row($result)){
			$return[] = $row[0];
		}
		return $return;
	}
	
	public function getTemplateJSON($id){
		$query = sprintf("SELECT template FROM templates WHERE id=%d",$id);
		$result = mysql_query($query,$this->connection);
		$row = mysql_fetch_row($result);
		return $row[0];
	}
	
	public function updateOrderData($orderitem_id,$data){
		$query = sprintf("UPDATE orderitems SET data='%s' WHERE id=%d",$data,$orderitem_id);
		if(!$result = mysql_query($query,$this->connection)){
			return "ERROR: "+mysql_error($this->connection);
		}
		else return "Data update ok";
	}
	
	public function getOrderData($orderitem_id){
		$query = sprintf("SELECT data fROM orderitems WHERE id=%d",$orderitem_id);
		if(!$result = mysql_query($query,$this->connection)){
			echo mysql_error($this->connection);
		}
		$row = mysql_fetch_row($result);
		return $row[0];
	}
	
	public function newTemplateCategory($category){
		$query = sprintf("INSERT INTO templatecategories (category) VALUES ('%s')",$category);
		if(!$result = mysql_query($query,$this->connection)){
			echo mysql_error();
		}	
	}
	
	public function newGraphicCategory($category){
		$query = sprintf("INSERT INTO imagecategories (category) VALUES ('%s')",$category);
		if(!$result = mysql_query($query,$this->connection)){
			echo mysql_error();
		}	
	}
	
	public function getTemplateCategories(){
		$query = "SELECT * FROM templatecategories";
		$result = mysql_query($query,$this->connection);
		$return = array();
		while($row = mysql_fetch_assoc($result)){
			$return[] = $row;
		}
		return $return;
	}
	
	public function getGraphicCategories(){
		$query = "SELECT * FROM imagecategories";
		$result = mysql_query($query,$this->connection);
		$return = array();
		while($row = mysql_fetch_assoc($result)){
			$return[] = $row;
		}
		return $return;
	}
	
	public function saveTemplate($name,$category,$json,$data){
		$query = sprintf("INSERT INTO templates(name,category,template,data) VALUES ('%s',%d,'%s','%s')",$name,$category,$json,$data);
		if(!$result = mysql_query($query,$this->connection)){
			return mysql_error();
		}
		else{
			return "OK";
		}
	}
	
	public function getTemplateData($template_id){
		$query = sprintf("SELECT data fROM templates WHERE id=%d",$template_id);
		if(!$result = mysql_query($query,$this->connection)){
			echo mysql_error($this->connection);
		}
		$row = mysql_fetch_row($result);
		return $row[0];
	}
	
	public function newLibraryGraphic($name,$category,$file,$userId=0){
		$query = sprintf("INSERT INTO imagelibrary(name,category_id,data,user_id) VALUES ('%s',%d,'%s',%d)",$name,$category,$file,$userId);
		if(!$result = mysql_query($query,$this->connection)){
			echo mysql_error();
		}
	}

	public function setOrderItemDesign($id, $json)
	{
		$query = sprintf("UPDATE orderitems SET design='%s'  WHERE id=%d", mysql_real_escape_string($json) , $id);		
		return mysql_query($query,$this->connection); 
	}
	
	public function getOrderItemDesign($id)
	{
		$query = sprintf("SELECT design FROM orderitems WHERE id=%d", $id);		
		$result = mysql_query($query,$this->connection);
		
		if(!$result) return null;
		
		$row = mysql_fetch_row($result);
		return $row[0];
	}	
}

?>