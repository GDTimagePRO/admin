<?php
/**
 * Settings file. 
 *
 * This is the file that keeps track of all necessary background information. It is essentially used
 * as a global variable keeper. 
 */

 class Startup{ 	 

	public $db;
	public $session;
	public $settings;
	public $processingstages;
	//public $directory = "C:/work/V3";
	//public $directory = "/cam/ssi";
	private static $instance;
	function __construct($directory){
		
		//load settings from file
		$settings_file = $directory."/Backend/settings.txt";
		$settings_file_content = file_get_contents($settings_file);
        /* Load the language file as a JSON object and transform it into an associative array */
        $this->settings = json_decode($settings_file_content, true);
		//$directory = $this->settings['directory'];
		include_once $directory."/Backend/User.php";
		include_once $directory."/Backend/Session.php";
		include_once $directory."/Backend/database.php";
		$this->db = new Database($this->settings['server'],$this->settings['username'],$this->settings['password'],$this->settings['database name']);
		$this->session = new Session();
		$this->processingstages = $this->db->getProcessingStages();
	}
	
	public static function getInstance($directory) {
	    if (!isset(self::$instance)) { 
	      self::$instance = new Startup($directory);
	    }
	    return self::$instance;
  }
}
?>