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
		if (!isset($params['orderDetails'])) {
			$product = $this->_initProduct();
			$genesysUrl = Mage::getStoreConfig('Genesys_Redirect/genesys_group/genesys_url');
			$jsonurl = "https://%s/shopify/shopifysetup.php?code=%s&shop=%s&submitUrl=%s";
			$sku = $product->getSku();
			$sName = 'GDT  (magento)';
			$magentoCurrentUrl = Mage::helper('core/url')->getCurrentUrl();
			$returnUrl = $magentoCurrentUrl;
			$return_url = Mage::getSingleton('core/session')->getLastUrl();
			$jsonurl = sprintf($jsonurl, $genesysUrl, $sku, $sName, $returnUrl);
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