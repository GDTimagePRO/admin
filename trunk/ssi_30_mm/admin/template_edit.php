<?php
	include_once "_common.php";
	include_once "../backend/session.php";
	
	$actionLog = "";
	
	function writeEditor($template)
	{		
		global $_design_db;
		global $_order_db;
		
		$categoryList = $_design_db->getTemplateCategoryList();
		//$designList = $_design_db->getDefualtDesignTemplateList();
		
		//
		
		
		echo "<form method='POST'>";
		echo "<table>";
		echo "<tr><td>Image :</td><td>".htmlspecialchars($template->getPreviewImageId()).'<br>';
		echo '<img src="' . Settings::getImageUrl(ResourceManager::TYPE_THUMBNAIL.'.'.$template->getPreviewImageId(), true) . '">';
		echo "</td></tr><tr><td>id:</td><td>";
		echo $template->id;
		echo '<input name="id" type="hidden" value="'.$template->id.'">';
		echo '</td></tr><tr><td>name:</td><td>';
		echo '<input name="name" type="text" value="'.htmlspecialchars($template->name).'">';
		echo '</td></tr><tr><td>category:</td><td>';
		
		if (count($categoryList)) 
		{
			echo '<select name="categoryId">';
			foreach($categoryList as $categoryItem) {
				$selected = ($categoryItem['id'] == $template->categoryId) ? $selected="selected" : $selected="";
						
      			echo '<option value="' . $categoryItem['id'] . '"' . $selected . '>' . $categoryItem['id'] . ': ' . $categoryItem['customer_description'] . ' -> ' . $categoryItem['name'] . '</option>';
			}
		}
		echo '</select>';
		
		echo "</td></tr><tr><td>page type:</td><td>";
		echo '<select id="pageType">';
		echo '<option value="1">BOX</option>';
		echo '<option value="2">CIRCLE</option>';
		echo '</select>';
		
// 		echo "</td></tr><tr><td>product type:</td><td>";
// 		if (count($designList))
// 		{
// 			echo '<select name="productTypeId">';
// 			foreach($designList as $designItem) {
// 				$selected = ($designItem['product_type_id'] == $template->productTypeId) ? $selected="selected" : $selected="";		
// 				echo '<option value="' . $designItem['product_type_id'] . '"' . $selected . '>' . $designItem['product_type_id'] . ': ' . $designItem['description'] . '</option>';
// 			}
// 		}
// 		echo '</select>';

		
		/*
		echo '<input name="productTypeId" type="text" value="'.htmlspecialchars($template->productTypeId).'">';
		echo "</td></tr><tr><td>image:</td><td>";
		echo '<input name="previewImageId" type="hidden" value="'.$template->previewImageId.'">';
		echo $template->previewImageId;
		*/
		echo "</td></tr><tr><td>design JSON:</td><td>";
		echo '<input id="designJSON" name="designJSON" type="text" value="'.htmlspecialchars($template->designJSON).'">';
		
		echo "</td></tr><tr><td>config JSON:</td><td>";
		echo '<input name="configJSON" type="text" value="'.htmlspecialchars($template->configJSON).'">';
		
		
		echo "</td></tr>";
		echo "</table>";
		echo '<input name="action_save" type="submit" value="Save">';
		echo '<input type="submit" value="Refresh"><br>';

		if($template->id != -1)
		{
			echo '<input name="action_open" type="submit" value="Open"> as product ';
			echo '<select name="productId">';
			
			$selectedProduct = isset($_POST['productId']) ? $_POST['productId'] : 323;
			
			$productList = $_order_db->getProductList();
			foreach($productList as $productItem)
			{
				echo '<option value="'.$productItem['id'].'"';
				if($productItem['id'] == $selectedProduct)
				{
					echo 'selected';
				}
				echo '>'.htmlspecialchars($productItem['id']). ': '.htmlspecialchars($productItem['code']).'</option>';
			}
			echo '<select>';
		}		
		echo "</form>";
		
	}
	
	if(isset($_POST['id']))
	{
		$id = $_POST['id'];
	}
	else if(isset($_GET['id']))
	{
		$id = $_GET['id'];
		$actionLog = $actionLog."Loading template ".$id."<br>";
	}
	else
	{
		$id = -1;
	}
	
	if($id != -1)
	{
		$template = $_design_db->getDesignTemplateById($id);
	}
	else
	{
		$template = new DesignTemplate();
		$actionLog = $actionLog."Starting new template<br>";
	}
	
	
	$template->id = $id;
	
	
	if(isset($_POST['name'])) $template->name = $_POST['name']; 
	if(isset($_POST['categoryId'])) $template->categoryId = $_POST['categoryId'];
	if(isset($_POST['previewImageId'])) $template->previewImageId = $_POST['previewImageId'];
	if(isset($_POST['configJSON'])) $template->configJSON = $_POST['configJSON'];
	if(isset($_POST['designJSON'])) $template->designJSON = $_POST['designJSON'];
	if(isset($_POST['productTypeId'])) $template->productTypeId = $_POST['productTypeId'];

	
	function createTemplate($template)
	{
		global $_image_db;
		global $_design_db;
		global $actionLog;
		
		if($_design_db->createDesignTemplate($template))
		{
			$actionLog = $actionLog."Adding<br>";
		}
		else
		{
			$actionLog = $actionLog."Error Adding.<br>";
		}
	}
	
	function updateTemplate($template)
	{
		global $_design_db;
		global $actionLog;
		
		if($_design_db->updateDesignTemplate($template))
		{
			$actionLog = $actionLog."Saved<br>";
		}
		else
		{
			$actionLog = $actionLog."Error saving.<br>";
		}
	}
	
	
	if(isset($_POST['action_open']))
	{		
		$customer = $_order_db->getCustomerByKey(Customer::KEY_INTERNAL);

		$session = Session::create($customer);
		$session->customerId = $customer->id;
		$session->designEnvironment = DesignEnvironment::createFromTemplate($template->id, $session->sessionId, $_POST['productId']);
		$session->urlHome = "admin/template_edit.php?id=" . $template->id;
		$session->urlSubmit = "admin/template_edit.php?id=" . $template->id;
		$session->urlReturn = "admin/template_edit.php?id=" . $template->id;
		
		$templateCategory = $_design_db->getDesignTemplateCategoryById($template->categoryId);
		$session->customerId = $templateCategory->customerId;
		$session->save();

		Header("location: http://". Settings::HOME_URL . "design_customize.php?sid=" . $session->sessionId);
		exit();
	}
		
	else if(isset($_POST['action_save']))
	{
		if($template->id < 0)
		{
			createTemplate($template);
		}
		else
		{
			updateTemplate($template);
		}			
	}
		
	include_once 'preamble.php';
?>
<script type="text/javascript">
	$(function(){
		var o = jQuery.parseJSON($("#designJSON").val());		
		$("#pageType").val(o.pageParams.type);

		
		$("#pageType").change(function () {
			var o = jQuery.parseJSON($("#designJSON").val());
			if(!o)
			{
				alert("Error parsing template");
				return;
			}
			o.pageParams.type = parseInt($(this).find(":selected").val());
			$("#designJSON").val($.toJSON(o));
		});
	});
</script>
<h1>Template ( Edit )</h1>
<h3><?php echo $actionLog; ?></h3>
<?php writeEditor($template); ?>
<br><br>
<a href="template_edit.php?id=-1">New Template</a>
<?php include_once 'postamble.php';?>
