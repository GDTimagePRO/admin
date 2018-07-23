<?php
require_once 'Mage/Checkout/controllers/CartController.php';

class Genesys_Redirect_CartController extends Mage_Checkout_CartController
{
	public function addAction()
	{
		if (!$this->_validateFormKey()) {
            $this->_goBack();
            return;
        }
		
		$params = $this->getRequest()->getParams();
		$product = $this->_initProduct();
		if (!isset($params['orderDetails']) && $product && $product->getData('gdtbarcode')) {
			$genesysUrl = Mage::getStoreConfig('Genesys_Redirect/genesys_group/genesys_url');
			$jsonurl = "http://%s/SetUp.php?code=%s&sName=%s&return_url=%s&url=%s&system_name=magento&redirect=false";
			$barcode = $product->getData('gdtbarcode');
			$sName = urlencode(Mage::getStoreConfig('Genesys_Redirect/genesys_group/genesys_name'));;
			$magentoCurrentUrl = Mage::helper('core/url')->getCurrentUrl();
			$returnUrl = $magentoCurrentUrl;
			$return_url = Mage::getSingleton('core/session')->getLastUrl();
			$jsonurl = sprintf($jsonurl, $genesysUrl, $barcode, $sName, $return_url, $returnUrl);
			echo $jsonurl;
			$json = file_get_contents($jsonurl);
			$json_object = json_decode($json, true);
			$this->_redirectUrl($json_object["url"]);
		} else {
			parent::addAction();
		}
	}

}
?> 