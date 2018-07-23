var numLines = 0;
var currentZoomLevel = 100;
var gridOn = false;
var INTERVAL = 20; //how often we check for a re-draw
var SAVE_INTERVAL = 1000; //how often we check if we need to save
var WIDTH; 		   //the width and height of the canvas.
var HEIGHT;
var browserName=navigator.appName;  //necessary to take IE into account.

//need to keep track of all of the textLines to be drawn
var textLines = [];
var textColor = "black";
//the logo to be put on the stamp
var logo = null;

//this is the canvas that represents the stamp
var canvas;
//this is the context (drawing object) for the stamp
var context;
//this is a "ghost canvas" (will be drawn to on the side in order to find where the user has clicked)
var ghostcanvas;
//this is the context for the ghost canvas
var gcxt;

//whether or not we need to re-draw the canvas
var isValid = false;

//whether we need to save or not
var toSave = false;

//co-ordinates of the mouse.
var mx,my;

//need to know where inside the object they clicked
var offsetx,offsety;

//need to know what I'm currently doing.
var isDrag = false;
var isResizeDrag = false;
var expectResize = -1; 

//the selected object
var mySel;

// holds the 4 tiny boxes that will be our selection handles
// the selection handles will be in this order:
// 0     1
//        
// 2     3
var selectionHandles = [];

//selection box color and size
var mySelBoxColor = 'darkred'; 
var mySelBoxSize = 6;


// Padding and border style widths for mouse offsets
var stylePaddingLeft, stylePaddingTop, styleBorderLeft, styleBorderTop;

//the object that each text line is stored in.
TextLine = function(){
	this.id = -1;
	this.text = "";
	this.fontsize = 6;
	this.fontfamily = "Arial";
	this.bold = 0;
	this.italic = 0;
	this.underline = 0;
	this.x = 0;
	this.y = 0;
	this.align = "left";
	this.show = true;
	this.type = "Straight";
	this.toJson = function(){
		var s = '{"id": '+this.id+', "text": "'+this.text+'", "fontsize": '+this.fontsize+', "font": "'+this.fontfamily+'", "x": '+this.x+', "y": '+this.y+', "bold": '+this.bold+', "italic": '+this.italic+',"underline": '+this.underline+'}';
		return s;
	}
}

//the object that represents the logo

Logo = function(){
	this.image;
	this.x = 0;
	this.y = 0;
	this.width = 100;
	this.height = 100;

}

//these are used for resizing boxes and for bounding boxes.
Box = function(){
	this.x = 0;
	this.y = 0;
	this.width = mySelBoxSize;
	this.height = mySelBoxSize;

}

//draws the text line to the canvas
drawTextLine = function(textLine){
	if(textLine.show){
		context.fillStyle = textColor;
		var fontstyle = "";
		if(textLine.bold==1){
			fontstyle+="bold ";
		}
		if(textLine.italic==1){
			fontstyle+="italic ";
		}
		if(textLine.underline==1){
			fontstyle+="underline";
		}
		context.font = fontstyle+" "+textLine.fontsize+"pt "+textLine.fontfamily;
		context.textAlign = textLine.align;
		context.fillText(textLine.text,textLine.x,textLine.y);
	}
	
}

//returns a Box object representing a bounding box for a textLine
getBoundingBox = function(textLine){
	context.font = textLine.fontstyle+" "+textLine.fontsize+"pt "+textLine.fontfamily;
	var metric = context.measureText(textLine.text);
	metric.height = textLine.fontsize*1.2;
	var box = new Box;
	box.x = textLine.x;
	box.y = textLine.y-metric.height;
	box.width = metric.width;
	box.height = metric.height;
	return box;
}


//draws a box around where a text line would be. This is used to determine if the user has clicked on a text line.
drawBoundingBox = function(textLine,context){
	context.font = textLine.fontstyle+" "+textLine.fontsize+"pt "+textLine.fontfamily;
	var metric = context.measureText(textLine.text);
	metric.height = textLine.fontsize*1.2;
	context.fillStyle = "#8ED6FF";
	context.beginPath();
	var x = textLine.x;
	if(textLine.align == "center"){
		x -= metric.width/2;
	}
	var y = textLine.y-metric.height;
	var width = metric.width;
	var height = metric.height;
	//console.log('Box Dimensions: ('+x+','+y+'),('+(x+width)+','+(y+height)+')');
	context.rect(x,y,width,height);
	context.fill();

}

clear = function(context){
	context.clearRect(0,0,WIDTH,HEIGHT);

}
//draws a faint outline around the edges of the stamp. 
drawOutline = function(){
	context.strokeStyle = "#000000";
	/*context.moveTo(1,1);
	context.lineTo(canvas.width-1,1);
	context.stroke();
	context.lineTo(canvas.width-1,canvas.height-1);
	context.stroke();
	context.lineTo(1,canvas.height-1);
	context.stroke();
	context.lineTo(1,1);
	context.stroke();*/
	context.strokeRect(1,1,WIDTH-2,HEIGHT-2);

}

//draws the selection handles
drawSelectionHandles = function(context){
	context.fillStyle = mySelBoxColor;
	for(var i = 0;i<4;i++){
		context.fillRect(selectionHandles[i].x,selectionHandles[i].y,mySelBoxSize,mySelBoxSize);
	}

}

//draws a logo on the stamp.

drawLogo = function(context){
	/*if(logo.image){
		context.drawImage(logo.image, logo.x, logo.y, logo.width, logo.height);
		//if the logo is selected, then draw the resize boxes around it.
		if(mySel == logo){
			context.strokeStyle = mySelBoxColor;
			context.strokeRect(logo.x,logo.y,logo.width,logo.height);
			var half = mySelBoxSize / 2;
			selectionHandles[0].x = logo.x-half;
			selectionHandles[0].y = logo.y-half;
			selectionHandles[1].x = logo.x+logo.width-half;
			selectionHandles[1].y = logo.y-half;
			selectionHandles[2].x = logo.x-half;
			selectionHandles[2].y = logo.y+logo.height-half;
			selectionHandles[3].x = logo.x+logo.width-half;
			selectionHandles[3].y = logo.y+logo.height-half;
			drawSelectionHandles(context);
		}
	}*/

}

function drawGrid(){
	context.strokeStyle = "#e8e8e8";
	for(var i=0;i<canvas.width;i+=10){
		//context.beginPath();
		context.moveTo(i,0);
		context.lineTo(i,canvas.height);
		context.stroke();
	}
	for(var i=0;i<canvas.height;i+=10){
		context.moveTo(0,i);
		context.lineTo(canvas.width,i);
		context.stroke();
	}
	
}

//this is the drawing loop function.
draw = function(){
	if(isValid == false){
		clear(context);
		if(gridOn){
			drawGrid();
		}
		if(logo!=null){
			drawLogo(context);
		}
		for(var i=1;i<textLines.length;i++){
			drawTextLine(textLines[i]);
			//drawBoundingBox(textLines[i],context);
		}
		//drawOutline();
		//This is just a test of a bezier curve
		/*context.strokeStyle = textColor;
		context.moveTo(0,canvas.height/2);
		context.bezierCurveTo(0,0,canvas.width,0,canvas.width,canvas.height/2);
		context.stroke();*/
		
		isValid = true;
	}

}

getMouse = function(e){
   var element = canvas;
   var offsetX = 0,offsetY = 0;
   if (element.offsetParent !== undefined) {
    do {
      offsetX += element.offsetLeft;
      offsetY += element.offsetTop;
    } while ((element = element.offsetParent));
  }
 //alert("OffsetX: "+offsetX+" OffsetY: "+offsetY);
 
  // Add padding and border style widths to offset
  // Also add the <html> offsets in case there's a position:fixed bar
  //alert("stylepaddingLeft: "+this.stylePaddingLeft+" styleborderLeft: "+this.styleBorderLeft+" htmlLeft: "+this.htmlLeft);
  //alert("stylepaddingLeft: "+this.stylePaddingTop+" styleborderLeft: "+this.styleBorderTop+" htmlLeft: "+this.htmlTop);
  /*offsetX += this.stylePaddingLeft + this.styleBorderLeft;
  offsetY += this.stylePaddingTop + this.styleBorderTop;
  if(this.htmlLeft!== undefined){
  	offsetX+= this.htmlLeft;
  	offsetY+= this.htmlTop;
  }*/
 //alert("OffsetX: "+offsetX+" OffsetY: "+offsetY);
  mx = e.clientX - offsetX;
  my = e.clientY - offsetY;
  mx /= (currentZoomLevel/100);
  my /= (currentZoomLevel/100);
}

// Happens when the mouse is moving inside the canvas
function myMove(e){
  if (isDrag){
    getMouse(e);
 
    mySel.x = mx - offsetx;
    mySel.y = my - offsety;   
	// something is changing position so we better invalidate the canvas!
    invalidate();
  } else if (isResizeDrag) {
		// time to resize!
		//getMouse(e);
		var oldx = mySel.x;
		var oldy = mySel.y;
	 
		switch (expectResize) {
		  case 0:
			mySel.x = mx;
			mySel.y = my;
			mySel.width += oldx - mx;
			mySel.height += oldy - my;
			break;
		  case 1:
			mySel.y = my;
			mySel.width = mx - oldx;
			mySel.height += oldy - my;
			break;
		  case 2:
			mySel.x = mx;
			mySel.width += oldx - mx;
			mySel.height = my - oldy;
			break;
		  case 3:
			mySel.width = mx - oldx;
			mySel.height = my - oldy;
			break;
		}
		// something is changing position so we better invalidate the canvas!
		invalidate();
	}
	
	 getMouse(e);
  // if there's a selection see if we grabbed one of the selection handles
  /*if (mySel !== null && !isResizeDrag) {
    for (var i = 0; i < 4; i++) {
      // 0     1
      //       
	  // 2     3
      var cur = selectionHandles[i];
      
      // we dont need to use the ghost context because
      // selection handles will always be rectangles
      if (mx >= cur.x && mx <= cur.x + mySelBoxSize &&
          my >= cur.y && my <= cur.y + mySelBoxSize) {
        // we found one!
        expectResize = i;
        invalidate();
        
        switch (i) {
          case 0:
            this.style.cursor='nw-resize';
            break;
          case 1:
            this.style.cursor='ne-resize';
            break;
          case 2:
            this.style.cursor='sw-resize';
            break;
          case 3:
            this.style.cursor='se-resize';
            break;
        }
        return;
      }
      
    }
	 // not over a selection box, return to normal
    isResizeDrag = false;
    expectResize = -1;
    this.style.cursor='auto';
	}*/
   
}
 
function myUp(){
  isDrag = false;
  isResizeDrag = false;
  expectResize = -1;
}

function myDown(e){
  getMouse(e);
  //alert(mx+"  "+my);
  //we are over a selection box
  if (expectResize !== -1) {
    isResizeDrag = true;
    return;
  }
  //alert("got mouse: "+mx+" "+my);
  //console.log("Got Mouse: ("+mx+","+my+")");
  clear(gcxt); // clear the ghost canvas from its last use
 
  // run through all the text lines
  var l = textLines.length;
  for (var i = l-1; i >= 1; i--) {
	//alert("drawingBoundingBox for "+i);
    
 
    /************************************************
	We can't use getImageData if the user is using 
	Internet Explorer (because it's retarded), so we 
	have to do a seperate check for IE
	************************************************/
	if(browserName!="Microsoft Internet Explorer"){
		// draw shape onto ghost context
		//alert("Drawing bounding box");
		//drawBoundingBox(textLines[i],context);
		// get image data at the mouse x,y pixel
		//var imageData = gcxt.getImageData(mx, my, 1, 1);
		//var index = (mx + my * imageData.width) * 4;
	 
		var box = getBoundingBox(textLines[i]);
		//console.log("Testing line click: ("+mx+","+my+") in ("+box.x+","+box.y+","+(box.x+box.width)+","+(box.y+box.height)+")");
		if((mx>box.x&&mx<box.x+box.width)&&(my>box.y&&my<box.y+box.height)){
		  mySel = textLines[i];
		  offsetx = mx - mySel.x;
		  offsety = my - mySel.y;
		  /*mySel.x = mx - offsetx;
		  mySel.y = my - offsety;*/
		  //console.log("Offset: ("+offsetx+","+offsety+")");
		  isDrag = true;
		  //alert("Selected "+i);
		  invalidate();
		  clear(gcxt);
		  return;
		}
	}
	else{
		/*need to declare a bounding box for the textLine
		and determine whether or not the mouse was clicked
		inside of it
		*/
		var box = getBoundingBox(textLines[i]);
		if((mx>box.x&&mx<box.x+box.width)&&(my>box.y&&my<box.y+box.height)){
		  mySel = textLines[i];
		  offsetx = mx - mySel.x;
		  offsety = my - mySel.y;
		  mySel.x = mx - offsetx;
		  mySel.y = my - offsety;
		  isDrag = true;
		  //alert("Selected "+i);
		  invalidate();
		  return;
		
		}
	
	}
 
  }
  //check to see if they have selected the logo
  clear(gcxt);
  /************************************************
	We can't use getImageData if they user is using 
	Internet Explorer (because it's retarded), so we 
	have to do a seperate check for IE
	************************************************/
	if(browserName!="Microsoft Internet Explorer"){
	  drawLogo(gcxt);
	  var imageData = gcxt.getImageData(mx, my, 1, 1);
	  if(imageData.data[3] > 0){
		mySel = logo;
		offsetx = mx - mySel.x;
		offsety = my - mySel.y;
		mySel.x = mx - offsetx;
		mySel.y = my - offsety;
		isDrag = true;
		invalidate();
		clear(gcxt);
		return;
	  }
	}
	else{
		/*
		We don't need to declare a bounding box for the
		logo as we did for the textlines since the logo
		already knows it's bounding box.
		*/
		if((mx>logo.x&&mx<logo.x+logo.width)&&(my>logo.y&&my<logo.y+logo.height)){
			mySel = logo;
			offsetx = mx - mySel.x;
			offsety = my - mySel.y;
			mySel.x = mx - offsetx;
			mySel.y = my - offsety;
			isDrag = true;
			invalidate();
			clear(gcxt);
			return;
		}
		
		
	
	}
  // havent returned means we have selected nothing
  mySel = null;
  // clear the ghost canvas for next time
  clear(gcxt);
  // invalidate because we might need the selection border to disappear
  invalidate();
}

invalidate = function(){
	isValid = false;
	setSave(true);
}

init = function(){
	//get the canvas from the document.
	canvas = document.getElementById("surface");
	WIDTH = canvas.width;
	HEIGHT = canvas.height;
	//get the context from the canvas (which we need to draw to)
	context = canvas.getContext("2d");
	
	//create the ghost canvas and context
	ghostcanvas = document.createElement('canvas');
	ghostcanvas.width = WIDTH;
	ghostcanvas.height = HEIGHT;
	/*if(browserName == "Microsoft Internet Explorer"){
		G_vmlCanvasManager.initElement(ghostcanvas);
	}*/
	gcxt = ghostcanvas.getContext("2d");
	
	
	/**
	 * Load in the textlines from the database if there is any.
	 */
	
	var s = $('#textlinesdb').html();
	var object = jQuery.parseJSON(s);
	for(var i=0;i<object.textlines.length;i++){
		numLines++;
		var line = object.textlines[i]; 
		textLines[numLines] = new TextLine();
		textLines[numLines].id = line.id;
		textLines[numLines].text = line.text;
		textLines[numLines].x = line.x;
		textLines[numLines].y = line.y;
		textLines[numLines].fontfamily = line.font;
		textLines[numLines].fontsize = line.fontsize;
		textLines[numLines].bold = line.bold;
		textLines[numLines].italic = line.italic;
		textLines[numLines].underline = line.underline;
		addTextLine(numLines);
	}
	
	/*logo = new Logo();
	var image = new Image();
	image.onload = function(){
		logo.image = this;
		logo.x = 10;
		logo.y = 10;
		invalidate();
	}
	image.src = "logo.png";
	  for (var i = 0; i < 4; i ++) {
		var rect = new Box;
		selectionHandles.push(rect);
	  }
	*/  
	 // fixes mouse co-ordinate problems when there's a border or padding
	  // see getMouse for more detail
	  if (document.defaultView && document.defaultView.getComputedStyle) {
		stylePaddingLeft = parseInt(document.defaultView.getComputedStyle(canvas, null)['paddingLeft'], 10)      || 0;
		stylePaddingTop  = parseInt(document.defaultView.getComputedStyle(canvas, null)['paddingTop'], 10)       || 0;
		styleBorderLeft  = parseInt(document.defaultView.getComputedStyle(canvas, null)['borderLeftWidth'], 10)  || 0;
		styleBorderTop   = parseInt(document.defaultView.getComputedStyle(canvas, null)['borderTopWidth'], 10)   || 0;
	  }
	
	//draw the textLines to the stamp
	invalidate();
	draw();
	
	//set up the interval for re-draw
	setInterval(draw, INTERVAL);
	
	//set up the interval for save
	setInterval(saveDesign,SAVE_INTERVAL);
	
	//add the events that will be taking care of the mouse clicks. 
	canvas.onmousedown = myDown;
	canvas.onmouseup = myUp;
	canvas.onmousemove = myMove;
	
	
	//draw a faint border around the whole stamp (mostly here for testing purposes).
	
}

window.onload = function(){
	init();
};

$(function(){


	$("#zoom").slider({
		max: 500,
		min: 10,
		value: 100,
		slide: function(event, ui) {
			zoom(ui.value);
		}
	});

});

function zoom(level){
	currentZoomLevel = level;
	var currentValue = $('#currentzoom');
	currentValue.value(level+"%");
	
	var width = 242*(level/100);
	var height = 107*(level/100);
	
	var surface = $("#surface");
	
	surface.width(width);
	surface.height(height);
}

function changeTextLine(lineNum,element){
	//alert(lineNum);
	textLines[lineNum].text = element.value;
	invalidate();
}

function changeTextLineFontSize(lineNum,element){
	textLines[lineNum].fontsize = element.value;
	invalidate();
	
}

function changeTextLineFont(lineNum,element){
	//alert("Change font: "+lineNum+" to "+element.value);
	textLines[lineNum].fontfamily = element.value;
	invalidate();
}

function changeTextLineFontStyle(lineNum,element,style){
	if(style=="bold"){
		if(element.checked == true){
			textLines[lineNum].bold=1;	
		}
		else{
			textLines[lineNum].bold = 0;
		}
		
	}
	if(style=="italic"){
		if(element.checked == true){
			textLines[lineNum].italic=1;	
		}
		else{
			textLines[lineNum].italic = 0;
		}
		
	}	
	if(style=="underline"){
		if(element.checked == true){
			textLines[lineNum].underline=1;	
		}
		else{
			textLines[lineNum].underline = 0;
		}
		
	}
	invalidate();
}

function addTextLine(index){
	var font = textLines[index].fontfamily;
	var s = "<option>"+font;
	var rep = "<option selected>"+font;
	var fonts = $('#fontselect').html().replace("#LINENUM",""+index);
	fonts = fonts.replace(s,rep);
	fontsize = textLines[index].fontsize;
	s = "<option>"+fontsize;
	rep = "<option selected>"+fontsize;
	var fontsizes = $('#fontsizeselect').html().replace("#LINENUM",""+index);
	fontsizes = fontsizes.replace(s,rep);
	var line = '<tr><td width="5%" align="center">'+index+'</td>';
	line+='<td width="10%" align="center"><select><option selected>Straight</option><option>Circular</option></select></td>';
	//line+='<td width="10%" align="center"><input type="text" value="100" size="5"></td>';
	line+='<td width="40%"><input type="text" size="100" onKeyUp="changeTextLine('+index+',this)" value="'+textLines[index].text+'"></td>';
	line+='<td width="10%" align="center">'+fonts+'</td>';
	line+='<td width="5%" align="center">'+fontsizes+'</td>';
	line+='<td width="5%" align="center"><input type="checkbox" onChange="changeTextLineFontStyle('+index+',this,\'bold\')"';
	if(textLines[index].bold == 1){
		line += ' checked';
	}
	line +='></td><td width="5%" align="center"><input type="checkbox" onChange="changeTextLineFontStyle('+index+',this,\'italic\')"';
	if(textLines[index].italic == 1){
		line+=' checked';
	}
	line+='></td><td width="15%" align="center"><div class="button" onClick="deleteTextLine('+index+')">Delete</div></td></tr>';
	
	$('#textlineTable > tbody:last').append(line);
}

function newTextLine(){
	numLines++;
	var fonts = $('#fontselect').html().replace("#LINENUM",""+numLines);;
	var fontsizes = $('#fontsizeselect').html().replace("#LINENUM",""+numLines);
	var line = '<tr><td width="5%" align="center">'+numLines+'</td>';
	line+= '<td width="10%" align="center"><select><option selected>Straight</option><option>Circular</option></select></td>';
	//line+= '<td width="10%" align="center"><input type="text" value="100" size="5"></td>';
	line+= '<td width="40%"><input type="text" size="100" onKeyUp="changeTextLine('+numLines+',this)" onChange="setSave(true)"></td>';
	line+= '<td width="10%" align="center">'+fonts+'</td>	<td width="5%" align="center">'+fontsizes+'</td>';
	line+= '<td width="5%" align="center"><input type="checkbox" onChange="changeTextLineFontStyle('+numLines+',this,\'bold\')"></td>';
	line+= '<td width="5%" align="center"><input type="checkbox" onChange="changeTextLineFontStyle('+numLines+',this,\'italic\')"></td>';
	line+= '<td width="15%" align="center"><div class="button" onClick="deleteTextLine('+numLines+')">Delete</div></td></tr>';
	
	$('#textlineTable > tbody:last').append(line);
	textLines[numLines] = new TextLine();
	textLines[numLines].x = 85;
	textLines[numLines].y = 26;
	
	
	//add the new textline to the database and retrieve the id
	var xmlhttp;
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  	xmlhttp=new XMLHttpRequest();
	}
	else
	  {// code for IE6, IE5
	  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	  
	xmlhttp.open("GET","newtextline.php?order_id="+$('#orderitem_id').html(),false);
	xmlhttp.send();
	textLines[numLines].id = xmlhttp.responseText;
	
}

function deleteTextLine(index){
	var line_id = textLines[index].id;
	textLines[index].show = false;
	//var oldLines = textLines;
	//textLines = [];
	/*var newLines = [];
	for(var i=1;i<textLines.length;i++){
		if(i!=index){
			var line = textLines[i];
			newLines[numLines] = new TextLine();
			newLines[numLines].id = line.id;
			newLines[numLines].text = line.text;
			newLines[numLines].x = line.x;
			newLines[numLines].y = line.y;
			newLines[numLines].fontfamily = line.fontfamily;
			newLines[numLines].fontsize = line.fontsize;
			newLines[numLines].bold = line.bold;
			newLines[numLines].italic = line.italic;
			newLines[numLines].underline = line.underline;
			numLines++;
		}
		
	}
	textLines = [];
	for(var i=1;i<newLines.length;i++){
		var line = newLines[i];
		textLines[i] = new TextLine();
		textLines[i].id = line.id;
		textLines[i].text = line.text;
		textLines[i].x = line.x;
		textLines[i].y = line.y;
		textLines[i].fontfamily = line.fontfamily;
		textLines[i].fontsize = line.fontsize;
		textLines[i].bold = line.bold;
		textLines[i].italic = line.italic;
		textLines[i].underline = line.underline;
	}*/
	//textLines = newLines;
	//numLines --;
	var xmlhttp;
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  	xmlhttp=new XMLHttpRequest();
	}
	else
	  {// code for IE6, IE5
	  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	  
	xmlhttp.open("GET","deletetextline.php?id="+line_id,true);
	xmlhttp.send();	
	
	removeAllTextLines();
	for(var i=1;i<textLines.length;i++){
		if(textLines[i].show){
			addTextLine(i);
		}
	}
	invalidate();
}

function removeAllTextLines(){
	$('#textlineTable tbody tr').remove();
}

function toggleGrid(element){
	if(gridOn==true){
		element.innerHTML = "Grid Lines: Off";
		gridOn = false;
	}
	else{
		element.innerHTML = "Grid Lines: On";
		gridOn = true;
	}
	invalidate();
}

function addGraphic(){
	$('#fade').css('display','block');	
	$('#addgraphicpopup').css('display','block');
}

function addTable(){
	$('#fade').css('display','block');	
	$('#addtablepopup').css('display','block');
}

function addBorder(){
	$('#fade').css('display','block');	
	$('#addborderpopup').css('display','block');
}

function addColour(){
	$('#fade').css('display','block');	
	$('#addcolourpopup').css('display','block');
}

function addMaterial(){
	$('#fade').css('display','block');	
	$('#addmaterialpopup').css('display','block');
}

function addTemplate(){
	$('#fade').css('display','block');	
	$('#addtemplatepopup').css('display','block');
}

function addLine(){
	$('#fade').css('display','block');	
	$('#addlinepopup').css('display','block');
}

function closePopup(popup){
	$(popup).css('display','none');
	$('#fade').css('display','none');	
}

function setSave(save){
	toSave = save;	
}

function saveDesign(){
	if(toSave){
		var xmlhttp;
		if (window.XMLHttpRequest)
		  {// code for IE7+, Firefox, Chrome, Opera, Safari
		  	xmlhttp=new XMLHttpRequest();
		}
		else
		  {// code for IE6, IE5
		  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		var s = "{ \"orderitem_id\": "+$('#orderitem_id').html()+", \"textlines\": [";
		for(var i=1;i<numLines;i++){
			if(textLines[i].show){
				s+=textLines[i].toJson()+",";
			}
		}  
		if(numLines > 0&&textLines[numLines]!=undefined&&textLines[numLines].show){
			s+=textLines[numLines].toJson();
		}
		s+="]}";
		/*xmlhttp.onreadystatechange=function()
	  {
	  if (xmlhttp.readyState==4 && xmlhttp.status==200)
	    {
	    alert(xmlhttp.responseText);
	    }
	  }*/
		xmlhttp.open("POST","design_save.php",true);
		xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		xmlhttp.send("s="+escape(s));	
		
		toSave = false;
	}
	
}
