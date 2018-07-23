<?php
require_once 'settings.php';

class ThemeInterface
{
	public $vars = NULL;
	public $customerId = NULL;
	public $themeName = NULL;
	public $homeURL = NULL;
	public $sessionId = NULL;
	public $config = NULL;
	
	public $HOME_URL = "";

	public function getVar($name)
	{
		if(isset($this->vars[$name])) return $this->vars[$name];
		if(!is_null($this->config) && isset($this->config->vars))
		{
			if(isset($this->config->vars->$name)) return $this->config->vars->$name; 
		}
		return '%' . $name . '%'; 
	}
	
	public function getVarHTML($name)
	{
		return htmlentities($this->getVar($name));   
	}
	
	public function getImageUrl($imageId, $noCaching = false)
	{
		return Settings::getImageUrl($imageId, $noCaching);
	}
	
	public function systemURL($url)
	{
		if(is_null($this->sessionId)) return $url;
		$url .= strpos($url, '?') ? '&' : '?';		
		$url .= 'sid=' . urlencode($this->sessionId);
		return $url; 
	}
}
?>