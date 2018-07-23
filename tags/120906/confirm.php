<?php
include_once "Backend/startup.php";
$startup = Startup::getInstance(".");
$db = $startup->db;
$s = $startup->session;
/*if($s->getUserId()!=""){
	if($s->getCurrentItem()==""){
		Header("location: http://".$startup->settings['url']."code.php");
	}
	else{
		Header("location: http://".$startup->settings['url']."design.php");
	}
}*/
include "preamble.php";	
?>
<div id="confirmpage" >
	<div id="blank" >          
        <h2>
           <span>Confirm Your Design</span>
       </h2>
	</div>
	
    <table id="submitted_box" width="700px" align="center" >
    	<tr>
    		<td colspan="2" border="1" id="previewImage">
    			<?php
    			$barcode = $db->getBarCode($s->getCurrentItem());
				$product_id = $db->getProductId($barcode);
				$product = $db->getProduct($product_id);
				$width = round($product['width']*0.0393700787 *90);
				?>
    			 <img src="getproductimage.php?id=<?php echo $s->getCurrentItem();?>" width ="<?php echo $width;?>" /> 
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<p align="left">
		           Please confirm your design. If you are satisfied, check the following declaration and click 'finish'. 
		           Your design will be scheduled for manufacturing and your item will be mailed to you. If you are not
		           satisfied with the design, click 'prev' and fine-tune your design.
		        </p>
	        </td>
        </tr>
        <tr>
            <td><input id="chechbox" type="checkbox" name="checkbox" /></td>
            <td>
            	<p align="left">
                I am satisfied with the design layout (ATTENTION: size on screen may vary from real size!).  
                I have verified that spelling and content are correct.  I understand that my design will be
                manufactured EXACTLY as it appears here and that I assume all responsibility for 
                typographical errors.
                </p>
            </td>
       </tr>
       <tr>
       	<td>
       		<a href="design.php" class="button" style="float: left;">previous</a>
       	</td>
       	<td>
       		<a href="submitted.php" class="button" style="float: right">finish</a>
       	</td>
       </tr>
    </table>  
    </div> 
	<?phP
	   include "postamble.php";
	?>
</body>
	
</html>
