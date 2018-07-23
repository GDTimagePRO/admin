<?php
	include_once "order_logic.php";

	function loginUser($user)
	{
		global $_session;
		
		if(is_null($user)) return false;
		
		$_session->setActiveUserId($user->id);
		$_session->setReturnUrl("");
		
		clearSelectedOrderItem();

		return true;
	}

	function loginUserByCredentials($email, $password)
	{
		global $_user_db;
		
		if (($user = $_user_db->getUserByEmail($email)) != NULL)
		{
			if ($password == $user->password)
			{
				if(loginUser($user)) return $user;
			}
		}
		return NULL;
	}

	function loginNOPUser($email, $returnUrl)
	{
		global $_user_db;
		global $_session;
		
		$login = "//nop_".$email;
		$user = null;//$_user_db->getUserByEmail($login);
		if(is_null($user))
		{
			$user = new User();
			$user->email = $login;
			$user->dateCreated = time();
			$user->isWorkplaceAddress = false;
			$user->name = "NOP";
			$user->contactName = "";
			$user->department = "";
			$user->street = "";
			$user->city = "";
			$user->stateCode = "";
			$user->postalCode = "";
			$user->countryCode = "";
			$user->phone = "";
			$user->fax = "";
			$user->password = "";
			if(!$_user_db->createUser($user))
			{
				echo "Failed to create user.";
				exit;
			}		
		}
		
		if(!loginUser($user))
		{
			echo "failed to login user";
			exit;
		}
		
		$_session->setActiveUserId($user->id);
		$_session->setReturnUrl($returnUrl);
		setcookie("redirect", $returnUrl);
		return $user;
	}
?>