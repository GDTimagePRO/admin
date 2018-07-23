<?php
global $layout,$width,$height,$marginx,$marginy,$rowMax,$current;

function interpretContent($content){
	global $current,$width,$height,$marginx,$marginy,$rowMax,$layout;
	foreach($content as $c){
		//echo $c->type."<br>";
		switch($c->type){
			case "text":
				addText(clone $c);
				break;
			case "repeaty":
				$repeat = $c->value->repeat;
				for($i=0;$i<$repeat;$i++){
					interpretContent($c->value->content);
					$current->y+= $rowMax;
					$current->x = $marginx;
					if($current->imageCount > $current->maxImages){
						break;
					}
				}

				break;
			case "repeatx":
				$repeat = $c->value->repeat;
				for($i=0;$i<$repeat;$i++){
					interpretContent($c->value->content);
					if($current->imageCount > $current->maxImages){
						break;
					}
				}
				break;
			case "spacex":
				$current->x+=$c->value;
				if($current->x >=$width){
					$current->x = $marginx;
				}
					
				break;
			case "spacey":
				$current->y+=$c->value;
					
				break;
			case "image":
				addImage(clone $c);
				break;
			case "page":
				$layout[] = clone $c;
				break;

		}
		if($current->imageCount > $current->maxImages){
			break;
		}
	}
}
/**
 *
 * * @param Object $text this contains the information for the text line
 *
 */
function addText($text){
	global $layout,$current;
	$c = clone $current;
	if(!isset($text->x)){
		$text->x = $c->x;
	}
	if(!isset($text->y)){
		$text->y = $c->y;
	}
	else if($text->y < 0){
		$text->y = $c->y+(-1)*$text->y;
	}
	$text->current = $c->imageCount-1;
	$layout[] = $text;
}

function addImage($image){
	global $current,$layout,$width,$height,$marginx,$marginy;


	$a = array();
	$c = clone $current;
	$image->value = $c->imageCount;
	if(!isset($image->x)){
		$image->x = $c->x;
	}
	if(!isset($image->y)){
		$image->y = $c->y;
	}
	//echo "Adding image $current->imageCount at ($current->x,$current->y)<br>";
	$current->x+= 41;
	$current->imageCount++;
	$layout[] = $image;
}

$template = array();
$template['orientation'] = "L";
$template['width'] = 215.9;
$template['height'] = 279.4;
$template['marginx'] = 2;
$template['marginy'] = 2;
$template['maxPages'] = 1;
$template['maxImages'] = 30;
$template['cutLines'] = true;
$template['content'] = array();
$template['content'][0]['type'] = "repeaty";
$template['content'][0]['value']['repeat'] = 5;
$template['content'][0]['value']['content'][0]['type'] = 'repeatx';
$template['content'][0]['value']['content'][0]['value']['repeat'] = 6;
$template['content'][0]['value']['content'][0]['value']['content'][0]['type'] = 'image';
$template['content'][0]['value']['content'][0]['value']['content'][1]['type'] = 'spacex';
$template['content'][0]['value']['content'][0]['value']['content'][1]['value'] = 1;
$template['content'][0]['value']['content'][1]['type'] = 'spacey';
$template['content'][0]['value']['content'][1]['value'] = 2;
$json = json_encode($template);

echo $json.'<br><br><br>';

$struct = json_decode($json);

//echo $blah->content[1]->value->content[0]->value->content[0]->type;

$width = $struct->width;
$height = $struct->height;
$rowMax = 41; //TO-DO: this needs to be image dependent. (i.e. need to get it as I go to find correct value).
$marginx = $struct->marginx;
$marginy = $struct->marginy;
$maxImages = $struct->maxImages;
$current->x = $marginx;
$current->y = $marginy;
$current->imageCount = 0;
$current->maxImages = $maxImages;
$content = $struct->content;
$layout = array();
$count = 0;
interpretContent($content);
//var_dump($layout);
$json2 = json_encode($layout);
echo $json2;
?>