<?php
class Genesys_Redirect_CustomOptions_Observer
{
	public function add_custom_option($observer)
	{
		// set the additional options on the product
		$action = Mage::app()->getFrontController()->getAction();
		if ($action->getFullActionName() == 'checkout_cart_add')
		{
			if ($options = $action->getRequest()->getParam('orderDetails'))
			{
				$options = json_decode($options, true);
	
				$product = $observer->getProduct();

				// add to the additional options array
				$additionalOptions = array();
				if ($additionalOption = $product->getCustomOption('additional_options'))
				{
					$additionalOptions = (array) unserialize($additionalOption->getValue());
				}
				$additionalOptions[] = array(
					'label' => 'orderItemId',
					'value' => $options['orderItemId'],
				);

				// add the additional options array with the option code additional_options
				$observer->getProduct()->addCustomOption('additional_options', serialize($additionalOptions));
			}
		}
	}
	
	public function copy_custom_option_to_order($observer) 
	{
		$quoteItem = $observer->getItem();
		if ($additionalOptions = $quoteItem->getOptionByCode('additional_options')) {
			$orderItem = $observer->getOrderItem();
			$options = $orderItem->getProductOptions();
			$options['additional_options'] = unserialize($additionalOptions->getValue());

			$orderItem->setProductOptions($options);
		}
	}
}
?>