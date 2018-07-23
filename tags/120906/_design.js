var currentZoomLevel = 100;
var gridOn = false;
var INTERVAL = 20; //how often we check for a re-draw
var SAVE_INTERVAL = 1000; //how often we check if we need to save
var WIDTH; 		   //the width and height of the canvas.
var HEIGHT;
var browserName=navigator.appName;  //necessary to take IE into account.


var _objectIdSeed = 100;
//need to keep track of all of the textLines to be drawn
var textLines = [];
var textColor = "black";
//the logos to be put on the stamp
var images = [];
var borders = [];
var lines = [];
var tables = [];
var objects = [];

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
var mySelBoxColor = 'white'; 
var mySelBoxSize = 6;



var _stateHistory = {		
		MAX_SIZE : 10,
		data : [],
		selected : -1,
		save : function()
		{
			if((this.selected >= 0) && (this.selected < this.data.length -1))
			{
				this.data = this.data.slice(0,this.selected + 1);
			}							
			this.data.push(getStateAsJSON());
			if(this.data.length > this.MAX_SIZE) this.data = this.data.slice(1);
			this.selected = this.data.length - 1;
			return true;
		},
		loadPrevious : function()
		{
			if(this.selected == 0) return false;
			if(this.selected == this.data.length -1)
			{
				this.data[this.selected] = getStateAsJSON();	
			}
			this.selected--;
			parseJSON(this.data[this.selected]);
			invalidate(true);
			return true;
		},
		loadNext : function()
		{
			if(this.selected == this.data.length -1) return false;
			this.selected++;
			parseJSON(this.data[this.selected]);
			invalidate(true);
			return true;
		}
	};



// Padding and border style widths for mouse offsets
var stylePaddingLeft, stylePaddingTop, styleBorderLeft, styleBorderTop;

var selectedImage = -1;
var selectedTemplate = -1;

TextLine = function(){
	this.id = _objectIdSeed++;
	this.text = "";
	this.fontsize = 6;
	this.fontfamily = "Arial";
	this.bold = 0;
	this.italic = 0;
	this.underline = 0;
	this.x = 0;
	this.y = 0;
	this.align = "left";
	this.type = "Straight";
	this.radius = 0;
}

//the object that represents the logo

Logo = function(){
	this.image;
	this.id = _objectIdSeed++;
	this.image_id = -1;
	this.x = 0;
	this.y = 0;
	this.width = 100;
	this.height = 100;
	
	//Images cant be serialized so this class must override the defualt state
	this.getState = function() { return {
		id :		this.id,
		image_id :	this.image_id,
		x :			this.x,
		y :			this.y,
		width :		this.width,
		height :	this.height,
	};};
}

Border = function(){
	this.id = _objectIdSeed++;
	this.x = 0;
	this.y = 0;
	this.width=100;
	this.height = 100;
	this.style_id = 0;
	this.type_id = 0;
	this.line_width = 2;
	this.sides = "1111";
	this.radius = 1;
}

Line = function(){
	this.id = _objectIdSeed++;
	this.x = 10;
	this.y = 10;
	this.x2 = 100;
	this.y2 = 100;
	this.type_id = 1;
	this.line_width = 2;
}

Table = function(){
	this.id = _objectIdSeed++;
	this.x = 10;
	this.y = 10;
	this.width = 100;
	this.height = 100;
	this.border = 1;
	this.columns = 1;
	this.rows = 1;
} 

var SelectedBorder = new Border();
var SelectedLine = new Line();
var SelectedTable = new Table();
//these are used for resizing boxes and for bounding boxes.
Box = function(){
	this.x = 0;
	this.y = 0;
	this.width = mySelBoxSize;
	this.height = mySelBoxSize;

}

function drawStraightTextLine(textLine,context){
	context.fillText(textLine.text,textLine.x,textLine.y);
}

function drawCurvedTextLine(textLine,flip,context){
 	var text = textLine.text;
 	var metric = context.measureText(text);
 	metric.height = textLine.fontsize*1.2;
 	var radius  = textLine.radius;//metric.width/2;
	var flip = (!flip ? false : true);
    var rotation_end = Math.PI/2;
    var rotation_start = -Math.PI/2;
    var coeff = 1;
    if(textLine.type=="Curved Up"){
    	rotation_end +=Math.PI;
    	rotation_start +=Math.PI;
    }
    if(textLine.type=="Full Circle"){
    	rotation_end = -Math.PI/2;
    	rotation_start = 3*Math.PI/2;
    	coeff = -1;
    }
    /*if(textLine.type=="Curved Up"){
    	var t = rotation_start;
    	rotation_start = rotation_end;
    	rotation_end = t;
    }*/
    var total_radians = rotation_end - rotation_start;
    var piece_radians = coeff*(flip ? 1 : -1) * total_radians / (text.length);
    
    context.save();
    context.translate(textLine.x+radius, textLine.y- (flip ? -1 : 1) *radius);
    context.rotate(rotation_start);

    for(var l = 0; l < text.length; l++){
        context.fillText(text[l], 0, (flip ? -1 : 1) * radius);
        context.rotate(piece_radians);
    }
    
    context.restore();
	//context.strokeRect(textLine.x-metric.height,textLine.y-metric.height,radius*2+metric.height*2,radius+metric.height);
	//drawBoundingBox(textLine,context);
	
}

//draws the text line to the canvas
drawTextLine = function(textLine,context){
	context.fillStyle = textColor;
	context.strokeStyle = textColor;
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
	if(textLine.type == "Straight"){
		drawStraightTextLine(textLine,context);
	}
	else if(textLine.type=="Curved Down"){
		drawCurvedTextLine(textLine,true,context);
		
	}
	else if(textLine.type=="Curved Up"){
		drawCurvedTextLine(textLine,false,context);
	}
	else if(textLine.type=="Full Circle"){
		drawCurvedTextLine(textLine,true,context);
	}	
}

//returns a Box object representing a bounding box for a textLine
getBoundingBox = function(textLine){
	context.font = textLine.fontstyle+" "+textLine.fontsize+"pt "+textLine.fontfamily;
	var metric = context.measureText(textLine.text);
	metric.height = textLine.fontsize*1.5;
	var box = new Box;
	if(textLine.type=="Straight"){
		box.x = textLine.x;
		box.y = textLine.y-metric.height*0.8;
		box.width = metric.width;
		box.height = metric.height;
	}
	//context.strokeRect(textLine.x-metric.height,textLine.y-metric.height,radius*2+metric.height*2,radius+metric.height);
	else if(textLine.type=="Curved Down"){
		box.x = textLine.x-metric.height;
		box.y = textLine.y-metric.height;
		box.width = textLine.radius*2+metric.height*2;//metric.width+metric.height*2;
		box.height = textLine.radius*1+1*metric.height;//metric.width/2+metric.height;
	}
	else if(textLine.type=="Curved Up"){
		box.x = textLine.x-metric.height;
		box.y = textLine.y-textLine.radius;
		box.width = textLine.radius*2+metric.height*2;//metric.width+metric.height*2;
		box.height = textLine.radius*1+1*metric.height;//metric.width/2+metric.height;
	}
	else if(textLine.type=="Full Circle"){
		box.x = textLine.x-metric.height;
		box.y = textLine.y-metric.height;
		box.width = textLine.radius*2+metric.height*2;//metric.width+metric.height*2;
		box.height = textLine.radius*2+metric.height*2;//metric.width/2+metric.height;
	}
	return box;
}


//draws a box around where a text line would be. This is used to determine if the user has clicked on a text line.
drawBoundingBox = function(textLine,context){
	var box = getBoundingBox(textLine);
	context.strokeStyle = "#000000";
	context.lineWidth =1;
	//console.log('Box Dimensions: ('+x+','+y+'),('+(x+width)+','+(y+height)+')');
	context.strokeRect(box.x,box.y,box.width,box.height);
	//context.fill();

}

drawImageBoundingBox = function(image,context){
	context.lineWidth =1;
	context.strokeStyle = "#000000";
	//console.log('Box Dimensions: ('+x+','+y+'),('+(x+width)+','+(y+height)+')');
	context.strokeRect(image.x,image.y,image.width,image.height);
	//context.fill();

}


clear = function(context){
	context.clearRect(0,0,WIDTH,HEIGHT);

}
//draws the selection handles
drawSelectionHandles = function(context){
	context.fillStyle = mySelBoxColor;
	context.strokeStyle = "#000000";
	for(var i = 0;i<4;i++){
		if(selectionHandles[i].x >=0){
			context.fillRect(selectionHandles[i].x,selectionHandles[i].y,mySelBoxSize,mySelBoxSize);
			context.strokeRect(selectionHandles[i].x,selectionHandles[i].y,mySelBoxSize,mySelBoxSize);
		}
	}

}


function drawImage(image,context){
	if(image.image){
		context.drawImage(image.image,image.x,image.y,image.width,image.height);
	}
	
}

function drawBorder(border,context)
{
	context.lineWidth = border.line_width;
	context.strokeStyle = textColor;
	border.radius = parseInt(border.radius);
	border.width = parseInt(border.width);
	border.height = parseInt(border.height);
	border.x = parseInt(border.x);
	border.y = parseInt(border.y);
	if(border.type_id=="Rectangular"){
		
		var sides = border.sides.split("");
		if(sides[0]==1){
			context.beginPath();
			context.moveTo(border.x+border.radius,border.y);
			//context.lineTo(border.x+1*border.width-border.radius,border.y);
			context.arcTo(border.x+1*border.width,border.y,border.x+1*border.width,border.y+border.radius,border.radius);
			context.stroke();
		}
		if(sides[1]==1){
			context.beginPath();
			context.moveTo(border.x+1*border.width,border.y+border.radius);
			//context.lineTo(border.x+1*border.width,border.y+1*border.height-border.radius);
			context.arcTo(border.x+border.width,border.y+border.height,border.x+border.width-border.radius,border.y+border.height,border.radius);
			context.stroke();
		}
		if(sides[2]==1){
			context.beginPath();
			context.moveTo(border.x+border.width-border.radius,border.y+1*border.height);
			//context.lineTo(border.x+1*border.width-border.radius,border.y+1*border.height);
			context.arcTo(border.x,border.y+border.height,border.x,border.y+border.height-border.radius,border.radius);
			context.stroke();
		}
		if(sides[3]==1){
			context.beginPath();
			context.moveTo(border.x,border.y+border.height-border.radius);
			context.arcTo(border.x,border.y,border.x+border.radius,border.y,border.radius);
			context.stroke();
		}
	}
	else if(border.type_id == "Circular")
	{
	   var centerX = (border.x+border.width)/2;
	   var centerY = (border.y+border.height)/2;
	   context.beginPath();
		for (var i = 0 * Math.PI; i < 2 * Math.PI; i += 0.01 ) 
		{
			xPos = centerX - (border.height/2 * Math.sin(i)) * Math.sin(0 * Math.PI) + (border.width/2 * Math.cos(i)) * Math.cos(0 * Math.PI);
			yPos = centerY + (border.width/2 * Math.cos(i)) * Math.sin(0 * Math.PI) + (border.height/2 * Math.sin(i)) * Math.cos(0 * Math.PI);

    		if (i == 0) 
    		{
    		    context.moveTo(xPos, yPos);
    		} 
    		else 
    		{
    		    context.lineTo(xPos, yPos);
    		}
		}
		context.stroke();
		context.closePath();
	}
		
}

function drawLine(line,context){
	context.strokeStyle = textColor;
	//context.drawImage(context.image,0,0,WIDTH,HEIGHT);
	context.lineWidth = line.line_width;
	line.x = parseInt(line.x);
	line.y = parseInt(line.y);
	line.x2 = parseInt(line.x2);
	line.y2 = parseInt(line.y2);
	context.beginPath();
	context.moveTo(line.x,line.y);
	context.lineTo(line.x2,line.y2);
	context.stroke();	
}

function drawTable(table,context){
	context.strokeStyle = textColor;
	context.lineWidth = 2;
	table.x = parseInt(table.x);
	table.y = parseInt(table.y);
	table.width = parseInt(table.width);
	table.height = parseInt(table.height);
	var cwidth = table.width/table.columns;
	var rheight = table.height/table.rows;
	//alert(cwidth+" "+rheight);
	if(table.border == 1){
		context.beginPath();
		context.rect(table.x,table.y,table.width,table.height);
		context.stroke();
	}
	for(var r = 1; r < table.rows; r++){
		context.beginPath();
		context.moveTo(table.x,table.y+r*rheight);
		context.lineTo(table.x+table.width,table.y+r*rheight);
		context.stroke();
	}
	for(var c = 1;c < table.columns;c++){
		context.beginPath();
		context.moveTo(table.x+c*cwidth,table.y);
		context.lineTo(table.x+c*cwidth,table.y+table.height);
		context.stroke();
	}	
}

function drawGrid(){
	context.strokeStyle = "#e8e8e8";
	context.lineWidth =1;
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


function drawToGhost(){
	var context = gcxt;
	clear(context);
	context.beginPath();
    context.rect(0, 0, canvas.width, canvas.height);
    context.fillStyle = "#ffffff";
    context.fill();
	
	for(var i=0;i<images.length;i++){
		drawImage(images[i],context);
	}
	for(var i=0;i<textLines.length;i++){
		drawTextLine(textLines[i],context);
	}
	for(var i=0;i<borders.length;i++){
			drawBorder(borders[i],context);
	}
	for(var i=0;i<lines.length;i++){
			drawLine(lines[i],context);
	}	
	for(var i=0;i<tables.length;i++){
		drawTable(tables[i],context);
	}
}
//this is the drawing loop function.
draw = function(){
	if(isValid == false){
		context.save();
		clear(context);
        context.fillStyle = "#ffffff";
        context.fillRect(0, 0, canvas.width, canvas.height);
		if(gridOn){
			drawGrid();
		}
		for(var i=0;i<images.length;i++){
			drawImage(images[i],context);
			if(mySel == images[i]){
				var half = mySelBoxSize / 2;
				selectionHandles[0].x = images[i].x-half;
				selectionHandles[0].y = images[i].y-half;
				selectionHandles[1].x = images[i].x+images[i].width-half;
				selectionHandles[1].y = images[i].y-half;
				selectionHandles[2].x = images[i].x-half;
				selectionHandles[2].y = images[i].y+images[i].height-half;
				selectionHandles[3].x = images[i].x+images[i].width-half;
				selectionHandles[3].y = images[i].y+images[i].height-half;
				drawSelectionHandles(context);
				drawImageBoundingBox(images[i],context);
			}
			
		}
		for(var i=0;i<textLines.length;i++){
			drawTextLine(textLines[i],context);
			if(mySel == textLines[i]){
				var half = mySelBoxSize / 2;
				box = getBoundingBox(textLines[i]);
				selectionHandles[0].x = box.x-half;
				selectionHandles[0].y = box.y-half;
				selectionHandles[1].x = box.x+box.width-half;
				selectionHandles[1].y = box.y-half;
				selectionHandles[2].x = box.x-half;
				selectionHandles[2].y = box.y+box.height-half;
				selectionHandles[3].x = box.x+box.width-half;
				selectionHandles[3].y = box.y+box.height-half;
				if(textLines[i].type!="Straight"){
					drawSelectionHandles(context);
				}
				drawBoundingBox(textLines[i],context);
			}
		}
		
		for(var i=0;i<borders.length;i++){
			drawBorder(borders[i],context);
			if(mySel == borders[i]){
				var half = mySelBoxSize / 2;
				selectionHandles[0].x = borders[i].x-half;
				selectionHandles[0].y = borders[i].y-half;
				selectionHandles[1].x = borders[i].x+borders[i].width-half;
				selectionHandles[1].y = borders[i].y-half;
				selectionHandles[2].x = borders[i].x-half;
				selectionHandles[2].y = borders[i].y+borders[i].height-half;
				selectionHandles[3].x = borders[i].x+borders[i].width-half;
				selectionHandles[3].y = borders[i].y+borders[i].height-half;
				drawSelectionHandles(context);
			}
		}
		
		for(var i=0;i<lines.length;i++){
			drawLine(lines[i],context);
			if(mySel == lines[i]){
				var half = mySelBoxSize / 2;
				selectionHandles[0].x = lines[i].x-half;
				selectionHandles[0].y = lines[i].y-half;
				selectionHandles[1].x = lines[i].x2-half;
				selectionHandles[1].y = lines[i].y2-half;
				selectionHandles[2].x = (lines[i].x+lines[i].x2)/2-half;
				selectionHandles[2].y = (lines[i].y+lines[i].y2)/2-half;
				selectionHandles[3].x = -1;
				selectionHandles[3].y = -1;
				drawSelectionHandles(context);
			}
		}
		
		for(var i=0;i<tables.length;i++){
			drawTable(tables[i],context);
			if(mySel == tables[i]){
				var half = mySelBoxSize / 2;
				selectionHandles[0].x = tables[i].x-half;
				selectionHandles[0].y = tables[i].y-half;
				selectionHandles[1].x = tables[i].x+tables[i].width-half;
				selectionHandles[1].y = tables[i].y-half;
				selectionHandles[2].x = tables[i].x-half;
				selectionHandles[2].y = tables[i].y+tables[i].height-half;
				selectionHandles[3].x = tables[i].x+tables[i].width-half;
				selectionHandles[3].y = tables[i].y+tables[i].height-half;
				drawSelectionHandles(context);
			}
		}
		
		isValid = true;
		context.restore();
		context.closePath();
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
    invalidate(true);
  } else if (isResizeDrag) {
		// time to resize!
		//getMouse(e);
		var oldx = mySel.x;
		var oldy = mySel.y;
	 	if(mySel instanceof Line){
	 		switch (expectResize){
	 			case 0:
	 				mySel.x = mx;
	 				mySel.y = my;
	 				break;
	 			case 2:
	 				oldx = (mySel.x+mySel.x2)/2;
	 				oldy = (mySel.y+mySel.y2)/2;
	 				mySel.x = mySel.x-(oldx-mx);
	 				mySel.y = mySel.y-(oldy-my);
	 				mySel.x2 = mySel.x2-(oldx-mx);
	 				mySel.y2 = mySel.y2-(oldy-my);
	 				break;
	 			case 1:
	 				mySel.x2 = mx;
	 				mySel.y2 = my;
	 				break;
	 				
	 		}
	 		if(mySel.x > mySel.x2){
	 			var temp = mySel.x;
	 			mySel.x = mySel.x2;
	 			mySel.x2 = mySel.x;
	 		}
	 		if(mySel.y > mySel.y2){
	 			var temp = mySel.y;
	 			mySel.y = mySel.y2;
	 			mySel.y2 = mySel.y;
	 		}	
	 	}
		else{
			switch (expectResize) {
			  case 0:
				mySel.x = mx;
				mySel.y = my;
				mySel.width += oldx - mx;
				mySel.height += oldy - my;
				if(!(mySel instanceof Border)){
					mySel.radius += (oldx - mx)/2;
				}
				break;
			  case 1:
				mySel.y = my;
				mySel.width = mx - oldx;
				mySel.height += oldy - my;
				if(!(mySel instanceof Border)){
					mySel.radius = (mx - oldx)/2;
				}
				break;
			  case 2:
				mySel.x = mx;
				mySel.width += oldx - mx;
				mySel.height = my - oldy;
				if(!(mySel instanceof Border)){
					mySel.radius += (oldx - mx)/2;
				}
				break;
			  case 3:
				mySel.width = mx - oldx;
				mySel.height = my - oldy;
				if(!(mySel instanceof Border)){
					mySel.radius = (mx - oldx)/2;
				}
				break;
			}
		}
		// something is changing position so we better invalidate the canvas!
		invalidate(true);
	}
	
	 getMouse(e);
  // if there's a selection see if we grabbed one of the selection handles
  if (mySel !== null && !isResizeDrag) {
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
        if(mySel instanceof Line){
        	this.style.cursor = 'move';
        }
        else{
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
	    }   
        return;
      }
      
    }
	 // not over a selection box, return to normal
    isResizeDrag = false;
    expectResize = -1;
    this.style.cursor='auto';
	}
   
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
 //check the already selected object
 if(mySel instanceof Line){
 	if(mx>=mySel.x&&mx<1*mySel.x2+1*mySel.line_width&&my>=mySel.y&&my<=1*mySel.y2+1*mySel.line_width){
 		return;	
 	}
 }
 else if(mySel instanceof TextLine){
 	var box = getBoundingBox(mySel);
 	if((mx>box.x&&mx<box.x+box.width)&&(my>box.y&&my<box.y+box.height)){
 		offsetx = mx - mySel.x;
		offsety = my - mySel.y;
 		isDrag = true;
		invalidate(true);
 		return;	
 	}
 }
 else if(mySel instanceof Logo){
 	if((mx>mySel.x&&mx<mySel.x+mySel.width)&&(my>mySel.y&&my<mySel.y+mySel.height)){
 		offsetx = mx - mySel.x;
		offsety = my - mySel.y;
 		isDrag = true;
		invalidate(true);
 		return;	
 	}
 }
 else if(mySel instanceof Border){
 	if((mx>mySel.x&&mx<mySel.x+mySel.width)&&(my>mySel.y&&my<mySel.y+mySel.height)){
 		offsetx = mx - mySel.x;
		offsety = my - mySel.y;
 		isDrag = true;
		invalidate(true);
 		return;	
 	}
 }
 else if(mySel instanceof Table){
 	if((mx>mySel.x&&mx<mySel.x+mySel.width)&&(my>mySel.y&&my<mySel.y+mySel.height)){
 		offsetx = mx - mySel.x;
		offsety = my - mySel.y;
 		isDrag = true;
		invalidate(true);
 		return;	
 	}
 }
 
  // run through all the text lines
  var l = textLines.length;
  for (var i = l-1; i >= 0; i--) {
	//alert("drawingBoundingBox for "+i);
	 
	var box = getBoundingBox(textLines[i]);
	//console.log("Testing line click: ("+mx+","+my+") in ("+box.x+","+box.y+","+(box.x+box.width)+","+(box.y+box.height)+")");\
	if((mx>box.x&&mx<box.x+box.width)&&(my>box.y&&my<box.y+box.height)){
	  mySel = textLines[i];
	  offsetx = mx - mySel.x;
	  offsety = my - mySel.y;
	  /*mySel.x = mx - offsetx;
	  mySel.y = my - offsety;*/
	  //console.log("Offset: ("+offsetx+","+offsety+")");
	  isDrag = true;
	  //alert("Selected "+i);
	  invalidate(true);
	  clear(gcxt);
	  return;
	}
  }
  //check to see if they have selected the logo
  clear(gcxt);
 	for (var i = images.length-1; i >= 0; i--) {
		if((mx>images[i].x&&mx<images[i].x+images[i].width)&&(my>images[i].y&&my<images[i].y+images[i].height)){
			mySel = images[i];
			offsetx = mx - mySel.x;
			offsety = my - mySel.y;
			mySel.x = mx - offsetx;
			mySel.y = my - offsety;
			isDrag = true;
			invalidate(true);
			return;
		}
	}
	
	for(var i=0;i<lines.length;i++){
		if(mx>=lines[i].x&&mx<1*lines[i].x2+1*lines[i].line_width&&my>=lines[i].y&&my<=1*lines[i].y2+1*lines[i].line_width){
			//alert("("+mx+","+my+") ("+lines[i].x+","+lines[i].y+") ("+(1*lines[i].x2+1*lines[i].line_width)+","+(1*lines[i].y2+1*lines[i].line_width)+")");
			mySel = lines[i];
			/*offsetx = mx - mySel.x;
			offsety = my - mySel.y;
			mySel.x = mx - offsetx;
			mySel.y = my - offsety;*/
			isDrag = true;
			invalidate(true);
			return;
		}
	}
	
	
	for(var i=0;i<borders.length; i++){
		if((mx>borders[i].x&&mx<borders[i].x+borders[i].width)&&(my>borders[i].y&&my<borders[i].y+borders[i].height)){
			mySel = borders[i];
			offsetx = mx - mySel.x;
			offsety = my - mySel.y;
			mySel.x = mx - offsetx;
			mySel.y = my - offsety;
			isDrag = true;
			invalidate(true);
			return;
		}
	}
	
	for(var i=0;i<tables.length; i++){
		if((mx>tables[i].x&&mx<tables[i].x+tables[i].width)&&(my>tables[i].y&&my<tables[i].y+tables[i].height)){
			mySel = tables[i];
			offsetx = mx - mySel.x;
			offsety = my - mySel.y;
			mySel.x = mx - offsetx;
			mySel.y = my - offsety;
			isDrag = true;
			invalidate(true);
			return;
		}
	}
	
	
  // havent returned means we have selected nothing
  mySel = null;
  // clear the ghost canvas for next time
  clear(gcxt);
  // invalidate because we might need the selection border to disappear
  invalidate(true);
}


invalidate = function(save){
	isValid = false;
	setSave(save);
}


function parseJSON(s)
{
	var object;	
	try
	{
		object = jQuery.parseJSON(s);		
	}
	catch(e)
	{
 		return false;
	}
	
	//note: getStateAsJSON is in many ways the counterpart
	var setState = function(dest, state)
	{
		if(dest.setState) { dest.setState(s); }
		else
		{
			for(var param in state) dest[param] = state[param];  
		}
	};
	
	var loadStateArray = function(state, constructor)
	{
		if(state == null) return null;
		
		var r = [];
		for(var i=0; i<state.length; i++)
		{
			if(state[i] != null)
			{
				var o = new constructor();
				setState(o, state[i]);				
				r.push(o);
			}
			else r.push(null);
		}
		return r;
	};
	
	_objectIdSeed = 100;
	eraseAllElements();	
	

	if(object.textColor) textColor = object.textColor;
	if(object.currentZoomLevel) zoom(object.currentZoomLevel); 
			
			
	textLines = loadStateArray(object.textLines, TextLine);
	images = loadStateArray(object.images, Logo);
	borders = loadStateArray(object.borders, Border);
	lines = loadStateArray(object.lines, Line);
	tables = loadStateArray(object.tables, Table);
	
	for(var i=0; i<textLines.length; i++)
	{
		addTextLine(i);
	}
	
	var getImageOnLoadCallback = function(logo)
	{
		return function()
		{
			logo.image = this;
			invalidate(false);
		}
	}
	
	for(var i=0; i<images.length; i++)
	{
		var image = new Image();
		image.onload = getImageOnLoadCallback(images[i]);
		image.src = "image.php?id="+images[i].image_id+"&color="+textColor;
		newObject("image",images[i]);
	}
	  	
	for(var i=0; i<borders.length; i++)
	{
		if(borders[i].width < 1)
		{
			borders[i].x *= WIDTH;
			borders[i].y *= HEIGHT;
			borders[i].width *= WIDTH;
			borders[i].height *= HEIGHT;
		}
		
		if(borders[i].radius <= 0)
		{
			borders[i].radius = 1;
		}
		
		newObject("border",borders[i]);	
	}
	
	for(var i=0; i<lines.length; i++)
	{	
		if(lines[i].width < 1)
		{
			lines[i].x *= WIDTH;
			lines[i].y *= HEIGHT;
			lines[i].x2 *= WIDTH;
			lines[i].y2 *= HEIGHT;
		}
		newObject("line",lines[i]);
	}
	
	for(var i=0; i<tables.length; i++)
	{
		if(tables[i].width < 1)
		{
			tables[i].x *= WIDTH;
			tables[i].y *= HEIGHT;
			tables[i].width *= WIDTH;
			tables[i].height *= HEIGHT;
		}
		newObject("table",tables[i]);
	}
	
	if(object.objectIdSeed) _objectIdSeed = object.objectIdSeed;	
	
	toSave = false;
	return true;
}

init = function(){
	/*$("#zoom").slider({
		max: 500,
		min: 10,
		value: 100,
		slide: function(event, ui) {
			zoom(ui.value);
		}
	});*/
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
	
	context.lineCap = "square";
	gcxt.lineCap = "square";
	/**
	 * Load in the textlines from the database if there is any.
	 */
	textColor = $('#textColor').html();
	textColor = textColor.replace(/^\s+|\s+$/g, '')
	var s = $('#textlinesdb').html();
	
	if(!parseJSON(s))
	{
		showPopup('#addtemplatepopup');		
	}
	
	for (var i = 0; i < 4; i ++) {
		var rect = new Box;
		selectionHandles.push(rect);
    }
	//drawBorder(border,context);
	 // fixes mouse co-ordinate problems when there's a border or padding
	  // see getMouse for more detail
	  if (document.defaultView && document.defaultView.getComputedStyle) {
		stylePaddingLeft = parseInt(document.defaultView.getComputedStyle(canvas, null)['paddingLeft'], 10)      || 0;
		stylePaddingTop  = parseInt(document.defaultView.getComputedStyle(canvas, null)['paddingTop'], 10)       || 0;
		styleBorderLeft  = parseInt(document.defaultView.getComputedStyle(canvas, null)['borderLeftWidth'], 10)  || 0;
		styleBorderTop   = parseInt(document.defaultView.getComputedStyle(canvas, null)['borderTopWidth'], 10)   || 0;
	  }
	
	//draw the textLines to the stamp
	invalidate(false);
	draw();
	invalidate(false);
	//set up the interval for re-draw
	setInterval(draw, INTERVAL);
	
	//set up the interval for save
	setInterval(saveDesign,SAVE_INTERVAL);
	
	//add the events that will be taking care of the mouse clicks. 
	canvas.onmousedown = myDown;
	canvas.onmouseup = myUp;
	canvas.onmousemove = myMove;
	
	changeImageCategory(1);
	changeTemplateCategory(1);
	
	_stateHistory.save();
}

window.onload = function(){
	init();
	$("#zoom").slider({
		max: 500,
		min: 10,
		value: currentZoomLevel,
		slide: function(event, ui) {
			zoom(ui.value);
		}
	});
};


function zoom(level){
	if(level instanceof String){
		level = level.replace("%","");
	}
	currentZoomLevel = level;
	var currentValue = $('#currentzoom');
	currentValue.val(level+"%");
	
	var width = 242*(level/100);
	var height = 107*(level/100);
	
	var surface = $("#surface");
	
	surface.width(width);
	surface.height(height);
	toSave = true;
}

function changeTextLine(lineNum,element){
	//alert(lineNum);
	textLines[lineNum].text = element.value;
	invalidate(true);
	_stateHistory.save();
}

function changeTextLineType(lineNum,element){
	textLines[lineNum].type = element.value;
	var metric = context.measureText(textLines[lineNum].text);
	textLines[lineNum].radius = metric.width/2;
	
	invalidate(true);
	_stateHistory.save();
}

function changeTextLineFontSize(lineNum,element){
	textLines[lineNum].fontsize = element.value;
	invalidate(true);
	_stateHistory.save();	
}

function changeTextLineFont(lineNum,element){
	//alert("Change font: "+lineNum+" to "+element.value);
	textLines[lineNum].fontfamily = element.value;
	invalidate(true);
	_stateHistory.save();
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
	invalidate(true);
	_stateHistory.save();
}

var _textLineUI = {
		data : [],
		findLine : function(dataIndex)
		{
			for(var i=0; i<textLines.length; i++)
			{
				if(textLines[i] == this.data[dataIndex]) return i;
			}
			return -1;
		},
	};

function changeTextLineAlign(a, b, c)
{
}

function addTextLine(index)
{
	var lineNumber = _textLineUI.data.length; 
	var tl = textLines[index];
	_textLineUI.data.push(tl);
	
	var font = tl.fontfamily;
	var s = "<option>"+font;
	var rep = "<option selected>"+font;
	var fonts = $('#fontselect').html().replace("#LINENUM",""+lineNumber);
	fonts = fonts.replace(s,rep);
	fontsize = tl.fontsize;
	s = "<option>"+fontsize;
	rep = "<option selected>"+fontsize;
	var fontsizes = $('#fontsizeselect').html().replace("#LINENUM",""+lineNumber);
	fontsizes = fontsizes.replace(s,rep);
	var type = tl.type;
	var line = '<tr><td align="center">'+(lineNumber + 1)+'</td>';
	var typeLine = '<select onChange="changeTextLineType('+index+',this)"><option>Straight</option><option>Curved Down</option><option>Curved Up</option><option>Full Circle</option></select>';
	typeLine = typeLine.replace('>'+type,' selected>'+type);
	line+='<td align="center">'+typeLine+'</td>';
	//line+='<td width="10%" align="center"><input type="text" value="100" size="5"></td>';
	line+='<td><input type="text" size="100" onKeyUp="changeTextLine(_textLineUI.findLine(' + lineNumber + '),this)" value="'+tl.text+'"></td>';
	line+='<td align="center">'+fonts+'</td>';
	line+='<td align="center">'+fontsizes+'</td>';
	line+='<td align="center"><input type="checkbox" onChange="changeTextLineFontStyle(_textLineUI.findLine(' + lineNumber + '),this,\'bold\')"';
	if(tl.bold == 1){
		line += ' checked';
	}
	line +='></td><td align="center"><input type="checkbox" onChange="changeTextLineFontStyle(_textLineUI.findLine(' + lineNumber + '),this,\'italic\')"';
	if(textLines[index].italic == 1){
		line+=' checked';
	}
	line+= '></td><td align="center">L <input type="radio" name="align' + lineNumber + '" onChange="changeTextLineAlign(_textLineUI.findLine(' + lineNumber + '),this,\'left\')" checked="checked"> C <input type="radio" name="align'+lineNumber+'" onChange="changeTextLineAlign(_textLineUI.findLine(' + lineNumber + '),this,\'center\')"> R <input type="radio" name="align'+lineNumber+'" onChange="changeTextLineAlign(_textLineUI.findLine(' + lineNumber + '),this,\'right\')"></td>';
	line+='<td align="center"><div class="button" onClick="selectTextLine(_textLineUI.findLine(' + lineNumber + '))">Select</div></td>';
	line+='<td align="center"><div class="button" onClick="deleteTextLine(_textLineUI.findLine(' + lineNumber + '), true)">Delete</div></td></tr>';
	
	$('#textlineTable > tbody:last').append(line);
}

function newTextLine(){
	textLines.push(new TextLine());
	var index = textLines.length - 1;
	textLines[index].x = 85;
	textLines[index].y = 26;	
	addTextLine(index);
	invalidate(true);
	_stateHistory.save();
}

function newGraphic(){
	closePopup('#addgraphicpopup');
	
	var i = images.length-1;
	images[i+1] = new Logo();
	var image = new Image();
	image.index = i;
	//image.selectedImage = selectedImage;
	image.onload = function(){
		images[this.index+1].image = this;
		images[this.index+1].x = 10;
		images[this.index+1].y = 10;
		images[this.index+1].width = this.width;
		images[this.index+1].height = this.height;
		//images[this.index+1].id = 3;
		//images[this.index+1].image_id = selectedImage;
		invalidate(false);
	}
	image.src = "image.php?id="+selectedImage+"&color="+textColor;
	images[i+1].image_id = selectedImage;
	newObject("image",images[i+1]);
	//alert(xmlhttp.responseText);
	selectedImage = -1;
	
	_stateHistory.save();
	invalidate(true);
}

function newBorder(){
	closePopup('#addborderpopup');
	
	var i = borders.length-1;
	borders[i+1] = new Border();
	borders[i+1].x = SelectedBorder.x;
	borders[i+1].y = SelectedBorder.y;
	borders[i+1].width = SelectedBorder.width;
	borders[i+1].height = SelectedBorder.height;
	if($('#borderFrame').is(":checked")){
		borders[i+1].x = 4;
		borders[i+1].y = 4;
		borders[i+1].width = WIDTH-8;
		borders[i+1].height = HEIGHT-8;
	}
	borders[i+1].type_id = SelectedBorder.type_id;
	borders[i+1].style_id = SelectedBorder.style_id;
	borders[i+1].sides = SelectedBorder.sides;
	borders[i+1].radius = SelectedBorder.radius;
	borders[i+1].line_width = SelectedBorder.line_width;
	
	newObject("border",borders[i+1]);
	//alert(xmlhttp.responseText);
	SelectedBorder = new Border();
	
	_stateHistory.save();
	invalidate(true);
}

function newLine(){
	closePopup('#addlinepopup');
	if(SelectedLine.x > SelectedLine.x2){
		var temp = SelectedLine.x;
		SelectedLine.x = SelectedLine.x2;
		SelectedLine.x2 = temp;
	}
	if(SelectedLine.y > SelectedLine.y2){
		var temp = SelectedLine.y;
		SelectedLine.y = SelectedLine.y2;
		SelectedLine.y2 = temp;
	}
	
	var i = lines.length-1;
	
	lines[i+1] = new Line();
	lines[i+1].x = SelectedLine.x;
	lines[i+1].y = SelectedLine.y;
	lines[i+1].x2 = SelectedLine.x2;
	lines[i+1].y2 = SelectedLine.y2;
	lines[i+1].type_id = SelectedLine.type_id;
	lines[i+1].line_width = SelectedLine.line_width;
	
	newObject("line",lines[i+1]);
	//alert(xmlhttp.responseText);
	SelectedLine = new Line();
	
	_stateHistory.save();
	invalidate(true);
}

function selectTextLine(id)	{
	mySel = textLines[id];
	invalidate(false);
}

function newTable(){
	closePopup('#addtablepopup');
	
	var i = tables.length-1;
	tables[i+1] = new Table();
	tables[i+1].x = SelectedTable.x;
	tables[i+1].y = SelectedTable.y;
	tables[i+1].width = SelectedTable.width;
	tables[i+1].height = SelectedTable.height;
	tables[i+1].rows = SelectedTable.rows;
	tables[i+1].columns = SelectedTable.columns;
	tables[i+1].border = SelectedTable.border;
		
	newObject("table",tables[i+1]);
	//alert(xmlhttp.responseText);
	SelectedTable = new Table();	
	_stateHistory.save();

	invalidate(true);
}

var bpmx;
var bpmy;
var bpwidth;
var bpheight;
var bcontext;

function borderPreviewDraw(){
	
	var context = bcontext;
	var border = SelectedBorder;
	clear(bcontext);
	bcontext.strokeStyle = textColor;
	bcontext.drawImage(bcontext.image,0,0,WIDTH,HEIGHT);
	//if(SelectedBorder.show){
		bcontext.lineWidth = SelectedBorder.line_width;
	  	var sides = SelectedBorder.sides.split("");
	  	border.radius = parseInt(border.radius);
		border.width = parseInt(border.width);
		border.height = parseInt(border.height);
		border.x = parseInt(border.x);
		border.y = parseInt(border.y);
		if(sides[0]==1){
			context.beginPath();
			context.moveTo(border.x+border.radius,border.y);
			//context.lineTo(border.x+1*border.width-border.radius,border.y);
			context.arcTo(border.x+1*border.width,border.y,border.x+1*border.width,border.y+border.radius,border.radius);
			context.stroke();
		}
		if(sides[1]==1){
			context.beginPath();
			context.moveTo(border.x+1*border.width,border.y+border.radius);
			//context.lineTo(border.x+1*border.width,border.y+1*border.height-border.radius);
			context.arcTo(border.x+border.width,border.y+border.height,border.x+border.width-border.radius,border.y+border.height,border.radius);
			context.stroke();
		}
		if(sides[2]==1){
			context.beginPath();
			context.moveTo(border.x+border.width-border.radius,border.y+1*border.height);
			//context.lineTo(border.x+1*border.width-border.radius,border.y+1*border.height);
			context.arcTo(border.x,border.y+border.height,border.x,border.y+border.height-border.radius,border.radius);
			context.stroke();
		}
		if(sides[3]==1){
			context.beginPath();
			context.moveTo(border.x,border.y+border.height-border.radius);
			context.arcTo(border.x,border.y,border.x+border.radius,border.y,border.radius);
			context.stroke();
		}
	//}
	
}

function borderPreviewMouseDown(e){
	var element = null;
   if (e.target) element = e.target;
   else if (e.srcElement) element = e.srcElement;
   var offsetX = 0,offsetY = 0;
   if (element.offsetParent !== undefined) {
    do {
      offsetX += element.offsetLeft;
      offsetY += element.offsetTop;
    } while ((element = element.offsetParent));
  }
  bpmx = e.clientX - offsetX;
  bpmy = e.clientY - offsetY;
  
  
	
}

function borderPreviewMouseUp(e){
	var element = null;
   if (e.target) element = e.target;
   else if (e.srcElement) element = e.srcElement;
   var offsetX = 0,offsetY = 0;
   if (element.offsetParent !== undefined) {
    do {
      offsetX += element.offsetLeft;
      offsetY += element.offsetTop;
    } while ((element = element.offsetParent));
  }
  
  bpwidth = e.clientX - offsetX - bpmx;
  bpheight = e.clientY - offsetY - bpmy;
  SelectedBorder.x = bpmx;
  SelectedBorder.y = bpmy;
  SelectedBorder.width = bpwidth;
  SelectedBorder.height = bpheight;
  SelectedBorder.type_id = $('#borderType').val();
  SelectedBorder.style_id = $('#borderStyle').val();
  var sides = "";
  if($('#topSide').is(":checked")) sides = "1";
  else sides = "0";
  if($('#rightSide').is(":checked")) sides += "1";
  else sides += "0";
  if($('#bottomSide').is(":checked")) sides += "1";
  else sides += "0";
  if($('#leftSide').is(":checked")) sides += "1";
  else sides += "0";
  SelectedBorder.sides = sides;
  SelectedBorder.line_width = $('#borderWidth').val();
  SelectedBorder.radius = $('#borderRadius').val();
  SelectedBorder.show = true;
  borderPreviewDraw();
}

function initBorderPreview(){
	var bcanvas = document.getElementById("borderpreview");
	bcontext = bcanvas.getContext("2d");
	clear(bcontext);
	var image = new Image();
	image.context = bcontext;
	SelectedBorder.show = false;
	//image.selectedImage = selectedImage;
	image.onload = function(){
		this.context.image = this;
		borderPreviewDraw();
		
	}
	image.src = "getproductimage.php?id="+$('#orderitem_id').html();
	
	bcanvas.onmousedown = borderPreviewMouseDown;
	bcanvas.onmouseup = borderPreviewMouseUp;
	//bcanvas.onmousemove = myMove;
	
	
}

var linemx;
var linemy;
var linemx2;
var linemy2;
var linecontext;

function linePreviewDraw(){
	
	var context = linecontext;
	var line = SelectedLine;
	clear(context);
	context.strokeStyle = textColor;
	context.drawImage(context.image,0,0,WIDTH,HEIGHT);
	if(SelectedLine.show){
		context.lineWidth = SelectedLine.line_width;
		line.x = parseInt(line.x);
		line.y = parseInt(line.y);
		line.x2 = parseInt(line.x2);
		line.y2 = parseInt(line.y2);
		context.beginPath();
		context.moveTo(line.x,line.y);
		context.lineTo(line.x2,line.y2);
		context.stroke();
	}
	
}

function linePreviewMouseDown(e){
	var element = null;
   if (e.target) element = e.target;
   else if (e.srcElement) element = e.srcElement;
   var offsetX = 0,offsetY = 0;
   if (element.offsetParent !== undefined) {
    do {
      offsetX += element.offsetLeft;
      offsetY += element.offsetTop;
    } while ((element = element.offsetParent));
  }
  linemx = e.clientX - offsetX;
  linemy = e.clientY - offsetY;
  
  
	
}

function linePreviewMouseUp(e){
	var element = null;
   if (e.target) element = e.target;
   else if (e.srcElement) element = e.srcElement;
   var offsetX = 0,offsetY = 0;
   if (element.offsetParent !== undefined) {
    do {
      offsetX += element.offsetLeft;
      offsetY += element.offsetTop;
    } while ((element = element.offsetParent));
  }
  
  linemx2 = e.clientX - offsetX;
  linemy2 = e.clientY - offsetY;
  SelectedLine.x = linemx;
  SelectedLine.y = linemy;
  SelectedLine.x2 = linemx2;
  SelectedLine.y2 = linemy2;
  SelectedLine.type_id = $('#lineStyle').val();
  SelectedLine.line_width = $('#lineWidth').val();
  SelectedLine.show = true;
  linePreviewDraw();
}

function initLinePreview(){
	var canvas = document.getElementById("linepreview");
	linecontext = canvas.getContext("2d");
	clear(linecontext);
	var image = new Image();
	image.context = linecontext;
	SelectedLine.show = false;
	//image.selectedImage = selectedImage;
	image.onload = function(){
		this.context.image = this;
		linePreviewDraw();
		
	}
	image.src = "getproductimage.php?id="+$('#orderitem_id').html();
	
	canvas.onmousedown = linePreviewMouseDown;
	canvas.onmouseup = linePreviewMouseUp;
	//bcanvas.onmousemove = myMove;
	
	
}

var tablemx;
var tablemy;
var tablewidth;
var tableheight;
var tablecontext;

function tablePreviewDraw(){
	
	var context = tablecontext;
	var table = SelectedTable;
	clear(context);
	context.strokeStyle = textColor;
	context.drawImage(context.image,0,0,WIDTH,HEIGHT);
	if(table.show){
		context.lineWidth = 2;
		table.x = parseInt(table.x);
		table.y = parseInt(table.y);
		table.width = parseInt(table.width);
		table.height = parseInt(table.height);
		var cwidth = table.width/table.columns;
		var rheight = table.height/table.rows;
		//alert(cwidth+" "+rheight);
		if(table.border == 1){
			context.rect(table.x,table.y,table.width,table.height);
			context.stroke();
		}
		for(var r = 1; r < table.rows; r++){
			context.moveTo(table.x,table.y+r*rheight);
			context.lineTo(table.x+table.width,table.y+r*rheight);
			context.stroke();
		}
		for(var c = 1;c < table.columns;c++){
			context.moveTo(table.x+c*cwidth,table.y);
			context.lineTo(table.x+c*cwidth,table.y+table.height);
			context.stroke();
		}
		
	}
	
}

function tablePreviewMouseDown(e){
	var element = null;
   if (e.target) element = e.target;
   else if (e.srcElement) element = e.srcElement;
   var offsetX = 0,offsetY = 0;
   if (element.offsetParent !== undefined) {
    do {
      offsetX += element.offsetLeft;
      offsetY += element.offsetTop;
    } while ((element = element.offsetParent));
  }
  tablemx = e.clientX - offsetX;
  tablemy = e.clientY - offsetY;
  
  
	
}

function tablePreviewMouseUp(e){
	var element = null;
   if (e.target) element = e.target;
   else if (e.srcElement) element = e.srcElement;
   var offsetX = 0,offsetY = 0;
   if (element.offsetParent !== undefined) {
    do {
      offsetX += element.offsetLeft;
      offsetY += element.offsetTop;
    } while ((element = element.offsetParent));
  }
  
  tablewidth = e.clientX - offsetX - tablemx;
  tableheight = e.clientY - offsetY - tablemy;
  SelectedTable.x = tablemx;
  SelectedTable.y = tablemy;
  SelectedTable.width = tablewidth;
  SelectedTable.height = tableheight;
  SelectedTable.rows = $('#tableRow').val(); 
  //alert(SelectedTable.rows);
  SelectedTable.columns = $('#tableColumn').val();
  if($('#tableBorder').is(":checked")){
  	SelectedTable.border = 1;
  }
  else SelectedTable.border = 0;
  
  SelectedTable.show = true;
  tablePreviewDraw();
}

function initTablePreview(){
	var canvas = document.getElementById("tablepreview");
	tablecontext = canvas.getContext("2d");
	clear(tablecontext);
	var image = new Image();
	image.context = tablecontext;
	SelectedTable.show = false;
	//image.selectedImage = selectedImage;
	image.onload = function(){
		this.context.image = this;
		tablePreviewDraw();
		
	}
	image.src = "getproductimage.php?id="+$('#orderitem_id').html();
	
	canvas.onmousedown = tablePreviewMouseDown;
	canvas.onmouseup = tablePreviewMouseUp;
	//bcanvas.onmousemove = myMove;
	
	
}


function deleteTextLine(index, doSave)
{	
	textLines.splice(index,1);	
	removeAllTextLines();
	var count = 0;
	for(var i=0;i<textLines.length;i++)
	{
		count++;
		addTextLine(i,count);
	}
	invalidate(true);
	if(!!doSave) _stateHistory.save();
}

function removeAllTextLines(){
	_textLineUI.data = [];
	$('#textlineTable tbody tr').remove();
}

function deleteGraphic(index){
	if(mySel == images[index]){
		mySel = null;
	}
	images.splice(index,1);	
	invalidate(true);
}

function deleteBorder(index){
	if(mySel == borders[index]){
		mySel = null;
	}
	borders.splice(index,1);	
	
	invalidate(true);
}

function deleteLine(index){
	if(mySel == lines[index]){
		mySel = null;
	}
	lines.splice(index,1);	
	invalidate(true);	
}

function deleteTable(index){
	if(mySel == tables[index]){
		mySel = null;
	}
	tables.splice(index,1);	
	invalidate(true);	
}

function eraseAllElements(){
	clear(context);
	//for(var i=0;i<textLines.length;i++){
	//	deleteTextLine(i);		
	//}
	removeAllTextLines();
	
	for(var i=0;i<images.length;i++){
		deleteGraphic(i);
	}
	
	for(var i=0;i<borders.length;i++){
		deleteBorder(i);
	}
	
	for(var i=0;i<lines.length;i++){
		deleteLine(i);
	}
	
	for(var i=0;i<tables.length;i++){
		deleteTable(i);
	}
	
	textLines = [];
	images = [];
	borders = [];
	lines  = []
	tables = [];
	objects = [];
	$('#objects').html("<h3>Object List</h3>");
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
	invalidate(false);
}

function showPopup(popup){
	$('#fade').css('display','block');	
	$(popup).css('display','block');
	if(popup == '#addgraphicpopup'){
		$('#selectgraphictab').css('display','block');
		//addGraphic();
	}
	else if(popup == '#addborderpopup'){
		initBorderPreview();
	}
	else if(popup == '#addlinepopup'){
		initLinePreview();
	}
	else if(popup == '#addtablepopup'){
		initTablePreview();
	}
}

function closePopup(popup){
	$(popup).css('display','none');
	$('#fade').css('display','none');	
}

function showTab(id){
	$('#selectgraphictab').css('display','none');
	$('#symboltab').css('display','none');
	$('#uploadgraphictab').css('display','none');
	$(id).css('display','block');
}

function changeImageCategory(value)
{		

	$.get("imagelist.php?id="+value+"&user="+$('#user_id').html(), function(data) {
		var s = "<legend>Select the stock graphic to use for your "+$('#productName').html()+"</legend>";
		var object = jQuery.parseJSON(data);
		for(var i=0;i<object.length;i++)
		{
			s += '<img onClick="selectImage(this,'+object[i]+')" src="image.php?id='+object[i]+'&color=black" width="100" />';
		}
		$('#selectimageblock').html(s);
	});
}

function changeTemplateCategory(value){
	$.get("templatelist.php?id="+value+"&user="+$('#user_id').html(), function(data) {
		var s = "<legend>Select the template to use for your "+$('#productName').html()+"</legend>";
		var object = jQuery.parseJSON(data);
		for(var i=0;i<object.length;i++)
		{
			s += '<img onClick="selectTemplate(this,'+object[i]+')" src="gettemplateimage.php?id='+object[i]+'&color=black" width="100"/>';
		}
		$('#selecttemplateblock').html(s);
	});	
}

function selectImage(element,image_id){
	$('#selectimageblock img').removeClass('imagelistselected');
	$(element).addClass('imagelistselected');
	selectedImage = image_id;
}

function selectTemplate(element,template_id){
	$('#selecttemplateblock img').removeClass('imagelistselected');
	$(element).addClass('imagelistselected');
	selectedTemplate = template_id;
}

function uploadGraphicCheck(){
	var file = document.getElementById('uploadGraphicFile').files[0];
	if(file){
		
	}
}

function uploadGraphicSubmit(){
  var xhr = new XMLHttpRequest();
  //alert("Uploading");
  //var form = document.getElementById('uploadGraphicForm');
  var fd = new FormData();
   fd.append("uploadGraphic", document.getElementById('uploadGraphicFile').files[0]);
  
  /* event listners */
  //xhr.upload.addEventListener("progress", uploadProgress, false);
  xhr.addEventListener("load", uploadGraphicComplete, false);
  //xhr.addEventListener("error", uploadFailed, false);
  //xhr.addEventListener("abort", uploadCanceled, false);
  /* Be sure to change the url below to the url of your upload server side script */
  xhr.open("POST", "uploadgraphic.php");
  xhr.send(fd);
}

function uploadGraphicComplete(){
	changeImageCategory(1);
	showTab('#selectgraphictab');
}

function changeTemplate(){
	if(selectedTemplate!=-1)
	{
		eraseAllElements();
		var xmlhttp;
		if (window.XMLHttpRequest)
		  {// code for IE7+, Firefox, Chrome, Opera, Safari
		  	xmlhttp=new XMLHttpRequest();
		}
		else
		  {// code for IE6, IE5
		  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		var url = "gettemplate.php?id="+selectedTemplate;
		xmlhttp.open("GET",url,false);
		xmlhttp.send();
		var response = xmlhttp.responseText;
		parseJSON(response);
		invalidate(true);
		closePopup('#addtemplatepopup');
	}
	
}

function changeColor(){
	closePopup('#addcolourpopup');
	textColor = $('#color').val();
	for(var i=0;i<images.length;i++){
		var image = new Image();
		image.index = i;
		image.onload = function(){
			images[this.index].image = this;
			invalidate(false);
		}
	image.src = "image.php?id="+images[i].image_id+"&color="+textColor;
	}
	invalidate(true);
}


function newObject(type, ref){
	var index = objects.length;
	objects[index] = {
			type : type,
			id : ref.id
		};
	var s = '<tr class="object"><td width="40%"><span onClick="selectObject(\''+type+'\','+ref.id+')">'+type.toUpperCase()+'</span></td><td><span class="button objectbutton" onClick="selectObject(\''+type+'\','+ref.id+')">Select</span></td><td><span class="button objectbutton" onClick="editObject(\''+type+'\','+ref.id+')">Edit</span></td><td><span class="button objectbutton" onClick="deleteObject(\''+type+'\','+ref.id+',this)">Delete</span></td></tr>'
	$('#objects').append(s);	
}


function getObjectIndexById(a, id)
{
	for(var i=0; i<a.length; i++) if(a[i].id == id) return i;
	return -1;
}

function getObjectArrayByType(type)
{
	if(type=="image") return images;
	if(type=="border") return borders;
	if(type=="line") return lines;
	if(type=="table") return tables;
	return null;
}

function editObject(type,id)
{	
}



function selectObject(type,id)
{
	var a = getObjectArrayByType(type);
	if(a == null) return false;
	
	var i = getObjectIndexById(a, id);
	if(i < 0) return false;
	
	mySel = a[i];
	invalidate(false);
	return true;
}

function deleteObject(type,id,element)
{
	if(type=="image"){
		deleteGraphic(getObjectIndexById(id));
	}
	if(type=="border"){
		deleteBorder(getObjectIndexById(id));
	}
	if(type=="line"){
		deleteLine(getObjectIndexById(id));
	}
	if(type == "table"){
		deleteTable(getObjectIndexById(id));
	}
	$(element.parentNode.parentNode).remove();
	for(var i=0; i<objects.length; i++)
	{
		if(objects[i].id == id) objects.splice(i,1);
	}	
	_stateHistory.save();
	invalidate(true);
}

function setSave(save){
	toSave = save;	
}

function getStateAsJSON()
{	
	var getState = function(o)
	{
		if(o.getState) return o.getState();
		return o;		
	};
	
	var getStateArray = function(a)
	{
		if(a == null) return null;
		
		var r = [];
		for(var i=0; i<a.length; i++)
		{
			if(a[i] != null)
			{
				if(a[i].getState) r.push(a[i].getState());
				else r.push(a[i]);
			}
			else r.push(null);
		}
		return r;
	};
		
	return $.toJSON({
		//currentZoomLevel : currentZoomLevel,
		orderitem_id :	parseInt($('#orderitem_id').html().trim()),
		textColor :		textColor,
		textLines :		getStateArray(textLines),
		images :		getStateArray(images),
		borders :		getStateArray(borders),
		lines :			getStateArray(lines),
		tables :		getStateArray(tables),
		objectIdSeed : _objectIdSeed
	});
}

function saveDesign(){
	if(toSave){
		
		$.post("_design_save.php", { s: getStateAsJSON() } );		
		toSave = false;		
	}
	
}
