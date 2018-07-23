<?php

class Startup
 { 	 
	public $db;
	public $session;
	public $settings;
	public $processingstages;
	private static $instance;
	
	function __construct($directory)
	{		
		$settings_file = $directory."/Backend/settings.txt";
		$settings_file_content = file_get_contents($settings_file);
		
        $this->settings = json_decode($settings_file_content, true);
		include_once $directory."/Backend/Session.php";
		include_once $directory."/Backend/db.php";
		$this->db = new DB($this->settings['server'],$this->settings['username'],$this->settings['password'],$this->settings['database name']);
		$this->session = new Session();
	}
	
	public static function getInstance($directory)
	{
		return new Startup($directory);
  	}
}
?>