<?php
	
	require_once 'backend/startup.php';
	$system = Startup::getInstance();
	
	/**
	 * @param RedemptionCodeGroup $group
	 */
	function addBarcode($group)
	{
		global $system;
		
		$code = new RedemptionCode();
		$code->groupId = $group->id;
		$code->customerId = $group->customerId;

		$tryCount = 0;
				
		$alphabetLen = count(RedemptionCode::$CODE_ALPHABET);
		while ($tryCount < 1000) 
		{
			$code->code = '';
			for($i = 0; $i < RedemptionCode::CODE_LENGTH; $i++)
			{
				$code->code .= RedemptionCode::$CODE_ALPHABET[
						mt_rand(0, $alphabetLen-1)
					];
			}
			
			$code->code = RedemptionCode::formatCode($code->code);
			
			if($system->db->redemption->createRedemptionCode($code))
			{
				return true;
			}
			
			$tryCount++;
		}
		
		return false;
	}
	
	$group = $system->db->redemption->getRedemptionCodeGroupById(1);
	for($i=0; $i<1000; $i++)
	{
		addBarcode(2);
	}
?>