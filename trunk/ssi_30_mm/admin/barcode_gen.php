<?php
	include_once "_common.php";		
	
	$actionLog = "";
	
	$inpAlphabet	= "abcdef";
	$inpFormat		= "##-##-##";
	$inptNumBarcodes = 5;
	$inpProductId	= 0;
	$inpTemplateCategoryId	= -1;
	$inpTemplateId	= -1;
	
	if(!empty($_POST['inpGenerate']))
	{
		$inpAlphabet			= $_POST['inpAlphabet'];
		$inpFormat				= $_POST['inpFormat'];
		$inptNumBarcodes 		= $_POST['inptNumBarcodes'];
		$inpProductId			= $_POST['inpProductId'];
		$inpTemplateCategoryId	= $_POST['inpTemplateCategoryId'];
		$inpTemplateId			= $_POST['inpTemplateId'];
		
		
		if(empty($inpAlphabet)) $actionLog = $actionLog."Alphabet is a required field.<br />"; 
		if(empty($inpFormat)) $actionLog = $actionLog."Format is a required field.<br />";
		if(empty($inptNumBarcodes)) $actionLog = $actionLog."Number of barcodes is a required field.<br />";
		if(empty($inpProductId)) $actionLog = $actionLog."Product id is a required field.<br />";
		if(empty($inpTemplateCategoryId)) $actionLog = $actionLog."Template category id is a required field.<br />";
		if(empty($inpTemplateId)) $actionLog = $actionLog."Template id is a required field.<br />";
		
		if($actionLog == "")
		{
			$inptNumBarcodes		= intval($inptNumBarcodes);
			$inpProductId			= intval($inpProductId);
			$inpTemplateCategoryId	= intval($inpTemplateCategoryId);
			$inpTemplateId			= intval($inpTemplateId);
				
			$count = 0;
			$stopcount = 0;

			$len = strlen($inpAlphabet);
			$dateCreated = time();
			$raw = str_split($inpFormat);
			$rawchar = str_split($inpAlphabet);
			
			while ($count < $inptNumBarcodes) 
			{
				$newbarcode = "";
				foreach ($raw as $char) 
				{
					if ($char == '#')
						$char = $rawchar[mt_rand(0, $len-1)];
					$newbarcode .= $char;
				}
				
				$barcode = new Barcode();
				$barcode->barcode = $newbarcode;
				$barcode->productId = $inpProductId;
				$barcode->master = 'N';
				$barcode->dateCreated = $dateCreated;
				$barcode->templateCategoryId = $inpTemplateCategoryId;
				$barcode->templateId = $inpTemplateId;
				
				if ($_order_db->createBarcode($barcode))
				{
					$count++;
					$stopcount = 0;
				}
				else
				{
					$stopcount++;
					// if there is more than 1000 collisions then stop
					if ($stopcount > 50) break;
				}
			}
			if ($count < $inptNumBarcodes)
			{
				$actionLog = "Error: Aborted due to high collision rate.<br> Please change the barcode format / alphabet and try again.<br>";
			}
		}
	}	
	
	function writeBarcodeList()
	{
		global $_order_db;
		$barcodes = $_order_db->getBarcodeListByLastGenerated();
		$numBarcode = count($barcodes);
		echo "<br><br>Last Batch:-----------------------------------<br>";
		echo "total: ".$numBarcode ."<br><br>";
		
		for($i=0; $i<count($barcodes); $i++)
		{
			echo $barcodes[$i]->barcode . "<br>";
		}
	}
	
	include_once 'preamble.php';
?>
	<h1>Barcode Generator</h1>
	<h3><?php echo $actionLog; ?></h3>
	<br />
	<br />
	<form method='POST'>
		<table>
		<tr><td>Alphabet:</td><td><input name="inpAlphabet" type="text" value="<?php echo htmlspecialchars($inpAlphabet) ?>" placeholder="abcdef" ></td></tr>
		<tr><td>Format:</td><td><input name="inpFormat" type="text" value="<?php echo htmlspecialchars($inpFormat) ?>" placeholder="##-##-##"></td></tr>
		<tr><td>Number of Barcode:</td><td><input name="inptNumBarcodes" type="text" value="<?php echo htmlspecialchars($inptNumBarcodes) ?>" placeholder="100"></td></tr>
		<tr><td>Product Id:</td><td><input name="inpProductId" type="text" value="<?php echo htmlspecialchars($inpProductId) ?>" placeholder="3"></td></tr>
		<tr><td>Template Category Id:</td><td><input name="inpTemplateCategoryId" type="text" value="<?php echo htmlspecialchars($inpTemplateCategoryId) ?>" placeholder="3"></td></tr>
		<tr><td>Template Id:</td><td><input name="inpTemplateId" type="text" value="<?php echo htmlspecialchars($inpTemplateId) ?>" placeholder="0"></td></tr>		
		<tr><td></td><td><input type='submit' name='inpGenerate' value='generate'></td></tr>
		</table>
	</form>
<?php 
	writeBarcodeList();
	include_once 'postamble.php';
?>
