Deploying Genesys Magento Module

1. Extract zip into base Magento folder
2. Create a new attribute named genesys. Attribute should be scope Global, type yes/no, used in product listing yes
3. Make sure the attribute is used by all sets that contain products that need to go through the genesys system
4. Under System->Configuration set Gensys Config options to point to genesys instance
5. Refresh magento cache
6. Turn attribute genesys to yes for all products required to go through the genesys system


This module overwrites the following layout files:
	- catalog/product/list.phtml
	- catalog/product/compare/list.phtml
	- catalog/product/view/addtocart.phtml
	- checkout/cart/item/default.phtml

	All files are located in the app/design/frontend/base/default/template/Genesys_Redirect 

If using a custom layout, not the default or modified default layout, then you may need to move these files to the correct
location within your layout.