<?php
//TODO: Ensure that lines do not get cutoff

	include_once "_common.php";	
	$k = 6;
	$col_unique_id = 0;
	$col_order_id = 1;
	$col_product_id = 3;
	$col_display_name = 4;
	
	function getTokens($str, $delim)
	{
		$a = explode($delim, $str);
		$i = 0;
		while($i < count($a))
		{			
			if($a[$i] == '')
			{
				array_splice($a, $i, 1);
			}
			else
			{
				$i++;
			}
		}
		return $a;
	}
		
	function getVal(&$r_master, $col, $line)
	{
		$v = $r_master[$col];
		if(count($v) > $line) return $v[$line];
		return ''; 
	}
	
	function findKeyValue(&$row, $name)
	{
		global $k;
		for($icol=0; $icol<=28; $icol+=2)
		{
			if($row[$k + $icol] == $name)
			{
				return $k + $icol + 1;
			}
		}
		return -1;
	}
	
	function fixCorruptKeyValue(&$row, $name)
	{		
		global $k;
		$len = strlen($name);		
		for($icol=0; $icol<=28; $icol+=2)
		{
			$m = $k + $icol;
			if(substr($row[$m], 0, $len) == $name)
			{
				if($row[$m] != $name)
				{
					$row[$m + 1] = substr($row[$m], $len);
					$row[$m] = $name;
				}
					
				return $m + 1;
			}
		}
		return -1;
	}
	
	function format_zip(&$r_master, $name)
	{
		$zipCode = findKeyValue($r_master, $name);
		if($zipCode > -1)
		{
			$value = trim($r_master[$zipCode]);
			if(is_numeric($value) && (strlen($value) == 4)) $value = '0'.$value;
			$r_master[$zipCode] = $value;
		}
	}
	
	function format_trim(&$r_master, $name)
	{
		$keyValue = findKeyValue($r_master, $name);
		if($keyValue > -1)
		{
			$r_master[$keyValue] = trim($r_master[$keyValue]);
		}
	}
	
	function format_replace(&$r_master, $name, $oldValue, $newValue)
	{
		$keyValue = findKeyValue($r_master, $name);
		if($keyValue > -1)
		{
			$r_master[$keyValue] = str_replace($oldValue, $newValue, $r_master[$keyValue]);
		}
	}
	
	function format_lcase(&$r_master, $name)
	{
		$keyValue = findKeyValue($r_master, $name);
		if($keyValue > -1)
		{
			$r_master[$keyValue] = strtolower($r_master[$keyValue]);
		}
	}
	
	function format_ucase(&$r_master, $name)
	{
		$keyValue = findKeyValue($r_master, $name);
		if($keyValue > -1)
		{
			$r_master[$keyValue] = strtoupper($r_master[$keyValue]);
		}
	}
	
	function format_ucase_name(&$r_master, $name)
	{
		$keyValue = findKeyValue($r_master, $name);
		if($keyValue > -1)
		{
			$value = strtoupper(trim($r_master[$keyValue]));
			if($value == 'THE') return;			
			
			$value = preg_replace('/\bMC/', 'Mc', $value);
			
			$r_master[$keyValue] = $value; 
		}
	}
	
	
	function format_lineSeparatedJack(&$r_master, $in_key, $out_line1, $out_line2)
	{
		 
		$line1 = ' ';
		$line2 = ' ';
		
		$keyValue = findKeyValue($r_master, $in_key);
		if($keyValue > -1)
		{		
				
			
			$value = trim($r_master[$keyValue]);
			$line1 = $value;
			
			$lines = preg_split("/(\r\n|\n|\r)/", $value);
			$i = 0;
			while($i < count($lines))
			{
				if(trim($lines[$i]) == '')
				{
					array_splice($lines, $i, 1);
				}
				else $i++;
			}
	
			if(count($lines) > 1)
			{
				$line1 = trim($lines[0]);
				$line2 = trim($lines[1]);
			}
			else
			{
				$value = str_replace(' ,', ',', $value);
				$value = str_replace(' ,', ',', $value);
				$value = str_replace(' ,', ',', $value);
				$value = str_replace(' ,', ',', $value);
				
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				
				$a = explode(' ', $value);
				$breakPoint = -1;
				for($i = count($a) - 1; $i >= count($a) / 3; $i--)
				{
					if(substr($a[$i], -1, 1) == ',')
					{
						$breakPoint = $i;
						break;
					}
				}
				
				if(($breakPoint == -1) && (count($a) > 5))
				{
					$breakPoint = count($a) - 3;
				}
				
				if($breakPoint != -1)
				{
					$line1 = $a[0];
					for($i = 1; $i < $breakPoint; $i++)
					{
						$line1 .= ' '.$a[$i];
					}
					
					$line2 = $a[$breakPoint];
					for($i = $breakPoint + 1; $i < count($a); $i++)
					{
						$line2 .= ' '.$a[$i];
					}
				}
			}
		}

		addKeyValue($r_master, $out_line1, $line1);
		addKeyValue($r_master, $out_line2, $line2);
	}
	
	function format_name(&$r_master, $in_name, $out_Top, $out_Bottom, $out_Middle, $prefix_The)
	{
		$name_top = ' ';
		$name_bottom = ' ';
		$name_middle = ' ';
		
		$name = findKeyValue($r_master, $in_name);
		if($name > -1)
		{
			$value = trim($r_master[$name]);			
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
				
			$valueLC = strtolower($value);
			$name_top = $value;
				
			$lines = preg_split("/(\r\n|\n|\r)/", $value);
			$i = 0;
			while($i < count($lines))
			{
				if(trim($lines[$i]) == '')
				{
					array_splice($lines, $i, 1);
				}
				else $i++;
			}
		
			if(count($lines) > 1)
			{
				$name_top = trim($lines[0]);
				$name_bottom = trim($lines[1]);
			}
			else
			{
				$l1_len = strrpos($value, ' ');
				if($l1_len !== FALSE)
				{
					$name_top = trim(substr($value, 0, $l1_len));
					$name_bottom = trim(substr($value, $l1_len));
					
					$lastChar = substr($name_top, -1, 1);			
					if(($lastChar == '&') || ($lastChar == '+'))
					{
						$name_top = $value;
						$name_bottom = ' ';
					}
				}
			}
		
			if($prefix_The && (strtolower($name_top) == 'the'))
			{
				if(strtolower(substr($name_bottom, -1, 1)) != 's')
				{
					$name_top = $name_bottom;
					$name_bottom = ' ';
				}
				else
				{
					$name_top = 'the';
				}
			}		
		}
		
		if((strlen($name_top) < 5) && (substr($name_top, -1, 1) == '.'))
		{
			$name_top = trim($name_top).' '.trim($name_bottom);
			$name_bottom = ' ';
		}
			
		if($name_bottom == ' ')
		{			
			if($out_Middle != '')
			{
				$name_middle = $name_top;
				$name_top = ' ';
			}
			else
			{
				$name_bottom = $name_top;
				$name_top = ' ';
			}
		}		
		
		addKeyValue($r_master, $out_Top, $name_top);
		addKeyValue($r_master, $out_Bottom, $name_bottom);

		if($out_Middle != '') 
		{
			addKeyValue($r_master, $out_Middle, $name_middle);
		}
	}
	
	function format_3initials(&$r_master, $in_name, $out_first, $out_Middle, $out_last)
	{
		$name = findKeyValue($r_master, $in_name);
		if($name > -1)
		{
			$value = strtoupper(trim($r_master[$name]));
			$value = preg_replace('/(\r\n|\n|\r)/', '', $value);
			$value = str_replace(' ', '', $value);								
			$value = str_replace('/', '', $value);
			$value = str_replace('\\', '', $value);
			
			addKeyValue($r_master, $out_first, substr($value, 0, 1));
			addKeyValue($r_master, $out_Middle, substr($value, 1, 1));
			addKeyValue($r_master, $out_last, substr($value, 2, 1));
		}
	}
	
	function format_3LineName(&$r_master, $in_name, $out_Top, $out_Middle, $out_Bottom)
	{
		$name_top = ' ';
		$name_middle = ' ';
		$name_bottom = ' ';
		
		$name = findKeyValue($r_master, $in_name);
		if($name > -1)
		{
			$value = trim($r_master[$name]);
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
			
			$valueLC = strtolower($value);
			$name_top = $value;
				
			$lines = preg_split("/(\r\n|\n|\r)/", $value);
			$i = 0;
			while($i < count($lines))
			{
				if(trim($lines[$i]) == '')
				{
					array_splice($lines, $i, 1);
				}
				else $i++;
			}
		
			if(count($lines) > 1)
			{
				$name_top = trim($lines[0]);
				$name_middle = trim($lines[1]);
				
				if(count($lines) > 2)
				{
					$name_bottom = trim($lines[2]);
				}
			}
			else
			{
				$l1_len = strrpos($value, ' ');
				if($l1_len !== FALSE)
				{
					$name_top = trim(substr($value, 0, $l1_len));
					$name_middle = trim(substr($value, $l1_len));
					
					$l1_len = strrpos($name_top, ' ');
				}
				
				if($l1_len !== FALSE)
				{				
					$name_bottom = $name_middle;
					$name_middle = trim(substr($name_top, $l1_len));
					$name_top = trim(substr($name_top, 0, $l1_len));
				}				
			}

			for($i=0; $i < 2; $i++)
			{				
				if((strlen($name_top) < 5) && (substr($name_top, -1, 1) == '.'))
				{
					$name_top = trim($name_top).' '.trim($name_middle);
					$name_middle = $name_bottom;
					$name_bottom = ' ';
				}
			}
					
			if((strlen($name_middle) < 5) && (substr($name_middle, -1, 1) == '.'))
			{
				$name_middle = trim($name_middle).' '.trim($name_bottom);
				$name_bottom = ' ';
			}
			
			$lastValue = $name_bottom;
			if($lastValue == ' ') $lastValue = $name_middle;  
			if($lastValue == ' ') $lastValue = $name_top;
				
			if($name_bottom == ' ')
			{
				if((substr($lastValue, -1, 1) == 's') && (strtolower($name_top) != 'the'))
				{
					$name_bottom = $name_middle;
					$name_middle = $name_top;
					$name_top = 'the';
				}
			}

			for($i=0; $i < 2; $i++)
			{
				if($name_bottom == ' ')
				{
					$name_bottom = $name_middle;
					$name_middle = $name_top;
					$name_top = ' ';
				}
			}
		}

		addKeyValue($r_master, $out_Top, $name_top);
		addKeyValue($r_master, $out_Middle, $name_middle);
		addKeyValue($r_master, $out_Bottom, $name_bottom);
	}
	
	function format_streetNameAndNumber(&$r_master, $in_addressLine, $out_streetName, $out_streetNumber)
	{
		$street_number = ' ';
		$street_name = ' ';
		
		$addr_line = findKeyValue($r_master, $in_addressLine);
		if($addr_line > -1)
		{
			$value = trim($r_master[$addr_line]);
			$value = str_replace('\\', ' ', $value);
			$value = str_replace('/', ' ', $value);
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
								
			$street_number = $value;
		
			$numLen = strpos($value, ' ');
			if($numLen !== FALSE)
			{
				$street_number = substr($value, 0, $numLen);
				$street_name = trim(substr($value, $numLen));
		
			}
		}
	
		addKeyValue($r_master, $out_streetName, $street_name);
		addKeyValue($r_master, $out_streetNumber, $street_number);
	}	
	
	function format_cityStateZip(&$r_master, $in_addressLine, $out_cityState, $out_zipCode)
	{
		$cityState = ' ';
		$zipCode = ' ';
		
		
		$addr_line = findKeyValue($r_master, $in_addressLine);
		if($addr_line > -1)
		{
		
			$value = trim($r_master[$addr_line]);
			$value = str_replace('\\', ' ', $value);
			$value = str_replace('/', ' ', $value);
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
			$value = str_replace('  ', ' ', $value);
				
			
			$cityState = $value; 
			
			$numLen = strrpos($value, ' ');
			if($numLen !== FALSE)
			{
				$l1 = substr($value, 0, $numLen);
				$l2 = trim(substr($value, $numLen));
		
				//if(is_numeric($l2))
				{					
					$cityState = $l1;
					$zipCode = $l2;
				}
			}
		}
		
		addKeyValue($r_master, $out_cityState, $cityState);
		addKeyValue($r_master, $out_zipCode, $zipCode);
		
		if($zipCode != ' ') format_zip($r_master, $out_zipCode);
	}
	
	function str_replace_and_match_case($oldValue, $newValue, $str)
	{
		$str =  str_replace($oldValue, $newValue, $str);
		$str =  str_replace(strtolower($oldValue), strtolower($newValue), $str);
		$str =  str_replace(strtoupper($oldValue), strtoupper($newValue), $str);
		return $str;
	}
	
	function renameKey(&$row, $oldName, $newName)
	{
		$i = findKeyValue($row, $oldName);
		if($i > 0)
		{
			$row[$i - 1] = $newName;
			return true;
		}
		return false;
	}
	
	function removeKey(&$row, $name)
	{
		$i = findKeyValue($row, $name);
		if($i > 0)
		{
			$row[$i - 1] = '';
			$row[$i] = '';
			return true;
		}
		return false;
	}
	
	function addKeyValue(&$row, $name, $value)
	{
		global $k;
		for($icol=$k; $icol<=28; $icol+=2)
		{
			if($row[$icol] == '')
			{
				$row[$icol] = $name;
				$row[$icol+1] = $value;				
				return TRUE;
			}
		}
		return FALSE;
	}
	
	function adjustRow(&$r_master)
	{
		global $col_unique_id;
		global $col_order_id;
		global $col_product_id;
		global $col_display_name;
				
		if($r_master[$col_display_name] == 'CHEN')
		{
			//$r[$col_display_name] = '';									
			//renameKey($r, 'Stamp 2 - Name', 'Greeting (max characters: 20)');
			//renameKey($r, 'aaa', 'Name (max characters: 20)');
		}	
		else if($r_master[$col_display_name] == 'WOLFS')
		{			
			format_cityStateZip(
				$r_master, 
				'Address Line 2 (max characters: 20)', 
				'City/ State', 
				'Zip Code'
			);
		}
		else if($r_master[$col_display_name] == 'LONGORIA')
		{
			format_3LineName(
				$r_master,
				'Name (max characters: 20)',
				'Name Line 1',
				'Name Line 2',
				'Name Line 3'
			);
				
			format_zip($r_master, 'Middle - Zip Code (max characters: 5)');
		}		
		else if($r_master[$col_display_name] == 'MORGAN')
		{
			$monthDayIndex = findKeyValue($r_master, 'month and day');
			if($monthDayIndex > -1)
			{
				$value = trim($r_master[$monthDayIndex]);
				$value = preg_replace('/(\r\n|\n|\r)/', ' ', $value);
				$value = str_replace('\\', ' ', $value);
				$value = str_replace('/', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);

				$value = str_replace_and_match_case('January', 'Jan.', $value);
				$value = str_replace_and_match_case('February', 'Feb.', $value);
				$value = str_replace_and_match_case('September', 'Sept.', $value);
				$value = str_replace_and_match_case('October', 'Oct.', $value);
				$value = str_replace_and_match_case('November', 'Nov.', $value);
				$value = str_replace_and_match_case('December', 'Dec.', $value);
								
				$value = str_replace('  ', ' ', $value);
				
				$r_master[$monthDayIndex] = $value; 
			}
			
			$nameIndex = findKeyValue($r_master, 'name');
			if($nameIndex > -1)
			{
				$value = $r_master[$nameIndex];
				$value = preg_replace('/(\r\n|\n|\r)/', ' ', $value);
				$value = str_replace(' and ', ' ', $value);
				$value = str_replace('+', ' ', $value);
				$value = str_replace('&', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				
				$l1_len = strrpos($value, ' ');
				if($l1_len !== FALSE)
				{
					addKeyValue($r_master, 'name 1', trim(substr($value, 0, $l1_len)));
					addKeyValue($r_master, 'name 2', trim(substr($value, $l1_len)));
				}
				else
				{
					addKeyValue($r_master, 'name 1', ' ');
					addKeyValue($r_master, 'name 2', ' ');
				}
			}				
			format_trim($r_master, 'year');				
		}		
		else if($r_master[$col_display_name] == 'HARRISON')
		{
			$initial = findKeyValue($r_master, 'Initial (max characters: 1)');
			if($initial > -1) $r_master[$initial] = substr(trim($r_master[$initial]), 0, 1);
		}
		else if($r_master[$col_display_name] == 'JACK')
		{
			$initial = findKeyValue($r_master, 'Initial (max characters: 1)');
			if($initial > -1) $r_master[$initial] = substr(trim($r_master[$initial]), 0, 1);
			
			$name = findKeyValue($r_master, 'Name (max characters: 20)');
			if($name > -1)
			{
				$value = trim($r_master[$name]);
				
				$value = str_replace('+', ' + ', $value);
				$value = str_replace('&', ' + ', $value);
				$value = str_replace(' and ', ' + ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
								
				$r_master[$name] = $value; 
			}
		}
		else if($r_master[$col_display_name] == 'JEFFREYS')
		{
			$initial = findKeyValue($r_master, 'Initial (max characters: 1)');
			if($initial > -1) $r_master[$initial] = substr(trim($r_master[$initial]), 0, 1);
		}
		else if($r_master[$col_display_name] == 'HENDERSON')
		{
			format_zip($r_master, 'Middle - Zip Code (max characters: 5)');
		}
		else if($r_master[$col_display_name] == 'MARKET STREET')
		{
			format_zip($r_master, 'Address Line 3 (max characters: 20)');
		}
		else if($r_master[$col_display_name] == 'MAKENZIE')
		{
			$name = findKeyValue($r_master, 'Name (max characters: 20)');
			if($name > -1)
			{
				$value = trim($r_master[$name]);
				if(substr($value, -1, 1) == 'd') $value.='  ';
				$r_master[$name] = $value;
			}
		}
		else if($r_master[$col_display_name] == 'MULBERRY')
		{
			format_zip($r_master, 'Address Line 3 (max characters: 20)2');
		}
		else if($r_master[$col_display_name] == 'ROBERTSON')
		{
			$initial = findKeyValue($r_master, 'Initial (max characters: 1)');
			if($initial > -1) $r_master[$initial] = substr(trim($r_master[$initial]), 0, 1);
		
			$name = findKeyValue($r_master, 'Name (max characters: 20)');
			if($name > -1)
			{
				$value = trim($r_master[$name]);
				$valueLC = strtolower($value);
				if(substr($valueLC, 0, 4) == 'the ') $value = substr($value, 4);
				$r_master[$name] = $value;
			}
		
		
			format_zip($r_master, 'Address Line 3 (max characters: 20)');
		}
		else if($r_master[$col_display_name] == 'PREDDER')
		{
			$address1 = findKeyValue($r_master, 'Address Line 1 (max characters: 20)');
			if($address1 > -1) $r_master[$address1] = str_replace(' ', '  ', $r_master[$address1]);
				
			$address2 = findKeyValue($r_master, 'Address Line 2 (max characters: 20)');
			if($address2 > -1) $r_master[$address2] = str_replace(' ', '  ', $r_master[$address2]);
		}		
		else if($r_master[$col_display_name] == 'WILLIAMS')
		{
			format_name(
				$r_master, 
				'Name (max characters: 20)', 
				'First name(s)', 
				'Last name', 
				'name middle', 
				true
			);
			
			format_ucase_name( $r_master, 'Last name');
			format_ucase_name( $r_master, 'name middle');
				
			format_streetNameAndNumber(
				$r_master,
				'Address Line 1 (max characters: 20)',
				'Street name',
				'Street number'
			);
			
			format_cityStateZip(
				$r_master,
				'Address Line 2 (max characters: 20)',
				'City, State',
				'ZIP Code'
			);
		}
		else if($r_master[$col_display_name] == 'CLEARMONT')
		{
			format_ucase_name( $r_master, 'Name (max characters: 20)');
				
		}
		else if($r_master[$col_display_name] == 'RICHARDSON')
		{
			$name = findKeyValue($r_master, 'Names (max characters: 40)');
			if($name > -1)
			{
				
				$value = trim($r_master[$name]);
				$valueLC = strtolower($value);
				$name_top = ' ';
				$name_bottom = ' ';
				$name_mid = ' ';
		
				$lines = preg_split("/(\r\n|\n|\r)/", $value);
				$i = 0;
				while($i < count($lines))
				{
					if(trim($lines[$i]) == '')
					{
						array_splice($lines, $i, 1);
					}
					else $i++;
				}
		
				if(count($lines) > 1)
				{
					$name_top = trim($lines[0]);
					$name_bottom = trim($lines[1]);
				}
				else
				{
					if((strpos($valueLC, '&') !== FALSE) || (strpos($valueLC, '+') !== FALSE) || (strpos($valueLC, ' and ') !== FALSE))
					{
						$numLen = strrpos($value, ' ');
						if($numLen !== FALSE)
						{
							$l1 = trim(substr($value, 0, $numLen));
							$l2 = trim(substr($value, $numLen));
							$lastChar = substr($l1, -1, 1);
								
							if(($lastChar != '&') && ($lastChar != '+'))
							{
								$name_top = $l1;
								$name_bottom = $l2;
							}
							else $name_mid =  $value;
						}
						else $name_mid =  $value;
					}
					else $name_mid =  $value;
				}
				 
				addKeyValue($r_master, 'Names', $name_top);
				addKeyValue($r_master, 'Last Name', $name_bottom);
				addKeyValue($r_master, 'Last Name only', $name_mid);
			}

			format_streetNameAndNumber(
				$r_master,
				'Address Line 1 (max characters: 20)',
				'street name',
				'Street number'
			);
		
			format_cityStateZip(
				$r_master,
				'Address Line 2 (max characters: 20)',
				'city/ state',
				'zip code'
			);		
		}
		else if($r_master[$col_display_name] == 'EMILY')
		{
			renameKey($r_master, 'Names (max characters: 20)', 'names');
			renameKey($r_master, 'City, State (max characters: 20)', 'City, State');
			
			$date = findKeyValue($r_master, 'Date (max characters: 20)');
			if($date > -1)
			{
				$value = strtolower(trim($r_master[$date]));
				$value = preg_replace('/(\r\n|\n|\r)/', ' ', $value);
				$value = str_replace('\\', ' ', $value);
				$value = str_replace('-', ' ', $value);
				$value = str_replace('/', ' ', $value);
				$value = str_replace(',', ' ', $value);
				$value = str_replace('.', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				$value = str_replace('  ', ' ', $value);
				
				$vals = explode(' ', $value);
				if(count($vals) == 3)
				{
					$vals[1] = str_replace('st', '', $vals[1]);
					$vals[0] = str_replace('st', '', $vals[0]);
						
					if(is_numeric($vals[0]) && !is_numeric($vals[1]))
					{
						$tmp = $vals[1];
						$vals[1] = $vals[0];
						$vals[0] = $tmp;
					}

					if(is_numeric($vals[2]) && (intval($vals[2]) < 100))
					{
						$vals[2] = intval($vals[2]) + 2000;
					}
						
					if($vals[0] == 'january') $vals[0] = '1';
					if($vals[0] == 'jan') $vals[0] = '1';
					
					if($vals[0] == 'february') $vals[0] = '2';
					if($vals[0] == 'feb') $vals[0] = '2';
					
					if($vals[0] == 'march') $vals[0] = '3';
					
					if($vals[0] == 'april') $vals[0] = '4';
					
					if($vals[0] == 'may') $vals[0] = '5';
					
					if($vals[0] == 'june') $vals[0] = '6';
					
					if($vals[0] == 'july') $vals[0] = '7';
					if($vals[0] == 'jul') $vals[0] = '7';
					
					if($vals[0] == 'augu') $vals[0] = '8';
					if($vals[0] == 'aug') $vals[0] = '8';
					
					if($vals[0] == 'september') $vals[0] = '9';
					if($vals[0] == 'sep') $vals[0] = '9';
					
					if($vals[0] == 'october') $vals[0] = '10';
					if($vals[0] == 'oct') $vals[0] = '10';
					
					if($vals[0] == 'november') $vals[0] = '11';
					if($vals[0] == 'nov') $vals[0] = '11';
					
					if($vals[0] == 'december') $vals[0] = '12';
					if($vals[0] == 'dec') $vals[0] = '12';
					
					if(is_numeric($vals[1]) && (intval($vals[1]) < 10))
					{
						$vals[1] = '0'.intval($vals[1]);
					}
					
					$dayMonthVal = $vals[0].'/'.$vals[1]; 
					$dayMonthVal = str_replace(' ', '/', $dayMonthVal);					
					$yearVal = $vals[2];
				}
				else
				{
					$dayMonthVal = trim($r_master[$date]);
					$yearVal = ' ';
				}
				
				addKeyValue($r_master, 'day/month', $dayMonthVal);
				addKeyValue($r_master, 'year', $yearVal);
			}
			else
			{
				addKeyValue($r_master, 'day/month', ' ');
				addKeyValue($r_master, 'year', ' ');
			}
						
		}
		else if($r_master[$col_display_name] == 'JOSH')
		{
			$name = findKeyValue($r_master, 'Name (max characters: 20)');
			if($name > -1)
			{
				
				$value = trim($r_master[$name]);
				$valueLC = strtolower($value);
				$name_top = ' ';
				$name_bottom = ' ';
		
				$name_top = $value;
				 
				$lines = preg_split("/(\r\n|\n|\r)/", $value);
				$i = 0;
				while($i < count($lines))
				{
					if(trim($lines[$i]) == '')
					{
						array_splice($lines, $i, 1);
					}
					else $i++;
				}
		
				if(count($lines) > 1)
				{
					$name_top = trim($lines[0]);
					$name_bottom = trim($lines[1]);
				}
				else
				{
					$l1_len = strrpos($valueLC, '&');
					if($l1_len !== FALSE) $l1_len += 1;
					else
					{
						$l1_len = strrpos($valueLC, '+');
						if($l1_len !== FALSE) $l1_len += 1;
					}
		
					if($l1_len === FALSE)
					{
						$l1_len = strrpos($valueLC, ' and ');
						if($l1_len !== FALSE) $l1_len += 5;
					}
		
					if($l1_len === FALSE)
					{
						if(substr($valueLC, 0, 4) == 'the ') $l1_len = 3;
					}
		
					if($l1_len === FALSE)
					{
						$l1_len = strrpos($valueLC, ' ');
					}
		
					if($l1_len !== FALSE)
					{
						$name_top = trim(substr($value, 0, $l1_len));
						$name_bottom = trim(substr($value, $l1_len));
					}
				}
		
				addKeyValue($r_master, 'First name 1', $name_top);
				addKeyValue($r_master, 'First name 2', $name_bottom);
			}
		}
		else if($r_master[$col_display_name] == 'STEPHENS')
		{

			format_name(
				$r_master,
				'Name (max characters: 20)',
				'First name(s)',
				'Last name',
				'',
				false
			);

			format_zip($r_master, 'Address Line 3 (max characters: 20)');
		}
		else if($r_master[$col_display_name] == 'DELHAGEN')
		{
		}
		else if($r_master[$col_display_name] == 'SORENSEN')
		{
			format_3initials(
				$r_master,
				'Initials - left to right (max characters: 3)',
				'First Initial',
				'Middle Initial',
				'Last initial'
			);
		}
		else if($r_master[$col_display_name] == 'GALANTE')
		{
			format_lcase($r_master, 'Initials - left to right (max characters: 3)');
			format_3initials(
				$r_master,
				'Initials - left to right (max characters: 3)',
				'First Initial',
				'Middle Initial',
				'Last initial'
			);
		}
	}
	
	
	
	function importData($filename, $minOrderId, $includeSingles)
	{
		global $_system;
		
		global $col_unique_id;
		global $col_order_id;
		global $col_product_id;
		global $col_display_name;
		
		//$filename='couponsmason-130118-1-Bob.csv';
		//$filename='MMC MR BATCH 2 mason sales.csv';
		$delimiter=',';
		$esc='"';
		global $k;
		
	    if(!file_exists($filename) || !is_readable($filename)) return FALSE;
	
	    if (($handle = fopen($filename, 'r')) !== FALSE)
	    {
	    	if(($cols = fgetcsv($handle, 2000, $delimiter, $esc)) == FALSE) return FALSE;	    	

	        while (($r_master = fgetcsv($handle, 2000, $delimiter, $esc)) !== FALSE)
	        {	        	
	        	if(	($r_master[2] == 'In Process') &&	        		
	        		(
						$includeSingles && (
								
// 	 	        		(($r_master[$col_product_id] == 'AD-1002') && ($r_master[$col_display_name] == 'LONGORIA')) ||	        					      
// 	 	        		(($r_master[$col_product_id] == 'AD-1001') && ($r_master[$col_display_name] == 'WILSON')) ||
// 	 	        		(($r_master[$col_product_id] == 'AD-1007') && ($r_master[$col_display_name] == 'HOLLOWAY')) ||
// 	 	        		(($r_master[$col_product_id] == 'AD-1008') && ($r_master[$col_display_name] == 'HENDERSON')) ||
// 	 					(($r_master[$col_product_id] == 'AD-1010') && ($r_master[$col_display_name] == 'MARKET STREET')) ||	        				
//  	 	        		(($r_master[$col_product_id] == 'AD-1013') && ($r_master[$col_display_name] == 'WILLIAMS')) ||
// 	 	        		(($r_master[$col_product_id] == 'AD-1014') && ($r_master[$col_display_name] == 'RICHARDSON')) ||
// 	 	        		(($r_master[$col_product_id] == 'AD-1015') && ($r_master[$col_display_name] == 'STEPHENS')) ||
// 	 	        		(($r_master[$col_product_id] == 'AD-1016') && ($r_master[$col_display_name] == 'JOSH')) ||
// 	 	        		(($r_master[$col_product_id] == 'AD-1017') && ($r_master[$col_display_name] == 'MULBERRY')) ||	        				 
// 	 	        		(($r_master[$col_product_id] == 'AD-1022') && ($r_master[$col_display_name] == 'LOGAN')) ||
//  	        			(($r_master[$col_product_id] == 'AD-1023') && ($r_master[$col_display_name] == 'CASTLE')) ||
// 	       				(($r_master[$col_product_id] == 'AD-1024') && ($r_master[$col_display_name] == 'MAKENZIE')) ||
// 	 	        		(($r_master[$col_product_id] == 'AD-1025') && ($r_master[$col_display_name] == 'JEFFREYS')) ||
// 	 					(($r_master[$col_product_id] == 'AD-1026') && ($r_master[$col_display_name] == 'JACK')) ||
// 						(($r_master[$col_product_id] == 'AD-1027') && ($r_master[$col_display_name] == 'HANSEN')) ||
//  						(($r_master[$col_product_id] == 'AD-1030') && ($r_master[$col_display_name] == 'CLEARMONT')) ||
// 						(($r_master[$col_product_id] == 'BR-1001') && ($r_master[$col_display_name] == 'ELLA')) ||
 						(($r_master[$col_product_id] == 'AD-1018') && ($r_master[$col_display_name] == 'ROBERTSON')) ||      				 
// 						(($r_master[$col_product_id] == 'AD-1021') && ($r_master[$col_display_name] == 'PREDDER')) ||
// 	        			(($r_master[$col_product_id] == 'AD-1028') && ($r_master[$col_display_name] == 'HARRISON')) ||
// 						(($r_master[$col_product_id] == 'BR-1008') && ($r_master[$col_display_name] == 'EMILY')) ||
						
						FALSE) ||
	        					        				
// 						($r_master[$col_product_id] == 'TR-1001') ||
// 						($r_master[$col_product_id] == 'TR-1002') ||
// 	        			($r_master[$col_product_id] == 'TR-1003') ||
// 						($r_master[$col_product_id] == 'TR-1008') ||
	        					      
	        			FALSE

	        		)
					&& 	(intval($r_master[$col_order_id]) > $minOrderId)
// 					&& 	(	(intval($r_master[$col_order_id]) == 105714) || 
// 							(intval($r_master[$col_order_id]) == 105825) ||
// 							(intval($r_master[$col_order_id]) == 105980) ||
// 							(intval($r_master[$col_order_id]) == 106022) ||
// 							(intval($r_master[$col_order_id]) == 106031))
	        	)	        		
	        	{
	        		//All zipcodes should have 5 digits if numeric
	        		
	        		for($icol=$k; $icol < count($r_master) - 1; $icol+=2)
	        		{
	        			if($r_master[$icol] != '')
	        			{
		        			$iFound = 2;
		        			for($icol2 = $icol+2; $icol2 < count($r_master); $icol2+=2)
		        			{
		        				if($r_master[$icol] == $r_master[$icol2])
		        				{
		        					$r_master[$icol2] = $r_master[$icol2].$iFound;
		        					$iFound++;
		        				}
		        			}
	        			}
	        		}
	        		
	        		$r_master = array_pad($r_master, count($cols), '');
	        		 
	        		
					if(substr($r_master[3], 0, 3) == 'TR-')
					{						
						removeKey($r_master, 'Stamp 1 - Ink Color');
	        			removeKey($r_master, 'Stamp 2 - Ink Color');
	        			removeKey($r_master, 'Stamp 3 - Ink Color');

	        			$rs = array();
						for($iLine=0; $iLine<3; $iLine++)
						{
							$r = array_pad(array(), count($cols), '');
							for($iCol=0; $iCol<count($r_master); $iCol++)
							{
								$r[$iCol] = $r_master[$iCol]; 
							}
							
							if($r[$col_product_id] == 'TR-1001')
							{
								switch($iLine)
								{
								case 0:
									{										
										$r[$col_display_name] = 'WILLIAMS';									
										renameKey($r, 'Stamp 1 - Name', 'Name (max characters: 20)');
										renameKey($r, 'Stamp 1 - Address', 'Address Line 1 (max characters: 20)');
										renameKey($r, 'Stamp 1 - City/State/Zip', 'Address Line 2 (max characters: 20)');
										
										removeKey($r, 'Stamp 2 - Name');
										removeKey($r, 'Stamp 3 - Initials (left to right)');
										
										break;
									}									
								case 1:
									{
										$r[$col_display_name] = 'DELHAGEN';									
										renameKey($r, 'Stamp 2 - Name', 'Name (max characters: 20)');

										removeKey($r, 'Stamp 1 - Name');
										removeKey($r, 'Stamp 1 - Address');
										removeKey($r, 'Stamp 1 - City/State/Zip');										
										removeKey($r, 'Stamp 3 - Initials (left to right)');
										break;
									}									
								case 2:
									{										
										$r[$col_display_name] = 'SORENSEN';									
										renameKey($r, 'Stamp 3 - Initials (left to right)', 'Initials - left to right (max characters: 3)');
										
										removeKey($r, 'Stamp 1 - Name');
										removeKey($r, 'Stamp 1 - Address');
										removeKey($r, 'Stamp 1 - City/State/Zip');										
										removeKey($r, 'Stamp 2 - Name');
										break;
									}
								}
							}
							else if($r[$col_product_id] == 'TR-1002')
							{
								fixCorruptKeyValue($r, 'Stamp 1 - Month/Day');
								fixCorruptKeyValue($r, 'Stamp 1 - Name');
								fixCorruptKeyValue($r, 'Stamp 1 - Year');
												
								switch($iLine)
								{
									case 0:
									{
										$r[$col_display_name] = 'MORGAN';											
										
										renameKey($r, 'Stamp 1 - Month/Day', 'month and day');
										renameKey($r, 'Stamp 1 - Name', 'name');
										renameKey($r, 'Stamp 1 - Year', 'year');

										removeKey($r, 'Stamp 2 - Initial');
										removeKey($r, 'Stamp 2 - Name');
										
										removeKey($r, 'Stamp 3 - Name');
										break;
									}
									case 1:
									{
										$r[$col_display_name] = 'JACK';
										renameKey($r, 'Stamp 2 - Initial', 'Initial (max characters: 1)');
										renameKey($r, 'Stamp 2 - Name', 'Name (max characters: 20)');
										
										format_lineSeparatedJack(
											$r,
											'Stamp 2 - Address',
											'Address Line 1 (max characters: 20)',
											'Address Line 2 (max characters: 20)'
										);

										removeKey($r, 'Stamp 1 - Month/Day');
										removeKey($r, 'Stamp 1 - Name');
										removeKey($r, 'Stamp 1 - Year');
										
										removeKey($r, 'Stamp 3 - Name');
										break;												
									}
									case 2:
									{
										$r[$col_display_name] = 'GALANTE';
										renameKey($r, 'Stamp 3 - Initials (left to right)', 'Initials - left to right (max characters: 3)');

										removeKey($r, 'Stamp 1 - Month/Day');
										removeKey($r, 'Stamp 1 - Name');
										removeKey($r, 'Stamp 1 - Year');											
										
										removeKey($r, 'Stamp 2 - Initial');
										removeKey($r, 'Stamp 2 - Name');
										break;
									}
								}
							}
							else if($r[$col_product_id] == 'TR-1003')
							{
								switch($iLine)
								{
									case 0:
									{										
										$r[$col_display_name] = 'WOLFS';									
										renameKey($r, 'Stamp 1 - Name', 'Name (max characters: 20)');
										renameKey($r, 'Stamp 1 - Address', 'Address Line 1 (max characters: 20)');
										renameKey($r, 'Stamp 1 - City/State/Zip', 'Address Line 2 (max characters: 20)');
										
										removeKey($r, 'Stamp 2 - Name');
										removeKey($r, 'Stamp 3 - Name');
										break;
									}									
									case 1:
									{
										$r[$col_display_name] = 'CHEN';									
										renameKey($r, 'Stamp 2 - Name', 'Name (max characters: 20)');
										
										removeKey($r, 'Stamp 1 - Name');
										removeKey($r, 'Stamp 1 - Address');
										removeKey($r, 'Stamp 1 - City/State/Zip');
										
										removeKey($r, 'Stamp 3 - Name');
										break;
									}									
									case 2:
									{										
										$r[$col_display_name] = 'WILKENS';									
										renameKey($r, 'Stamp 3 - Name', 'Name (max characters: 20)');
										
										removeKey($r, 'Stamp 1 - Name');
										removeKey($r, 'Stamp 1 - Address');
										removeKey($r, 'Stamp 1 - City/State/Zip');
										
										removeKey($r, 'Stamp 2 - Name');
										break;
									}
								}
							}
							else if($r[$col_product_id] == 'TR-1008')
							{
								switch($iLine)
								{
									case 0:
									{
										$r[$col_display_name] = 'MULBERRY';
										
										$streetNumber = ' ';
										$streetName = ' ';
										
										$addressLine = findKeyValue($r, 'Stamp 1 - Address');
										if($addressLine > -1)
										{
											$value = trim($r[$addressLine]);
											$streetName = $value; 
											
											$numLen = strpos($value, ' ');
											if($numLen !== FALSE)
											{
												$streetNumber = substr($value, 0, $numLen);
												$streetName = trim(substr($value, $numLen));
											}
										}											
										
										addKeyValue($r, 'Address Line 1 (max characters: 20)', $streetNumber);
										addKeyValue($r, 'Address Line 2 (max characters: 20)', $streetName);
										
										format_cityStateZip(
											$r, 
											'Stamp 1 - City/State/Zip', 
											'Address Line 3 (max characters: 20)',
											'Address Line 3 (max characters: 20)2'
										);
										
										break;
									}
									case 1:
									{
										$r[$col_display_name] = 'OLIVIA';
										renameKey($r, 'Stamp 2 - Name', 'Name (max characters: 20)');
										break;
									}
									case 2:
									{
										$r[$col_display_name] = 'RICHARDS';											
										renameKey($r, 'Stamp 3 - Initial', 'Initial');											
										$initial = findKeyValue($r, 'Initial');
										if($initial > -1)
										{
											$r[$initial] = substr(trim(strtolower($r[$initial])), 0, 1);
										}
										break;
									}
								}
							}
								
								
							
							adjustRow($r);
								
							$rs[] = $r;
						}
					}
					else
					{
						adjustRow($r_master);
						$rs = array($r_master);
 					}

	        		
					for($iLine=0; $iLine<count($rs); $iLine++)
					{
						$r = $rs[$iLine];
						
						//Make that all missing inputs will be clear and not the default string
						for($iCol=1; $iCol<=29; $iCol+=2)
						{
							if($r[$k + $iCol] == '') $r[$k + $iCol] = ' ';
						}
						 
						
						
						$sql = 
							'INSERT INTO batch_queue('.
							'image_id, unique_id, order_id, order_line, '. 
							'product_id, display_name, '. 
							'p1_id, p1_value, '. 
							'p2_id, p2_value, '. 
							'p3_id, p3_value, '.
							'p4_id, p4_value, '.
							'p5_id, p5_value, '.
							'p6_id, p6_value, '.
							'p7_id, p7_value, '.
							'p8_id, p8_value, '.
							'p9_id, p9_value, '.
							'p10_id, p10_value, '.
							'p11_id, p11_value, '.
							'p12_id, p12_value, '.
							'p13_id, p13_value, '.
							'p14_id, p14_value, '.
							'p15_id, p15_value '.						
							') VALUES('.
							'-1,'.$r[0].','.$r[1].','.($iLine + 1).', '.
							'\''.mysql_real_escape_string($r[3]).'\',\''.mysql_real_escape_string($r[4]).'\', '. 
							
							'\''.mysql_real_escape_string($r[$k + 0]).'\',\''.mysql_real_escape_string($r[$k + 1]).'\', '. //1
							'\''.mysql_real_escape_string($r[$k + 2]).'\',\''.mysql_real_escape_string($r[$k + 3]).'\', '. //2
							'\''.mysql_real_escape_string($r[$k + 4]).'\',\''.mysql_real_escape_string($r[$k + 5]).'\', '. //3
							'\''.mysql_real_escape_string($r[$k + 6]).'\',\''.mysql_real_escape_string($r[$k + 7]).'\', '. //4
							'\''.mysql_real_escape_string($r[$k + 8]).'\',\''.mysql_real_escape_string($r[$k + 9]).'\', '. //5
							'\''.mysql_real_escape_string($r[$k + 10]).'\',\''.mysql_real_escape_string($r[$k + 11]).'\', '. //6
							'\''.mysql_real_escape_string($r[$k + 12]).'\',\''.mysql_real_escape_string($r[$k + 13]).'\', '. //7
							'\''.mysql_real_escape_string($r[$k + 14]).'\',\''.mysql_real_escape_string($r[$k + 15]).'\', '. //8
							'\''.mysql_real_escape_string($r[$k + 16]).'\',\''.mysql_real_escape_string($r[$k + 17]).'\', '. //9
							'\''.mysql_real_escape_string($r[$k + 18]).'\',\''.mysql_real_escape_string($r[$k + 19]).'\', '. //10
							'\''.mysql_real_escape_string($r[$k + 20]).'\',\''.mysql_real_escape_string($r[$k + 21]).'\', '. //11
							'\''.mysql_real_escape_string($r[$k + 22]).'\',\''.mysql_real_escape_string($r[$k + 23]).'\', '. //12
							'\''.mysql_real_escape_string($r[$k + 24]).'\',\''.mysql_real_escape_string($r[$k + 25]).'\', '. //13
							'\''.mysql_real_escape_string($r[$k + 26]).'\',\''.mysql_real_escape_string($r[$k + 27]).'\', '. //14
							'\''.mysql_real_escape_string($r[$k + 28]).'\',\''.mysql_real_escape_string($r[$k + 29]).'\''. //15
							')';
					
						$result = mysql_query($sql,$_system->db->connection);
						if(!$result)
						{
							echo 'SQL:<br>'.$sql.'<br>';
							echo mysql_error();
							return FALSE;
						}
					}
	        	}
	        }

        	$result = mysql_query('SELECT * FROM batch_queue WHERE product_id LIKE \'TR-%\'',$_system->db->connection);
        	if(!$result)
        	{
        		echo 'SQL:<br>'.$sql.'<br>';
        		echo mysql_error();
        		return FALSE;
        	}
	        	
	        fclose($handle);
	    }
	    return FALSE;
	}

	mysql_query("DELETE FROM batch_queue",$_system->db->connection);	    	
	
	//importData('Source data combined & parsed Feb 6.csv', 105616, TRUE);	
	//importData('couponsmason-130118-1-Bob.csv', 105616, FALSE);
	//importData('MMC MR BATCH 2 mason sales.csv', 105616, FALSE);	
	//importData('Run3 Orders export 130207 Parsed.csv', 105616, TRUE);
	//importData('Run4 20130214-bob-batch4-106043-106251.csv', 105616, TRUE);
	//importData('mason row 021813-1bob-1.csv', 0, TRUE);
	//importData('MR Site Batch1.csv', 0, TRUE);
	//importData('Coupons-masonrow-130225.csv', 0, TRUE);	
	importData('Mason Row Batch Run.csv', 0, TRUE);
	
	

	//$str = "Hello  World";
	//print_r (getTokens($str));
	//$a = explode(":", "aasdasdb");
	//echo '<br>'.count($a).' : '.$a[0].'<br>';
	echo '<br>done<br>';
?>