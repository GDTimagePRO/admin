<?php
	require_once "utils.php";
	require_once "startup.php";
	require_once "resource_manager.php";

	class DesignTextElement
	{
		public static function writeJS($customerId, $customerName)
		{
			echo '	TextElement.FONTS = [' . "\n";
			#legacy fonts
			/*$legacyFonts = new ResourceId(ResourceManager::GROUP_LEGACY_FONTS);
			$legacyFonts = $legacyFonts->getFileList(TRUE);
			asort($legacyFonts);

			$isFirst = true;
			foreach($legacyFonts as $fontId)
			{
				$fileName = substr($fontId, strrpos($fontId, '/') + 1);
				if((strlen($fileName) > 4) && endsWith(strtolower($fileName), '.ttf'))
				{
					if(!$isFirst) echo ',';
					$isFirst = false;

					$filePath = substr($fontId, strpos($fontId, '/') + 1);
					$legacyId = substr($fileName, 0, strlen($fileName) - 4);

					echo sprintf(
							'{ name:"%s", id:"%s", legacyId:"%s"}',
							str_replace('"', '\\"', str_replace('\\', '\\\\', substr($filePath, 0, strrpos($filePath, ".")))),
							str_replace('"', '\\"', str_replace('\\', '\\\\', $fontId)),
							str_replace('"', '\\"', str_replace('\\', '\\\\', $legacyId))
					) . "\n";
				}
			}*/

			$customerFonts = new ResourceId(
					ResourceManager::GROUP_CUSTOMER,
					$customerId . '/' . ResourceManager::DIR_CUSTOMER_FONTS
			);
			$customerFonts = $customerFonts->getFileList(TRUE);
			asort($customerFonts);

			foreach($customerFonts as $fontId)
			{
				$fileName = substr($fontId, strrpos($fontId, '/') + 1);
				if((strlen($fileName) > 4) && endsWith(strtolower($fileName), '.ttf'))
				{
					if(!$isFirst) echo ',';
					$isFirst = false;

					$filePath = substr($fontId, strpos($fontId, '/', strpos($fontId, '/') + 1) + 1);
					$filePath = substr($filePath, strlen(ResourceManager::DIR_CUSTOMER_FONTS) + 1);

					echo sprintf(
							'{name:"%s [%s] : %s", id:"%s"}',
							strtolower(substr($fileName, 0 , 1)),
							str_replace('"', '\\"', str_replace('\\', '\\\\', $customerName)),
							str_replace('"', '\\"', str_replace('\\', '\\\\', $filePath)),
							str_replace('"', '\\"', str_replace('\\', '\\\\', $fontId))
					) . "\n";
				}
			}

			echo '	];' . "\n";
		}
	}

	class DesignElements
	{
		public static function writeJS($customerId)
		{
			$system = Startup::getInstance();
			$customer = $system->db->order->getCustomerById($customerId);

			DesignTextElement::writeJS($customerId, $customer->description);
		}
	}

?>
