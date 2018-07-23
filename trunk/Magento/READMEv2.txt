Deploying Genesys Magento Module

1. Copy files into base Magento folder
2. Create a new attribute named gdtbarcode. Attribute should be scope Global, type TextField. This will be set to the customization barcode for the product
3. Make sure the attribute is used by all sets that contain products that need to go through the genesys system
4. Under System->Configuration set Gensys Config options to point to genesys instance and set customer name
5. Refresh magento cache

This module overwrites the following layout files and is based on the rwd template files in Magento version 1.9.2.4:
	- catalog/product/list.phtml
	- catalog/product/compare/list.phtml
	- catalog/product/view/addtocart.phtml
	- checkout/cart/item/default.phtml
	- checkout/cart/minicart/default.phtml

	All files are located in the app/design/frontend/base/default/template/Genesys_Redirect 

If using a custom layout, not the default or modified default layout, then you may need to move these files to the correct
location within your layout.