<?php
	define('PHP_QPRINT_MAXL', 75);
	
	function php4_quoted_printable_encode($str)
	{
		$lp = 0;
		$ret = '';
		$hex = "0123456789ABCDEF";
		$length = strlen($str);
		$str_index = 0;
	
		while ($length--) {
			if ((($c = $str[$str_index++]) == "\015") && ($str[$str_index] == "\012") && $length > 0) {
				$ret .= "\015";
				$ret .= $str[$str_index++];
				$length--;
				$lp = 0;
			} else {
				if (ctype_cntrl($c)
						|| (ord($c) == 0x7f)
						|| (ord($c) & 0x80)
						|| ($c == '=')
						|| (($c == ' ') && ($str[$str_index] == "\015")))
				{
					if (($lp += 3) > PHP_QPRINT_MAXL)
					{
						$ret .= '=';
						$ret .= "\015";
						$ret .= "\012";
						$lp = 3;
					}
					$ret .= '=';
					$ret .= $hex[ord($c) >> 4];
					$ret .= $hex[ord($c) & 0xf];
				}
				else
				{
					if ((++$lp) > PHP_QPRINT_MAXL)
					{
						$ret .= '=';
						$ret .= "\015";
						$ret .= "\012";
						$lp = 1;
					}
					$ret .= $c;
				}
			}
		}
	
		return $ret;
	}	


	//function sendPrintableDesignImageEmail($imageId)
	function sendDesignImageEmail($orderItemId)
	{
		global $_image_db;
		global $_design_db;
		global $_order_db;
		
		
		$orderItem = $_order_db->getOrderItemById($orderItemId);
		$design = $_design_db->getDesignById($orderItem->designId);
		$product = $_order_db->getProductByOrderItemId($orderItem->id);
		
		
		$to = 'ValtchanV@GMail.com';
		$from = "Files attach <System@In-Stamp.com>";
		$subject = date("d.M H:i")." Image ID =".$design->imageId;
		
		$orderDate = date("F j, Y g:i a"); 
		$orderNumber = $orderItem->id;
		$orderLine = "1";
		$itemName = $product->code;
		$itemDescription = $product->longName;
		$itemColor = $design->getInkColor();

		if($product->frameWidth < $product->width) $product->frameWidth = $product->width; 
		if($product->frameHeight < $product->height) $product->frameHeight = $product->height;
		
		//$imageStyle = "width:".$product->frameWidth."mm;height:".$product->frameHeight."mm;border-style:solid;border-width:1px;";
		$imageStyle = "";				
		$imageSizeScale = 3.7795296;// inch_per_mm * 96 DPI		
		$imageWidth = round($product->frameWidth * $imageSizeScale); 
		$imageHeight = round($product->frameHeight * $imageSizeScale);
		
		ob_start();	
		
		?>
		<p><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">Thank you for placing your order with IN-STAMP, Your order is now being processed<u></u><u></u></span></p>		
		<p style="margin-bottom:6.0pt"><b><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">IN-STAMP Information<u></u><u></u></span></b></p>
		<table border="0" cellpadding="0">
		<tbody>
		<tr>
			<td style="padding:.75pt .75pt .75pt .75pt">
		  		<p class="MsoNormal"><b><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">Phone:<u></u><u></u></span></b></p>
			</td>
			<td style="padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><a href="tel:1-800-668-2724" value="+18006682724" target="_blank">1-800-668-2724</a><u></u><u></u></span></p>
			</td>
			<td style="padding:.75pt .75pt .75pt .75pt"></td>
			<td style="padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal"><b><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">Fax<u></u><u></u></span></b></p>
			</td>
			<td style="padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><a href="tel:1-800-565-0941" value="+18005650941" target="_blank">1-800-565-0941</a><u></u><u></u></span></p>
			</td>
			<td style="padding:.75pt .75pt .75pt .75pt"></td>
			<td style="padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal"><b><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">E-Mail:<u></u><u></u></span></b></p>
			</td>
			<td style="padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><a href="mailto:Bob@in-stamp.com" target="_blank">Bob@in-stamp.com</a><u></u><u></u></span></p>
			</td>
		</tr>
		</tbody>
		</table>
		
		<p style="margin-bottom:6.0pt"><b><span style="font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">Order Date: <?php echo $orderDate; ?><u></u><u></u></span></b></p>
		
		<table border="1" cellpadding="0" width="100%" style="width:100.0%;background:cornsilk;border:groove bisque 3.75pt">
		<thead>
		<tr>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><b><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">Order Number<u></u><u></u></span></b></p>
			</td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><b><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">Order Line #<u></u><u></u></span></b></p>
			</td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><b><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">Item Name<u></u><u></u></span></b></p>
			</td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><b><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">Desciption<u></u><u></u></span></b></p>
			</td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><b><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">Colour<u></u><u></u></span></b></p>
			</td>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><?php echo $orderNumber; ?><u></u><u></u></span></p>
			</td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><?php echo $orderLine; ?><u></u><u></u></span></p>
			</td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><?php echo $itemName; ?><u></u><u></u></span></p>
			</td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><?php echo $itemDescription; ?><u></u><u></u></span></p>
		  	</td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><?php echo $itemColor; ?><u></u><u></u></span></p>
			</td>
		</tr>
		</tbody></table>
		
		<p class="MsoNormal"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><u></u>&nbsp;<u></u></span></p>
		
		<table border="1" cellpadding="0" width="100%" style="width:100.0%;border:none;border-top:dotted windowtext 3.0pt">
		<tbody>
		<tr>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">THIS<u></u><u></u></span></p>
			</td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt"></td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">THIS<u></u><u></u></span></p>
			</td>
		</tr>
		<tr>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">SIDE<u></u><u></u></span></p>
			</td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt" align="center">
				<p class="MsoNormal" align="center" style="text-align:center"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;"><img width="<?php echo $imageWidth; ?>" height="<?php echo $imageHeight; ?>" style="<?php echo $imageStyle; ?>" src="cid:design_01"><u></u><u></u></span></p>				
			</td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">SIDE<u></u><u></u></span></p>
			</td>
		</tr>
		<tr>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">UP<u></u><u></u></span></p>
			</td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt"></td>
			<td style="border:none;padding:.75pt .75pt .75pt .75pt">
				<p class="MsoNormal" align="center" style="text-align:center"><span style="font-size:11.0pt;font-family:&quot;Arial&quot;,&quot;sans-serif&quot;">UP<u></u><u></u></span></p>
			</td>
		</tr>
		</tbody></table>		
		<?php		
		
		$messageBody = ob_get_contents();
		ob_end_clean();
		
		$semi_rand = md5(date('r', time()));
		$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
 
 
		$headers = 
			"From: System@In-Stamp.com\n" .
			"MIME-Version: 1.0\n".
			"Content-Type: multipart/mixed; boundary=\"{$mime_boundary}\"";
					
		$message = 
			"--{$mime_boundary}\n" . 
			"Content-Type: text/html; charset=\"iso-8859-1\"\n" .
			"Content-Transfer-Encoding: quoted-printable\n\n" . 
			//quoted_printable_encode($messageBody) .
			php4_quoted_printable_encode($messageBody) .
			"\n\n" ;
			
		$imageData = $_image_db->getImageById($design->imageId)->data;		
		$imageData = imagecreatefromstring($imageData);
		ob_start();
		//imagejpeg($imageData);
		imagepng($imageData);
		$imageData = ob_get_contents();
		ob_end_clean();
		//$imageData = substr_replace($imageData, pack("cnn", 1, 600, 600), 13, 5);
		
		$message .= 
			"--{$mime_boundary}\n" .
			//"Content-Type: image/jpeg; name=\"design.jpg\"\n" .
			"Content-Type: image/png; name=\"design.png\"\n" .
			"Content-Transfer-Encoding: base64\n" .
            "Content-ID: <design_01>\n" .
            //"Content-Disposition: inline; filename=\"design.jpg\"; size=".mb_strlen($imageData).";\n" .
			//"Content-Description: \"design.jpg\"\n" .
			"\n" . 			
			chunk_split(base64_encode($imageData)). 			
			"\n\n" . 
			"--{$mime_boundary}--";
					
    	mail('bobloucks2009@gmail.com', $subject, $message, $headers);
    	return mail($to, $subject, $message, $headers); 
			
		//mail('ValtchanV@GMail.com', 'Image Sent', 'Image id : ' + $imageId, 'From: System@In-Stamp.com');
	}
?>