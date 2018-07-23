var numLines = 0;
var currentZoomLevel = 100;
var gridOn = false;
var INTERVAL = 20; //how often we check for a re-draw
var SAVE_INTERVAL = 1000; //how often we check if we need to save
var WIDTH; 		   //the width and height of the canvas.
var HEIGHT;
var canvasOffsetX;
var canvasOffsetY;
var browserName=navigator.appName;  //necessary to take IE into account.

//need to keep track of all of the textLines to be drawn
var textLines = [];
var textColor = "black";
var shape = "Rectangular";
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


// Padding and border style widths for mouse offsets
var stylePaddingLeft, stylePaddingTop, styleBorderLeft, styleBorderTop;

var selectedImage = -1;
var selectedTemplate = -1;

//the object that each text line is stored in.
TextLine = function(){
	this.id = -1;
	this.text = "";
	this.fontsize = 11;
	this.fontfamily = "Arial";
	this.bold = 0;
	this.italic = 0;
	this.underline = 0;
	this.x = 0;
	this.y = 0;
	this.align = "left";
	this.show = true;
	this.type = "Straight";
	this.radius = 0;
	this.lock = false;
	this.toJson = function(){
		var s = '{"id": '+this.id+', "text": "'+this.text+'", "fontsize": '+this.fontsize+', "font": "'+this.fontfamily+'", "x": '+this.x+', "y": '+this.y+', "bold": '+this.bold+', "italic": '+this.italic+',"underline": '+this.underline+',"type": "'+this.type+'", "radius": '+this.radius+', "align": "'+this.align+'"}';
		return s;
	}
}

//the object that represents the logo

Logo = function(){
	this.image;
	this.id = -1;
	this.image_id = -1;
	this.x = 0;
	this.y = 0;
	this.width = 100;
	this.height = 100;
	this.show = true;
	this.lock = false;
	this.toJson = function(){
		var s = '{"id": '+this.id+', "image_id": '+this.image_id+', "x": '+this.x+', "y": '+this.y+', "width": '+this.width+', "height": '+this.height+'}';
		return s;
		
	}

}

Border = function(){
	this.id = -1;
	this.x = 0;
	this.y = 0;
	this.width=100;
	this.height = 100;
	this.style_id = 0;
	this.type_id = 0;
	this.line_width = 2;
	this.sides = "1111";
	this.radius = 1;
	this.show = true;
	this.lock = false;
	this.toJson = function(){
		var s = '{"id": '+this.id+', "x": '+this.x+', "y": '+this.y+', "width": '+this.width+', "height": '+this.height+', "type_id": "'+this.type_id+'", "style_id": '+this.style_id+', "line_width": '+this.line_width+', "sides": "'+this.sides+'", "radius": '+this.radius+'}';
		return s;
		
	}
}

Line = function(){
	this.id = -1;
	this.x = 10;
	this.y = 10;
	this.x2 = 100;
	this.y2 = 100;
	this.type_id = 1;
	this.line_width = 2;
	this.show = true;
	this.lock = false;
	this.toJson = function(){
		var s = '{"id": '+this.id+', "x": '+this.x+', "y": '+this.y+', "x2": '+this.x2+', "y2": '+this.y2+', "type_id": '+this.type_id+', "line_width": '+this.line_width+'}';
		return s;
		
	}
}

Table = function(){
	this.id = -1;
	this.x = 10;
	this.y = 10;
	this.width = 100;
	this.height = 100;
	this.border = 1;
	this.columns = 1;
	this.rows = 1;
	this.show = true;
	this.lock = false;
	this.toJson = function(){
		var s = '{"id": '+this.id+', "x": '+this.x+', "y": '+this.y+', "width": '+this.width+', "height": '+this.height+', "border": '+this.border+', "columns": '+this.columns+', "rows": '+this.rows+'}';
		return s;
	}
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
 	var spacing = 0.25*textLine.fontsize;
 	var width = metric.width+(text.length-1)*spacing;
 	var radius  = textLine.radius;//metric.width/2;
 	var circumference = 2*Math.PI*radius;
 	var ratio = width / circumference;
 	var each_side = ratio * Math.PI;
	var flip = (!flip ? false : true);
    //var rotation_end = Math.PI/2;
    //var rotation_start = -Math.PI/2;
    var rotation_end = each_side;
    var rotation_start = -each_side;
    var coeff = 1;
    if(textLine.type=="Curved Up"){
    	rotation_end = -each_side;//3*Math.PI/2+each_side;
    	rotation_start  = each_side;//3*Math.PI/2-each_side;
    	coeff = -1;
    }
    if(textLine.type=="Full Circle"){
    	rotation_end = -2*Math.PI/2;
    	rotation_start = 2*Math.PI/2;
    	coeff = -1;
    }
    /*if(textLine.type=="Curved Up"){
    	var t = rotation_start;
    	rotation_start = rotation_end;
    	rotation_end = t;
    }*/
    var total_radians = rotation_end - rotation_start;
    var piece_radians = coeff*(flip ? 1 : -1) * total_radians / (text.length);
    //console.log(piece_radians);
    context.save();
    context.translate(textLine.x, textLine.y);
    context.rotate(rotation_start);

    for(var l = 0; l < text.length; l++){
        context.fillText(text[l], 0, (flip ? -1 : 1) * radius);
        var piece_radians = (coeff*(flip ? 1 : -1) * total_radians) * ((context.measureText(text[l]).width+spacing)/width);
        //console.log(context.measureText(text[l]));
        context.rotate(piece_radians);
    }
    
    context.restore();
	
}

//draws the text line to the canvas
drawTextLine = function(textLine,context){
	if(textLine.show){
		
		//textLine.x *=(currentZoomLevel/100);
		//textLine.y *=(currentZoomLevel/100);
		//textLine.fontsize *=(currentZoomLevel/100);
		//textLine.radius *=(currentZoomLevel/100);
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
		if(textLine.type == "Straight"){
			context.textAlign = textLine.align;
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
	
}


//returns a Box object representing a bounding box for a textLine
getBoundingBox = function(textLine){
	context.font = textLine.fontstyle+" "+textLine.fontsize+"pt "+textLine.fontfamily;
	var metric = context.measureText(textLine.text);
	metric.height = textLine.fontsize*1.5;
	var box = new Box;
	var ghostcanvas = document.createElement('canvas');
	ghostcanvas.width = canvas.width;
	ghostcanvas.height = canvas.height;
	var gcxt = ghostcanvas.getContext("2d");
	gcxt.beginPath();
    gcxt.rect(0, 0, canvas.width, canvas.height);
    gcxt.fillStyle = "#ffffff";
    gcxt.fill();
    gcxt.translate(canvasOffsetX,canvasOffsetY);
    //console.log("offset: ("+canvasOffsetX+","+canvasOffsetY+")");
    var minX=canvas.width,maxX=0,minY=canvas.height,maxY=0;
    gcxt.fillStyle = "#000000";
    gcxt.strokeStyle = "#000000";
    drawTextLine(textLine,gcxt);
    //gcxt.fillRect(0,0,1,1);
    var data = gcxt.getImageData(0,0,canvas.width,canvas.height).data;
    for(var i=0;i<data.length;i+=4){		
		//console.log(data[0]+" "+data[1]+" "+data[2]);
		if(data[i] == 0 && data[i+1] == 0 && data[i+2] == 0){
			//console.log(i);
			var index = i/4;
			var x = index%canvas.height;
			var y = Math.floor(index/canvas.height);
			//console.log(index+" ("+x+","+y+")");
			if(x < minX) minX = x;
			if(x > maxX) maxX = x;
			if(y < minY) minY = y;
			if(y > maxY) maxY = y;
		}
    	
    }
    //console.log(textLine.x+" "+metric.width);
    box.x = minX-canvasOffsetX-2;
    box.y = minY-canvasOffsetY-2;
    box.width = maxX - minX+5;
    box.height = maxY - minY+5;
    //console.log(minX+" "+minY+" "+maxX+" "+maxY);
	//console.log(box.x+" "+box.y+" "+box.width+" "+box.height);
	/*if(textLine.type=="Straight"){
		box.x = textLine.x;
		box.y = textLine.y-metric.height*0.8;
		box.width = metric.width;
		box.height = metric.height;
	}
	//context.strokeRect(textLine.x-metric.height,textLine.y-metric.height,radius*2+metric.height*2,radius+metric.height);
	else if(textLine.type=="Curved Down"){
		box.x = textLine.x-metric.height-textLine.radius;
		box.y = textLine.y-textLine.radius-metric.height;
		box.width = textLine.radius*2+metric.height*2;//metric.width+metric.height*2;
		box.height = textLine.radius*2+metric.height*2;//textLine.radius*1+1*metric.height;//metric.width/2+metric.height;
	}
	else if(textLine.type=="Curved Up"){
		box.x = textLine.x-metric.height-textLine.radius;
		box.y = textLine.y-textLine.radius;
		box.width = textLine.radius*2+metric.height*2;//metric.width+metric.height*2;
		box.height = textLine.radius*2+metric.height*2;//textLine.radius*1+1*metric.height;//metric.width/2+metric.height;
	}
	else if(textLine.type=="Full Circle"){
		box.x = textLine.x-metric.height;
		box.y = textLine.y-metric.height;
		box.width = textLine.radius*2+metric.height*2;//metric.width+metric.height*2;
		box.height = textLine.radius*2+metric.height*2;//metric.width/2+metric.height;
	}*/
	return box;
}


//draws a box around where a text line would be. This is used to determine if the user has clicked on a text line.
drawBoundingBox = function(textLine,context){
	var box = getBoundingBox(textLine);
	context.strokeStyle = "#000000";
	context.lineWidth =1;
	//console.log('Box Dimensions: ('+box.x+','+box.y+'),('+(box.x+box.width)+','+(box.y+box.height)+')');
	context.strokeRect(box.x,box.y,box.width,box.height);
	if(textLine.type == "Straight"){
		if(textLine.align == "left"){
			context.beginPath();
			context.moveTo(box.x,box.y-5)
			context.lineTo(box.x,box.y+box.height+5);
			context.stroke();
		}
		else if(textLine.align == "center"){
			context.beginPath();
			context.moveTo(box.x+box.width/2,box.y-5);
			context.lineTo(box.x+box.width/2,box.y+box.height+5);
			context.stroke();
		}
		else if(textLine.align == "right"){
			context.beginPath();
			context.moveTo(box.x+box.width,box.y-5);
			context.lineTo(box.x+box.width,box.y+box.height+5);
			context.stroke();
		}
	}
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
	if(image.image&&image.show){
		context.drawImage(image.image,image.x,image.y,image.width,image.height);
	}
	
}
 var _mapParams;
 var _map;
 var _selectedPattern;
 var _patternSize = 10;
 var _patternSpacingScale = 1;
 var count = 0;
function drawBorder(border,context){
	if(border.show){
		context.strokeStyle = textColor;
		context.fillStyle = textColor;
		var radius = border.radius;
		if(border.type_id == "Rectangular"){
			radius = 0;
		}
		else if(border.type_id == "Circular"){
			radius = border.width;
		}
		//console.log("|"+border.type_id+"|");
		_mapParams = { x1:border.x, y1:border.y, x2:border.x+border.width, y2:border.y+border.height, r:radius, s:0.8 };
		_map = roundedRectangleMap(
          _mapParams.x1, 
          _mapParams.y1, 
          _mapParams.x2, 
          _mapParams.y2, 
          _mapParams.r,
          _mapParams.s
          );
          var style = border.style_id;
          if(style == 1){
      		_selectedPattern = new PatternRope();
      		_patternSize = 10+parseInt(border.line_width);
      	}else if(style == 2){
      		_selectedPattern = new PatternHash();
      		_patternSize = 10+parseInt(border.line_width);
      	}
      	else if(style == 0){
      		_selectedPattern = new PatternLines([{distance: 0, size: 1, corner:'edge'}]); 
      		_patternSize = border.line_width;
      	}
      	else if(style == 3){
      		_selectedPattern = new PatternStars();
      		_patternSize = 10+parseInt(border.line_width);
      	}
      	count++;
		context.save();        
        if(_map instanceof MapCollection)
        {
          for(var i=0; i<_map.maps.length; i++)
          {
            _selectedPattern.border(_map.maps[i], context, _patternSize, _patternSpacingScale);            
          }
          for(var i=0; i<_map.corners.length; i++)
          {
            _selectedPattern.corner(_map.corners[i], context, _patternSize, _patternSpacingScale);            
          }
        }
        else
        {          
          _selectedPattern.border(_map, context, _patternSize, _patternSpacingScale);
        }
        context.restore();
	}
}

function drawLine(line,context){
	context.strokeStyle = textColor;
	//context.drawImage(context.image,0,0,WIDTH,HEIGHT);
	if(line.show){
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
	
}

function drawTable(table,context){
	context.strokeStyle = textColor;
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
	
}

function drawGrid(){
	//context.translate(0,0);
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
	
	context.strokeStyle = "#b9b9b9";
	context.lineWidth =1;
	var i=canvas.width/2;	
		//context.beginPath();
		context.moveTo(i,0);
		context.lineTo(i,canvas.height);
		context.stroke();
	var i=canvas.height/2;
		//context.beginPath();
		context.moveTo(0,i);
		context.lineTo(canvas.width,i);
		context.stroke();
}


function drawToGhost(){
	var context = gcxt;
	//clear(context);
	context.beginPath();
    context.rect(0, 0, WIDTH, HEIGHT);
    context.fillStyle = "#ffffff";
    context.fill();
    context.fillStyle = textColor;
	context.strokeStyle = textColor;
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
		count = 0;
		context.save();
		//clear(context);
		context.fillStyle = "#689ac6";
		context.fillRect(0, 0, canvas.width, canvas.height);
		
		context.translate(canvasOffsetX,canvasOffsetY);
        context.fillStyle = "#ffffff";
        if(shape=="Rectangular"){
        	context.fillRect(0, 0, WIDTH, HEIGHT);
        }
        else if(shape=="Circular"){
        	context.beginPath();
        	context.arc(WIDTH/2,HEIGHT/2,Math.min(WIDTH/2,HEIGHT/2),0,2*Math.PI,false);
        	//context.endPath();
        	context.fill();
        }
		context.restore();
		if(gridOn){
			drawGrid();
		}
		context.fillStyle = textColor;
		context.strokeStyle = textColor;
		context.save();
		context.translate(canvasOffsetX,canvasOffsetY);
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
			context.fillStyle = textColor;
				context.strokeStyle = textColor;
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
			/*context.fillStyle = "blue";
			context.beginPath();
			context.arc(textLines[i].x,textLines[i].y,5,0,2*Math.PI,false);
			context.fill();*/
		}
		context.fillStyle = textColor;
		context.strokeStyle = textColor;
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
  var scrollLeft = (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
  var scrollTop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
  offsetX -= scrollLeft;
  offsetY -= scrollTop;
  mx = e.clientX - offsetX-canvasOffsetX;
  my = e.clientY - offsetY-canvasOffsetY;
  //console.log(scrollLeft +" "+mx);
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
	 		/*if(mySel.x > mySel.x2){
	 			var temp = mySel.x;
	 			mySel.x = mySel.x2;
	 			mySel.x2 = mySel.x;
	 		}
	 		if(mySel.y > mySel.y2){
	 			var temp = mySel.y;
	 			mySel.y = mySel.y2;
	 			mySel.y2 = mySel.y;
	 		}*/	
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
        invalidate(false);
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
	if(textLines[i].show&&!textLines[i].lock){
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

  }
  //check to see if they have selected the logo
  clear(gcxt);
 	for (var i = images.length-1; i >= 0; i--) {
 		if(images[i].show&&!images[i].lock){
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
	}
	
	for(var i=0;i<lines.length;i++){
		if(lines[i].show&&!lines[i].lock){
			var minX = Math.min(lines[i].x,lines[i].x2);
			var minY = Math.min(lines[i].y,lines[i].y2);
			var maxX = Math.max(lines[i].x,lines[i].x2);
			var maxY = Math.max(lines[i].y,lines[i].y2);
			if(mx>=minX&&mx<=maxX+parseInt(lines[i].line_width)&&my>=minY&&my<=maxY+parseInt(lines[i].line_width)){
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
	}
	
	
	for(var i=0;i<borders.length; i++){
		if(borders[i].show&&!borders[i].lock){
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
	}
	
	for(var i=0;i<tables.length; i++){
		if(tables[i].show&&!tables[i].lock){
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

function parseJSON(s){
	var count = 0;
	var object = jQuery.parseJSON(s);
	for(var i=0;i<object.textlines.length;i++){
		var line = object.textlines[i]; 
		var textLine = new TextLine();
		textLine = new TextLine();
		textLine.id = line.id;
		textLine.text = line.text;
		textLine.x = line.x;
		textLine.y = line.y;
		if(textLine.x < 1) textLine.x = textLine.x*WIDTH;
		if(textLine.y < 1) textLine.y = textLine.y*HEIGHT;
		textLine.fontfamily = line.font;
		textLine.fontsize = line.fontsize;
		textLine.bold = line.bold;
		textLine.italic = line.italic;
		textLine.underline = line.underline;
		textLine.type = line.type;
		textLine.show = true;
		textLine.radius = line.radius;
		textLine.align = line.align;
		textLines[numLines] = textLine;
		if(textLine.id == -1){
			newTextLineServerCall(numLines);
		}
		addTextLine(numLines,numLines+1);
		numLines++;
		count++;
	}
	
    //s = $("#imagesdb").html();
    //object = jQuery.parseJSON(s);
    for(var i=0;i<object.images.length;i++){
    	var line = object.images[i];
    	 
		images[i] = new Logo();
		if(line.width < 1){
			line.x = line.x * WIDTH;
			line.y = line.y * HEIGHT;
			line.width = line.width * WIDTH;
			line.height = line.height * HEIGHT;
		}
		
		var image = new Image();
		image.index = i;
		image.line = line;
		image.onload = function(){
			images[this.index].image = this;
			
			images[this.index].x = 1*this.line.x;
			images[this.index].y = 1*this.line.y;
			
			images[this.index].width = 1*this.line.width;
			images[this.index].height = 1*this.line.height;
			images[this.index].id = this.line.id;
			images[this.index].image_id = this.line.image_id;
			if(this.line.id == -1){
				newGraphicServerCall(this.index,this.line.image_id);
			}
			count++;
			invalidate(false);
		}
	image.src = "../image.php?id="+line.image_id+"&color="+textColor;
	newObject("image",i);
	}
	  
	for(var i=0;i<object.borders.length;i++){
		var b = object.borders[i];
		var border = new Border();
		border.id = b.id;
		if(b.width < 1){
			b.x = b.x * WIDTH;
			b.y = b.y * HEIGHT;
			b.width = b.width * WIDTH;
			b.height = b.height * HEIGHT;
		}
		border.x = b.x;
		border.y = b.y;
		border.width = 1* b.width;
		border.height = 1*b.height;
		border.type_id = b.type_id;
		if(border.type_id == 0){
			border.type_id = "Rectangular";
		}
		border.style_id = b.style_id;
		border.line_width = b.line_width;
		border.radius = b.radius;
		if(border.radius <= 0){
			border.radius = 1;
		}
		border.sides = b.sides;
		borders[i] = border;
		if(borders[i].id == -1){
			newBorderServerCall(i,border);
		}
		newObject("border",i);	
		count++;
	}
	
	for(var i=0;i<object.lines.length;i++){
		var l = object.lines[i];
		var line1 = new Line();
		if(l.width < 1){
			l.x = l.x * WIDTH;
			l.y = l.y * HEIGHT;
			l.x2 = l.x2 * WIDTH;
			l.y2 = l.y2 * HEIGHT;
		}
		line1.id = l.id;
		line1.x = l.x;
		line1.y = l.y;
		line1.x2 = l.x2;
		line1.y2 = l.y2;
		line1.type_id = l.type_id;
		line1.line_width = l.line_width;
		lines[i] = line1;
		if(lines[i].id == -1){
			newLineServerCall(i,line1);
		}
		newObject("line",i);
		count++;
	}
	
	for(var i=0;i<object.tables.length;i++){
		var t = object.tables[i];
		var table = new Table();
		if(t.width < 1){
			t.x = t.x * WIDTH;
			t.y = t.y * HEIGHT;
			t.width = t.width * WIDTH;
			t.height = t.height * HEIGHT;
		}
		table.id = t.id;
		table.x = t.x;
		table.y = t.y;
		table.width = t.width;
		table.height = t.height;
		table.rows = t.rows;
		table.columns = t.columns;
		table.border = t.border;
		tables[i] = table;
		if(tables[i].id == -1){
			newTableServerCall(i,table);
		}
		newObject("table",i);
		count++;
	}
	
	if(count == 0){
		showPopup('#addtemplatepopup');
	}
}

function get_html_translation_table (table, quote_style) {
    // http://kevin.vanzonneveld.net
    // +   original by: Philip Peterson
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: noname
    // +   bugfixed by: Alex
    // +   bugfixed by: Marco
    // +   bugfixed by: madipta
    // +   improved by: KELAN
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Frank Forte
    // +   bugfixed by: T.Wild
    // +      input by: Ratheous
    // %          note: It has been decided that we're not going to add global
    // %          note: dependencies to php.js, meaning the constants are not
    // %          note: real constants, but strings instead. Integers are also supported if someone
    // %          note: chooses to create the constants themselves.
    // *     example 1: get_html_translation_table('HTML_SPECIALCHARS');
    // *     returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}
    var entities = {},
        hash_map = {},
        decimal;
    var constMappingTable = {},
        constMappingQuoteStyle = {};
    var useTable = {},
        useQuoteStyle = {};

    // Translate arguments
    constMappingTable[0] = 'HTML_SPECIALCHARS';
    constMappingTable[1] = 'HTML_ENTITIES';
    constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
    constMappingQuoteStyle[2] = 'ENT_COMPAT';
    constMappingQuoteStyle[3] = 'ENT_QUOTES';

    useTable = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
    useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT';

    if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
        throw new Error("Table: " + useTable + ' not supported');
        // return false;
    }

    entities['38'] = '&amp;';
    if (useTable === 'HTML_ENTITIES') {
        entities['160'] = '&nbsp;';
        entities['161'] = '&iexcl;';
        entities['162'] = '&cent;';
        entities['163'] = '&pound;';
        entities['164'] = '&curren;';
        entities['165'] = '&yen;';
        entities['166'] = '&brvbar;';
        entities['167'] = '&sect;';
        entities['168'] = '&uml;';
        entities['169'] = '&copy;';
        entities['170'] = '&ordf;';
        entities['171'] = '&laquo;';
        entities['172'] = '&not;';
        entities['173'] = '&shy;';
        entities['174'] = '&reg;';
        entities['175'] = '&macr;';
        entities['176'] = '&deg;';
        entities['177'] = '&plusmn;';
        entities['178'] = '&sup2;';
        entities['179'] = '&sup3;';
        entities['180'] = '&acute;';
        entities['181'] = '&micro;';
        entities['182'] = '&para;';
        entities['183'] = '&middot;';
        entities['184'] = '&cedil;';
        entities['185'] = '&sup1;';
        entities['186'] = '&ordm;';
        entities['187'] = '&raquo;';
        entities['188'] = '&frac14;';
        entities['189'] = '&frac12;';
        entities['190'] = '&frac34;';
        entities['191'] = '&iquest;';
        entities['192'] = '&Agrave;';
        entities['193'] = '&Aacute;';
        entities['194'] = '&Acirc;';
        entities['195'] = '&Atilde;';
        entities['196'] = '&Auml;';
        entities['197'] = '&Aring;';
        entities['198'] = '&AElig;';
        entities['199'] = '&Ccedil;';
        entities['200'] = '&Egrave;';
        entities['201'] = '&Eacute;';
        entities['202'] = '&Ecirc;';
        entities['203'] = '&Euml;';
        entities['204'] = '&Igrave;';
        entities['205'] = '&Iacute;';
        entities['206'] = '&Icirc;';
        entities['207'] = '&Iuml;';
        entities['208'] = '&ETH;';
        entities['209'] = '&Ntilde;';
        entities['210'] = '&Ograve;';
        entities['211'] = '&Oacute;';
        entities['212'] = '&Ocirc;';
        entities['213'] = '&Otilde;';
        entities['214'] = '&Ouml;';
        entities['215'] = '&times;';
        entities['216'] = '&Oslash;';
        entities['217'] = '&Ugrave;';
        entities['218'] = '&Uacute;';
        entities['219'] = '&Ucirc;';
        entities['220'] = '&Uuml;';
        entities['221'] = '&Yacute;';
        entities['222'] = '&THORN;';
        entities['223'] = '&szlig;';
        entities['224'] = '&agrave;';
        entities['225'] = '&aacute;';
        entities['226'] = '&acirc;';
        entities['227'] = '&atilde;';
        entities['228'] = '&auml;';
        entities['229'] = '&aring;';
        entities['230'] = '&aelig;';
        entities['231'] = '&ccedil;';
        entities['232'] = '&egrave;';
        entities['233'] = '&eacute;';
        entities['234'] = '&ecirc;';
        entities['235'] = '&euml;';
        entities['236'] = '&igrave;';
        entities['237'] = '&iacute;';
        entities['238'] = '&icirc;';
        entities['239'] = '&iuml;';
        entities['240'] = '&eth;';
        entities['241'] = '&ntilde;';
        entities['242'] = '&ograve;';
        entities['243'] = '&oacute;';
        entities['244'] = '&ocirc;';
        entities['245'] = '&otilde;';
        entities['246'] = '&ouml;';
        entities['247'] = '&divide;';
        entities['248'] = '&oslash;';
        entities['249'] = '&ugrave;';
        entities['250'] = '&uacute;';
        entities['251'] = '&ucirc;';
        entities['252'] = '&uuml;';
        entities['253'] = '&yacute;';
        entities['254'] = '&thorn;';
        entities['255'] = '&yuml;';
    }

    if (useQuoteStyle !== 'ENT_NOQUOTES') {
        entities['34'] = '&quot;';
    }
    if (useQuoteStyle === 'ENT_QUOTES') {
        entities['39'] = '&#39;';
    }
    entities['60'] = '&lt;';
    entities['62'] = '&gt;';


    // ascii decimals to real symbols
    for (decimal in entities) {
        if (entities.hasOwnProperty(decimal)) {
            hash_map[String.fromCharCode(decimal)] = entities[decimal];
        }
    }

    return hash_map;
}

function html_entity_decode (string, quote_style) {
    // http://kevin.vanzonneveld.net
    // +   original by: john (http://www.jd-tech.net)
    // +      input by: ger
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: marc andreu
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Ratheous
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Nick Kolosov (http://sammy.ru)
    // +   bugfixed by: Fox
    // -    depends on: get_html_translation_table
    // *     example 1: html_entity_decode('Kevin &amp; van Zonneveld');
    // *     returns 1: 'Kevin & van Zonneveld'
    // *     example 2: html_entity_decode('&amp;lt;');
    // *     returns 2: '&lt;'
    var hash_map = {},
        symbol = '',
        tmp_str = '',
        entity = '';
    tmp_str = string.toString();

    if (false === (hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    }

    // fix &amp; problem
    // http://phpjs.org/functions/get_html_translation_table:416#comment_97660
    delete(hash_map['&']);
    hash_map['&'] = '&amp;';

    for (symbol in hash_map) {
        entity = hash_map[symbol];
        tmp_str = tmp_str.split(entity).join(symbol);
    }
    tmp_str = tmp_str.split('&#039;').join("'");

    return tmp_str;
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
	WIDTH = $('#productwidth').html().replace(/^\s+|\s+$/g, '');//canvas.width;
	HEIGHT = $('#productheight').html().replace(/^\s+|\s+$/g, '');//canvas.height;
	//get the context from the canvas (which we need to draw to)
	canvasOffsetX = (canvas.width - WIDTH)/2;
	canvasOffsetY = (canvas.height - HEIGHT)/2;
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
	textColor = textColor.replace(/^\s+|\s+$/g, '');
	//console.log("TextColor: ("+textColor+")");
	shape = $('#productType').html().replace(/^\s+|\s+$/g, '');
	//var s = html_entity_decode($('#textlinesdb').html());
	//parseJSON(s);
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
	//setInterval(saveDesign,SAVE_INTERVAL);
	
	//add the events that will be taking care of the mouse clicks. 
	canvas.onmousedown = myDown;
	canvas.onmouseup = myUp;
	canvas.onmousemove = myMove;
	
	changeImageCategory(1);
	changeTemplateCategory(1);
	showTab("#selectgraphictab");
	
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
	if(level instanceof String){
		level = level.replace("%","");
	}
	currentZoomLevel = level;
	var currentValue = $('#currentzoom');
	currentValue.val(level+"%");
	
	var width = WIDTH*(level/100);
	var height = HEIGHT*(level/100);
	
	var surface = $("#surface");
	
	//surface.width(width);
	//surface.height(height);
	//canvasOffsetX = (canvas.width - width)/2;
	//canvasOffsetY = (canvas.height - height)/2;
	//invalidate();
}

function changeTextLine(lineNum,element){
	//alert(lineNum);
	textLines[lineNum].text = element.value.replace(/^\s+|\s+$/g, '');;
	mySel = textLines[lineNum];
	invalidate(true);
}

function changeTextLineType(lineNum,element){
	textLines[lineNum].type = element.value;
	var metric = context.measureText(textLines[lineNum].text);
	textLines[lineNum].radius = metric.width/2;
	mySel = textLines[lineNum];
	invalidate(true);
}

function changeTextLineFontSize(lineNum,element){
	textLines[lineNum].fontsize = element.value;
	mySel = textLines[lineNum];
	invalidate(true);
	
}

function changeTextLineAlign(lineNum,element,align){
	textLines[lineNum].align = align;
	mySel = textLines[lineNum];
	invalidate(true);
}

function changeTextLineFont(lineNum,element){
	//alert("Change font: "+lineNum+" to "+element.value);
	textLines[lineNum].fontfamily = element.value;
	mySel = textLines[lineNum];
	invalidate(true);
}

function changeTextLineFontStyle(lineNum,element,style){
	mySel = textLines[lineNum];
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
}

function addTextLine(index,count){
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
	var type = textLines[index].type;
	var line = '<tr><td align="center">'+count+'</td>';
	var typeLine = '<select onChange="changeTextLineType('+index+',this)"><option>Straight</option><option>Curved Down</option><option>Curved Up</option><option>Full Circle</option></select>';
	typeLine = typeLine.replace('>'+type,' selected>'+type);
	line+='<td align="center">'+typeLine+'</td>';
	//line+='<td width="10%" align="center"><input type="text" value="100" size="5"></td>';
	line+='<td><input type="text" size="40" onKeyUp="changeTextLine('+index+',this)" value="'+textLines[index].text+'"></td>';
	line+='<td align="center">'+fonts+'</td>';
	line+='<td align="center">'+fontsizes+'</td>';
	line+='<td align="center"><input type="checkbox" onChange="changeTextLineFontStyle('+index+',this,\'bold\')"';
	if(textLines[index].bold == 1){
		line += ' checked';
	}
	line +='></td><td align="center"><input type="checkbox" onChange="changeTextLineFontStyle('+index+',this,\'italic\')"';
	if(textLines[index].italic == 1){
		line+=' checked';
	}
	line+= '></td><td align="center">L <input type="radio" name="align'+numLines+'" onChange="changeTextLineAlign('+numLines+',this,\'left\')"';
	if(textLines[index].align == "left") line+=' checked="checked"';
	line+='> C <input type="radio" name="align'+numLines+'" onChange="changeTextLineAlign('+numLines+',this,\'center\')"';
	if(textLines[index].align == "center") line+=' checked="checked"';
	line+='> R <input type="radio" name="align'+numLines+'" onChange="changeTextLineAlign('+numLines+',this,\'right\')"';
	if(textLines[index].align == "right") line+=' checked="checked"';
	line+='></td>';
	line+='<td align="center"><div class="button objectbutton" width="10px"  onClick="selectTextLine('+index+')">Select</div></td>';
	line+='<td align="center"><div class="button objectbutton"  onClick="deleteTextLine('+index+')">Delete</div></td>';
	line+='<td align="center"><div class="button"><label>Lock</label><input name="lockpos" value="lockpos" type="checkbox" onclick="lockObject(\'textlines\','+index+',this)"></div></td></tr>';

	$('#textlineTable > tbody:last').append(line);
}

function newTextLine(){
	var fonts = $('#fontselect').html().replace("#LINENUM",""+numLines);;
	var fontsizes = $('#fontsizeselect').html().replace("#LINENUM",""+numLines);
	s = "<option>11";
	rep = "<option selected>11";
	fontsizes = fontsizes.replace(s,rep);
	var line = '<tr><td align="center">'+(numLines+1)+ '</td>';
	line+= '<td align="center"><select onChange="changeTextLineType('+numLines+',this)"><option selected>Straight</option><option>Curved Down</option><option>Curved Up</option><option>Full Circle</option></select></td>';
	//line+= '<td width="10%" align="center"><input type="text" value="100" size="5"></td>';
	line+= '<td><input type="text" size="40" onKeyUp="changeTextLine('+numLines+',this)" onChange="setSave(true)"></td>';
	line+= '<td align="center">'+fonts+'</td>	<td align="center">'+fontsizes+'</td>';
	line+= '<td align="center"><input type="checkbox" onChange="changeTextLineFontStyle('+numLines+',this,\'bold\')"></td>';
	line+= '<td align="center"><input type="checkbox" onChange="changeTextLineFontStyle('+numLines+',this,\'italic\')"></td>';
	line+= '<td align="center">L <input type="radio" name="align'+numLines+'" onChange="changeTextLineAlign('+numLines+',this,\'left\')" checked="checked"> C <input type="radio" name="align'+numLines+'" onChange="changeTextLineAlign('+numLines+',this,\'center\')"> R <input type="radio" name="align'+numLines+'" onChange="changeTextLineAlign('+numLines+',this,\'right\')"></td>';
	line+= '<td align="center"><div class="button objectbutton" width="10px" onClick="selectTextLine('+numLines+')">Select</div></td>';
	line+= '<td align="center"><div class="button objectbutton" onClick="deleteTextLine('+numLines+')">Delete</div></td>';
	line+= '<td align="center"><div class="button "><label>Lock</label><input name="lockpos" value="lockpos" type="checkbox" onclick="lockObject(\'textlines\','+numLines+',this)"></div></td></tr>';
	
	$('#textlineTable > tbody:last').append(line);
	textLines[numLines] = new TextLine();
	textLines[numLines].x = 85;
	textLines[numLines].y = 26;
	
	
	//add the new textline to the database and retrieve the id
	newTextLineServerCall(numLines);
	numLines++;
	
}

function newTextLineServerCall(index){
	
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
		if(this.width > WIDTH){
			this.height = WIDTH/this.width*this.height;
			this.width =WIDTH; 
		}
		if(this.height > HEIGHT){
			this.width = HEIGHT/this.height*this.width;
			this.height = HEIGHT;
		}
		images[this.index+1].width = this.width;
		images[this.index+1].height = this.height;
		//images[this.index+1].id = 3;
		//images[this.index+1].image_id = selectedImage;
		invalidate(false);
	}
	image.src = "../image.php?id="+selectedImage+"&color="+textColor;
	images[i+1].image_id = selectedImage;
	newGraphicServerCall(i+1,selectedImage);
	newObject("image",i+1);
	//alert(xmlhttp.responseText);
	selectedImage = -1;
	
}

function newGraphicServerCall(index,image_id){
	
}

function newBorder(){
	closePopup('#addborderpopup');
	
	var i = borders.length-1;
	borders[i+1] = new Border();
	borders[i+1].x = SelectedBorder.x;
	borders[i+1].y = SelectedBorder.y;
	borders[i+1].width = SelectedBorder.width;
	borders[i+1].height = SelectedBorder.height;
	
	borders[i+1].type_id = SelectedBorder.type_id;
	borders[i+1].style_id = SelectedBorder.style_id;
	borders[i+1].sides = SelectedBorder.sides;
	borders[i+1].radius = SelectedBorder.radius;
	borders[i+1].line_width = SelectedBorder.line_width;
	
	newBorderServerCall(i+1,SelectedBorder);
	newObject("border",i+1);
	//alert(xmlhttp.responseText);
	SelectedBorder = new Border();
	invalidate(false);
}

function newBorderServerCall(index,SelectedBorder){
	
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
	
	newLineServerCall(i+1,SelectedLine);
	newObject("line",i+1);
	//alert(xmlhttp.responseText);
	SelectedLine = new Line();
	invalidate(false);
}

function newLineServerCall(index,SelectedLine){
	
}

function selectTextLine(id)	{
	if(!textline[id].lock){
		mySel = textLines[id];
	}
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
	
	
	newTableServerCall(i+1,SelectedTable);
	newObject("table",i+1);
	//alert(xmlhttp.responseText);
	SelectedTable = new Table();
	invalidate(false);
}

function newTableServerCall(index,SelectedTable){
	
}

var bpmx;
var bpmy;
var bpwidth;
var bpheight;
var bcontext;

function borderPreviewDraw(){
	
	var context = bcontext;
	var border = SelectedBorder;
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
  if($('#borderFrame').is(":checked")){
  		var offset = parseInt(SelectedBorder.line_width)+10; 
		SelectedBorder.x = offset;
		SelectedBorder.y = offset;
		SelectedBorder.width = WIDTH-offset*2;
		SelectedBorder.height = HEIGHT-offset*2;
	} 
	clear(bcontext);
	bcontext.strokeStyle = textColor;
	bcontext.fillStyle = "white";
	bcontext.fillRect(0,0,WIDTH,HEIGHT);
	bcontext.fillStyle = textColor;
	//bcontext.drawImage(bcontext.image,0,0,WIDTH,HEIGHT);
	drawBorder(border,context);
	/*if(SelectedBorder.show){
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
	}*/
	
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
  var scrollLeft = (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
  var scrollTop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
  offsetX -= scrollLeft;
  offsetY -= scrollTop;
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
  var scrollLeft = (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
  var scrollTop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
  offsetX -= scrollLeft;
  offsetY -= scrollTop;
  bpwidth = e.clientX - offsetX - bpmx;
  bpheight = e.clientY - offsetY - bpmy;
  SelectedBorder.x = bpmx;
  SelectedBorder.y = bpmy;
  SelectedBorder.width = bpwidth;
  SelectedBorder.height = bpheight;
  SelectedBorder.show = true;
  borderPreviewDraw();
}

function initBorderPreview(){
	var bcanvas = document.getElementById("borderpreview");
	bcontext = bcanvas.getContext("2d");
	clear(bcontext);
	SelectedBorder.show = false;
	//image.selectedImage = selectedImage;
	
	borderPreviewDraw();
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
	//context.drawImage(context.image,0,0,WIDTH,HEIGHT);
	context.fillStyle = "white";
	context.fillRect(0,0,WIDTH,HEIGHT);
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
  var scrollLeft = (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
  var scrollTop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
  offsetX -= scrollLeft;
  offsetY -= scrollTop;
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
  var scrollLeft = (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
  var scrollTop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
  offsetX -= scrollLeft;
  offsetY -= scrollTop;
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
	SelectedLine.show = false;
	//image.selectedImage = selectedImage;
	linePreviewDraw();
	
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
  var scrollLeft = (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
  var scrollTop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
  offsetX -= scrollLeft;
  offsetY -= scrollTop;
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
  var scrollLeft = (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
  var scrollTop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
  offsetX -= scrollLeft;
  offsetY -= scrollTop;
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
	image.src = "../getproductimage.php?id="+$('#orderitem_id').html();
	
	canvas.onmousedown = tablePreviewMouseDown;
	canvas.onmouseup = tablePreviewMouseUp;
	//bcanvas.onmousemove = myMove;
}


function deleteTextLine(index){
	var line_id = textLines[index].id;
	textLines[index].show = false;
	if(mySel == textLines[index]){
		mySel = null;
	}
	
	removeAllTextLines();
	var count = 0;
	for(var i=0;i<textLines.length;i++){
		if(textLines[i].show){
			count++;
			addTextLine(i,count);
		}
	}
	
	invalidate(true);
}

function removeAllTextLines(){
	$('#textlineTable tbody tr').remove();
}

function deleteGraphic(index){
	var image_id = images[index].id;
	images[index].show = false;
	
	if(mySel == images[index]){
		mySel = null;
	}
	invalidate(true);
	
}

function deleteBorder(index){
	borders[index].show = false;
	
	if(mySel == borders[index]){
		mySel = null;
	}
	invalidate(true);
	
}

function deleteLine(index){
	lines[index].show = false;
	
	
	if(mySel == lines[index]){
		mySel = null;
	}
	invalidate(true);
	
}

function deleteTable(index){
	tables[index].show = false;
	
	if(mySel == tables[index]){
		mySel = null;
	}
	invalidate(true);
	
}

function eraseAllElements(){
	clear(context);
	for(var i=0;i<numLines;i++){
		deleteTextLine(i);
		
	}
	removeAllTextLines();
	numLines = 0;
	
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
	//console.log("Showing tab "+id);
	$('#selectgraphictab').css('display','none');
	$('#symboltab').css('display','none');
	$('#uploadgraphictab').css('display','none');
	if(id=="#selectgraphictab"){
		$('#uploadGraphicTabButton').removeClass('tab_button_selected');
		$('#selectSymbloTabButton').removeClass('tab_button_selected');
		$('#selectGraphicTabButton').removeClass('tab_button');
		$('#uploadGraphicTabButton').addClass('tab_button');
		$('#selectSymbolTabButton').addClass('tab_button');
		$('#selectGraphicTabButton').addClass('tab_button_selected');	
	}
	else if(id=="#uploadgraphictab"){
		$('#selectGraphicTabButton').removeClass('tab_button_selected');
		$('#selectSymbolTabButton').removeClass('tab_button_selected');
		$('#uploadGraphicTabButton').removeClass('tab_button');
		$('#selectGraphicTabButton').addClass('tab_button');
		$('#selectSymbolTabButton').addClass('tab_button');		
		$('#uploadGraphicTabButton').addClass('tab_button_selected');
	}
	
	
	$(id).css('display','block');
}

function changeImageCategory(value){
	//alert(element.value);
	
	var xmlhttp;
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  	xmlhttp=new XMLHttpRequest();
	}
	else
	  {// code for IE6, IE5
	  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var s = "<legend>Select the stock graphic to use for your "+$('#productName').html()+"</legend>";
	xmlhttp.open("GET","../imagelist.php?id="+value+"&user="+$('#user_id').html(),false);
	xmlhttp.send();
	var response = xmlhttp.responseText;
	var object = jQuery.parseJSON(response);
	for(var i=0;i<object.length;i++){
		s += '<img  class="librarygraphic" onClick="selectImage(this,'+object[i]+')" onDblClick="newGraphic()" src="../image.php?id='+object[i]+'&color=black" />';
		//$('#selectimageblock').add(s);
	}
	$('#selectimageblock').html(s);
}


function changeTemplateCategory(value){
	//alert(value);
	
	var xmlhttp;
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  	xmlhttp=new XMLHttpRequest();
	}
	else
	  {// code for IE6, IE5
	  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	var s = "<legend></legend>";
	var url = "../templatelist.php?id="+value+"&user="+$('#user_id').html();
	xmlhttp.open("GET",url,false);
	xmlhttp.send();
	var response = xmlhttp.responseText;
	var object = jQuery.parseJSON(response);
	for(var i=0;i<object.length;i++){
		s += '<img onClick="selectTemplate(this,'+object[i]+')" onDblClick="changeTemplate()" src="../gettemplateimage.php?id='+object[i]+'&color=black" width="200"/>&nbsp;&nbsp;';
		//$('#selectimageblock').add(s);
	}
	$('#selecttemplateblock').html(s);
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
  xhr.open("POST", "../uploadgraphic.php");
  xhr.send(fd);
}

function uploadGraphicComplete(){
	changeImageCategory(1);
	showTab('#selectgraphictab');
}

function changeTemplate(){
	if(selectedTemplate!=-1){
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
		var url = "../gettemplate.php?id="+selectedTemplate;
		xmlhttp.open("GET",url,false);
		xmlhttp.send();
		var response = xmlhttp.responseText;
		parseJSON(response);
		invalidate(true);
		closePopup('#addtemplatepopup');
	}
	
}

function changeColor(){
	textColor = $('#color').val();
	for(var i=0;i<images.length;i++){
		var image = new Image();
		image.index = i;
		image.onload = function(){
			images[this.index].image = this;
			invalidate(false);
		}
	image.src = "../image.php?id="+images[i].image_id+"&color="+textColor;
	}
	invalidate(true);
}


function newObject(type,id){
	var index = objects.length;
	objects[index] = [];
	objects[index][0] = type;
	objects[index][1] = id;
	var s = '<tr class="object"><td width="30%"><span onClick="selectObject(\''+type+'\','+id+')">'+type.toUpperCase()+"-"+(id+1)+'</span></td><td><span class="button objectbutton" onClick="selectObject(\''+type+'\','+id+')">Select</span></td><td><span title="Coming Soon" class="button objectbutton" onClick="editObject(\''+type+'\','+id+')">Edit</span></td><td><span class="button objectbutton" onClick="deleteObject(\''+type+'\','+id+',this)">Delete</span></td><td><span class="button" objectbutton><label>Lock</label><input name="lockpos" value="lockpos" type="checkbox" onclick="lockObject(\''+type+'\','+id+',this)"></span></td></tr>'
	// var s = '<div class="object"><span onClick="selectObject(\''+type+'\','+id+')">'+type.toUpperCase()+id+'</span>&nbsp;&nbsp;<span class="button" onClick="editObject(\''+type+'\','+id+')">Edit</span>&nbsp;&nbsp;<span class="button" onClick="deleteObject(\''+type+'\','+id+',this)">Delete</span></div>';
	$('#objects').append(s);	
}


function editObject(type,id){	
	
}

function lockObject(type,id,element){
	var lock = element.checked;
	if(type=="image"){
		if(mySel == images[id]) mySel = null;
		images[id].lock = lock;
	}
	else if(type=="border"){
		if(mySel == borders[id]) mySel = null;
		borders[id].lock = lock;
	}
	else if(type=="line"){
		if(mySel == lines[id]) mySel = null;
		lines[id].lock = lock;
	}
	else if(type=="table"){
		if(mySel == tables[id]) mySel = null;
		tables[id].lock = lock;
	}
	else if(type=="textlines"){
		if(mySel == textLines[id]) mySel = null;
		textLines[id].lock = lock;
	}
	invalidate(false);
}

function selectObject(type,id){
	if(type=="image"){
		if(!images[id].lock){
			mySel = images[id];	
		}
		
	}
	if(type=="border"){
		if(!borders[id].lock){
			mySel = borders[id];
		}
	}
	if(type=="line"){
		if(!lines[id].lock){
			mySel = lines[id];
		}
	}
	if(type=="table"){
		if(!tables[id].lock){
			mySel = tables[id];
		}
	}
	invalidate(false);
}

function deleteObject(type,id,element){
	if(type=="image"){
		deleteGraphic(id);
	}
	if(type=="border"){
		deleteBorder(id);
	}
	if(type=="line"){
		deleteLine(id);
	}
	if(type == "table"){
		deleteTable(id);
	}
	$(element.parentNode.parentNode).remove();
	invalidate(true);
}

function setSave(save){
	toSave = save;
	
}


/*****************************************************************************************************************************************
 * 
 * 
 * 				BORDERS
 * 
 *****************************************************************************************************************************************/

 function Border()
  {
    
  }
  
  function LineMap( destX1, destY1, destX2, destY2, srcX )
  {
  	this.name = "LineMap";
    var width = destX2 - destX1;
    var height = destY2 - destY1;
    var len = Math.sqrt( width * width + height * height );
    var angle = Math.atan2( height, width );
    
    var ca = Math.cos(angle);
    var sa = Math.sin(angle);
    
    this.range = [srcX, srcX + len]; 
            
    this.transform = function(x ,y)
    {
      x = x - srcX;
                
      return [
        x * ca + y * sa + destX1 ,  
        x * sa - y * ca + destY1 ,
        angle,
      ];
    };
  }

  function CornerMap(mapA, mapB)
  {
  	this.name = "CornerMap";
    var v1 = mapB.transform(mapB.range[0], 0);        
    var v2 = mapB.transform(mapB.range[0], 100);
    var angle = Math.atan2( v2[1] - v1[1], v2[0] - v1[0] );
    
    var ca = Math.cos(angle);
    var sa = Math.sin(angle);
    
    this.range = [0, 0]; 
            
    this.transform = function(x ,y)
    {
      return [
        x * ca + y * sa + v1[0] ,  
        x * sa - y * ca + v1[1] ,
        angle,
      ];
    };
  }
  
  function CircleMap( centerX, centerY, angleStart, angleEnd, radius, srcX, srcScale )
  {
  	this.name = "CircleMap";
  	//angleEnd+=Math.PI;
   // angleStart+=Math.PI;
    var len = Math.abs(angleEnd - angleStart) * radius / srcScale;
    this.range = [srcX, srcX + len];
    
    this.transform = function( x, y )
    {
      var a = (x - srcX) * srcScale / radius + angleStart;
      var h = y + radius;
      return [
        h * Math.cos(a) + centerX ,
        h * Math.sin(a) + centerY ,
        a + Math.PI / 2
      ];
    };      
  }

  function CompositeMap()
  {
  	this.name = "CompositeMap";
    this.range = [0, 0];

    var maps = [];
    var selected = null;
    
    this.addMap = function(map)
    {
      maps.push(map);          
      this.range[1] = map.range[1] 
      if(selected == null) selected = map;
    };

    this.transform = function(x, y)
    {
      var x = x - Math.floor(x / this.range[1]) * this.range[1];
     // console.log("Transforming "+selected.name);
      if((selected.range[0] > x) || (selected.range[1] <= x))
      {
        var a = 0; 
        var b = maps.length - 1;
                    
        while(true)
        {
          var c = (b + a) >> 1;
          selected = maps[c];
                 
          if(selected.range[0] > x)
          {
            if(c == a) break;
            b = c - 1;                 
          }
          else if(selected.range[1] <= x)
          {
            if(c == b) break;
            a = c + 1;                                 
          }
          else break;
        }             
      }
      return selected.transform(x ,y);                
    };
  }

  function MapCollection()
  {
    var canAddCorner = false;
    var cornerUpdateNeeded = false;
    
    this.range = [0, 0];
    this.maps = [];
    this.corners = [];
                    
    this.addMap = function(map)
    {
      if(cornerUpdateNeeded)
      {
        this.corners[this.corners.length - 1] = new CornerMap(
          this.maps[this.maps.length - 1], map
        );
        cornerUpdateNeeded = false;
      }
      this.maps.push(map);
      canAddCorner = true;
    };
    
    this.addCorner = function()
    {
      if(!canAddCorner) return;          
      this.corners.push( new CornerMap(
          this.maps[this.maps.length - 1], this.maps[0]
        ));
      cornerUpdateNeeded = true;
      canAddCorner = false;
    };
  }
  
  function roundedRectangleMap(x1, y1, x2, y2, radius, srcScale)
  {
  	this.name = "RoundedRectangleMap";
    if(radius < 0) radius = 0;
    if(x1 > x2) { var tmp = x2; x2 = x1; x1 = tmp; }
    if(y1 > y2) { var tmp = y2; y2 = y1; y1 = tmp; }
    
    var width = x2 - x1; 
    var height = y2 - y1;        
    var diameter = radius * 2;
    
    if(width < diameter) diameter = width;
    if(height < diameter) diameter = height;
    
    width = width - diameter;
    height = height - diameter;
    
    radius = diameter / 2;
    
    var map = (radius > 0) ? new CompositeMap() : new MapCollection();
    
    
    
    //top line segment
    if(width > 0)
    {
      map.addMap(new LineMap(
        x1 + radius, y1,
        x2 - radius, y1, 
        map.range[1] ));
    }        

    //top right corner
    if(radius > 0)
    {
    	//console.log("raduis = "+radius);
      map.addMap(new CircleMap(
        x2 - radius, 
        y1 + radius,
        Math.PI * 3/2, 2 * Math.PI,
        radius,
        map.range[1],
        srcScale ));
    }
    else map.addCorner();

    //right line segment
    if(height > 0)
    {
      map.addMap(new LineMap(
        x2, y1 + radius, 
        x2, y2 - radius, 
        map.range[1] ));
    }        

    //bottom right corner
    if(radius > 0)
    {
      map.addMap(new CircleMap(
        x2 - radius, 
        y2 - radius,
        0, Math.PI * 1/2,
        radius,
        map.range[1],
        srcScale ));
    }
    else map.addCorner(); 

    //bottom line segment
    if(width > 0)
    {
      map.addMap(new LineMap(
        x2 - radius, y2, 
        x1 + radius, y2,
        map.range[1] ));
    }        

    //bottom left corner
    if(radius > 0)
    {
      map.addMap(new CircleMap(
        x1 + radius, 
        y2 - radius,
        Math.PI *  1/2, Math.PI,   
        radius,
        map.range[1],
        srcScale ));
    }
    else map.addCorner();

    //left line segment
    if(height > 0)
    {
      map.addMap(new LineMap(
        x1, y2 - radius, 
        x1, y1 + radius, 
        map.range[1] ));
    }        

    //top left corner
        if(radius > 0)
        {
          map.addMap(new CircleMap(
            x1 + radius, 
            y1 + radius,
            Math.PI, Math.PI * 3/2,   
            radius,
            map.range[1],
            srcScale ));
        }
        else map.addCorner();
        
        return map;
      }
      
      function PatternStripes()
      {
        this.corner = function(map, context, size, spacingScale)
        {            
        };
        
        this.border = function(map, context, size, spacingScale)
        {
          var height = 20;
          var width = 4;   
          var spacing = 10;
          
          var scale = size / height; 
          height = size;
          width = width * scale;
          spacing = spacing * scale * spacingScale;
          
          var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
          spacing = (map.range[1] - map.range[0]) / segments;
          
          for(var i=0; i<segments; i++)
          {
            var v = map.transform(i * spacing, 0);
            
            context.save();
            
            context.translate(v[0], v[1]);
            context.rotate(v[2]);         
            context.translate(-width/2, - height);
            context.fillRect(0,0,width, height);
            
            context.restore();
          }
        };       
      }
      
      function PatternStripes2() 
      {
        this.corner = function(map, context, size, spacingScale)
        {
        };
        
        this.border = function(map, context, size, spacingScale)
        {
          var height = 20;
          var width = 4;   
          var spacing = 10;
          
          var scale = size / height; 
          height = size;
          width = width * scale;
          spacing = spacing * scale * spacingScale;
          
          var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
          spacing = (map.range[1] - map.range[0]) / segments;
          
          for(var i=0; i<segments; i++)
          {
            var v = map.transform(i * spacing, 0);
            
            context.save();
            
            context.translate(v[0], v[1]);
            context.rotate(v[2]);         
            context.translate(-width/2, -height);
            context.rotate( Math.PI * 1/5);
            context.fillRect(0,0,width, height);          
            
            context.restore();
          }
        };        
      }

      function PatternRibbon()
      {
        this.corner = function(map, context, size, spacingScale)
        {
            var v1 = map.transform(0, size);
            var v2 = map.transform(size, size);
            var v3 = map.transform(size, 0);
            var v4 = map.transform(0, 0);

            context.beginPath();

            context.moveTo(v1[0], v1[1]);
            context.lineTo(v2[0], v2[1]);
            context.lineTo(v3[0], v3[1]);
            context.lineTo(v4[0], v4[1]);

            context.closePath();
            context.fill();
        };
        
        this.border = function(map, context, size, spacingScale)
        {
          var ribbonHeight = size;
          var spacingRibbon = 5 
          var segmentsRibbon = Math.floor((map.range[1] - map.range[0]) / spacingRibbon);
          var spacingRibbon = (map.range[1] - map.range[0]) / segmentsRibbon;
                  
          context.beginPath();
          var v_first = map.transform(i * spacingRibbon, ribbonHeight);
          context.moveTo(v_first[0], v_first[1]);
          
          for(var i=0; i<= segmentsRibbon; i++)
          {
            var v = map.transform(i * spacingRibbon, ribbonHeight);
            context.lineTo(v[0], v[1]);
          }
  
          for(var i=segmentsRibbon; i>=0; i--)
          {
            var v = map.transform(i * spacingRibbon, 0);
            context.lineTo(v[0], v[1]);
          }
  
          context.lineTo(v_first[0], v_first[1]);
          context.closePath();
          context.fill();
        };
      }

      function PatternStars()
      {
        var patternRibbon = new PatternRibbon();
        var defaultHeight = 30; 
        var defaultSpacing = 26; 
        
        function makeStar(scale)
        {
          return [
            { x : -12.5 * scale, y :  -2.5 * scale },
            { x :  -4.5 * scale, y :  -2.5 * scale },
            { x :   0.5 * scale, y : -12.5 * scale },
            { x :   4.5 * scale, y :  -2.5 * scale },
            { x :  12.5 * scale, y :  -2.5 * scale },
            { x :   4.5 * scale, y :   3.5 * scale },
            { x :   8.5 * scale, y :  12.5 * scale },
            { x :   0.5 * scale, y :   6.5 * scale },
            { x :  -8.5 * scale, y :  12.5 * scale },
            { x :  -4.5 * scale, y :   3.5 * scale }
            ];
        }

        this.corner = function(map, context, size, spacingScale)
        {
          context.save();          

          context.fillStyle = '#ffffff';
      patternRibbon.corner(map, context, size, spacingScale);

      context.fillStyle = '#000000';
      var star = makeStar(size / defaultHeight);
      var v = map.transform(size * 2 / 5,  size * 2 / 5);

      context.translate(v[0], v[1]);
      context.rotate(v[2] + Math.PI / 4);          
      
      context.beginPath();
      context.moveTo(star[0].x , star[0].y);
      
      for(var ii=1; ii<star.length; ii++)
      {
        v = star[ii];
        context.lineTo(v.x, v.y);
      }
      context.closePath();
      context.fill();
      
      context.restore();
    };
    
    this.border = function(map, context, size, spacingScale)
    {
      var spacing = defaultSpacing;
      var scale = size / defaultHeight; 
      var height = size;
      spacing = spacing * scale * spacingScale;
      
      context.fillStyle = '#ffffff';
      patternRibbon.border(map, context, size, spacingScale);
	                
      context.fillStyle = '#000000';
  
          var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
          spacing = (map.range[1] - map.range[0]) / segments;
          
          var offset = [ spacing / 2, size / 2];
  
          var star = makeStar(scale);
          
          for(var i=0; i<segments; i++)
          {
            var v = map.transform(i * spacing + offset[0],  + offset[1]);          
            
            context.save();          
            
            context.translate(v[0], v[1]);
            context.rotate(v[2]);          
            
            context.beginPath();
            context.moveTo(star[0].x , star[0].y);
            
            for(var ii=1; ii<star.length; ii++)
            {
              v = star[ii];
              context.lineTo(v.x, v.y);
            }
            context.closePath();
            context.fill();
            
            context.restore();
          }
        };        
      }

     

      function PatternRope()
      {
        this.corner = function(map, context, size, spacingScale)
        {
        };
        
        this.border = function(map, context, size, spacingScale)
        {
          //var spacing = _s;
      //var height = _h;
  
          var spacing = 10;
          var height = 15;
          
          var scale = size / height; 
          height = size;
          spacing = spacing * scale * spacingScale;
          
          var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
          spacing = (map.range[1] - map.range[0]) / segments;
          
          context.save();
  
         
  
          context.lineWidth = 1.6;
          context.beginPath();
          
           
          var rv1 = {x: spacing * -1.46, y: height * 0.2};
          var rv2 = {x: spacing * -0.2, y: height * -0.3};
          var rv3 = {x: spacing * 0.2, y: height * 1.4};
          var rv4 = {x: spacing * 1.46, y: height * 0.8};
  
          for(var i=0; i<segments; i++)
          {
            var s = i * spacing;
            var v1 = map.transform(s + rv1.x, rv1.y);
            var v3 = map.transform(s + rv3.x, rv3.y);
            var v2 = map.transform(s + rv2.x, rv2.y);
            var v4 = map.transform(s + rv4.x, rv4.y);
            
            context.moveTo(v1[0], v1[1]);
            context.bezierCurveTo(
              v2[0], v2[1],
              v3[0], v3[1],
              v4[0], v4[1] );
          }        
          context.stroke();
          context.restore();
        };
      }
       
      function PatternDotted()
      {
        this.corner = function(map, context, size, spacingScale)
        {
        };
        
        this.border = function(map, context, size, spacingScale)
        {
          var height = 5;
          var width = 4;   
          var spacing = 7;
          
          var scale = size / height; 
          height = size;
          width = width * scale;
          spacing = spacing * scale * spacingScale;
          
          var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
          spacing = (map.range[1] - map.range[0]) / segments;
          
          var v1 = {x: -width / 2, y: -3 * scale};
          //var v2 = {x: -2 * scale, y: -3 * scale};
      var v3 = {x: -width / 2, y: -6 * scale};
      var v4 = {x:  width / 2, y: -6 * scale};
      var v5 = {x:  width / 2, y: -3 * scale};
      //var v6 = {x:  2 * scale, y: -3 * scale};
      var v7 = {x:  width / 2, y: 0};
      var v8 = {x: -width / 2, y: 0};
      
      for(var i=0; i<segments; i++)
      {
        var v = map.transform(i * spacing + spacing / 2, 0);
        
        context.save();          
        
        context.translate(v[0], v[1]);
        context.rotate(v[2]);          
        
        context.beginPath();
        
        context.moveTo(v1.x , v1.y);
        context.bezierCurveTo(
          v3.x, v3.y, 
          v4.x, v4.y,
          v5.x, v5.y );
        
        context.bezierCurveTo(
          v7.x, v7.y, 
          v8.x, v8.y,
          v1.x, v1.y );
        
        context.closePath();
        context.fill();
        
        context.restore();          
      }
    };        
  }
        
  function PatternHash()
  {
    this.corner = function(map, context, size, spacingScale)
    {
    };
    
    this.border = function(map, context, size, spacingScale)
    {       
      var height = 12;
      var width = 4;   
      var spacing = 7;
      
      var scale = size / height; 
      height = size;
      width = width * scale;
      spacing = spacing * scale * spacingScale;
      
      var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
      spacing = (map.range[1] - map.range[0]) / segments;
      
      var v1 = {x: -width / 2, y: -3 * scale};
      var v2 = {x: -width / 2, y: -9.5 * scale};
      var v3 = {x: -width / 2, y: -12.5 * scale};
      var v4 = {x:  width / 2, y: -12.5 * scale};
      var v5 = {x:  width / 2, y: -9.5 * scale};
      var v6 = {x:  width / 2, y: -3 * scale};
      var v7 = {x:  width / 2, y: 0};
      var v8 = {x: -width / 2, y: 0};
      
      for(var i=0; i<segments; i++)
      {
        var v = map.transform(i * spacing + spacing / 2, 0);
        
        context.save();          
        
        context.translate(v[0], v[1]);
        context.rotate(v[2]);          
        
        context.beginPath();
        
        context.moveTo(v1.x , v1.y);
        context.lineTo(v2.x, v2.y);
        context.bezierCurveTo(
          v3.x, v3.y, 
          v4.x, v4.y,
          v5.x, v5.y );
        
        context.lineTo(v6.x, v6.y);
        context.bezierCurveTo(
          v7.x, v7.y, 
          v8.x, v8.y,
          v1.x, v1.y );
        
        context.closePath();
        context.fill();
        
        context.restore();            
      }
    };
  }


 function PatternLines(lineSpecs)
  {
    var defaultHeight = 0;
    for(var i=0; i<lineSpecs.length; i++)
    {
      if(lineSpecs[i].distance + lineSpecs[i].size > defaultHeight)
      {
        defaultHeight = lineSpecs[i].distance + lineSpecs[i].size;
      }
    }
    
    this.corner = function(map, context, size, spacingScale)
    {
      var scale = size / defaultHeight;
      var indent = 0.9;          
      
      for(var iLineSpec=0; iLineSpec <lineSpecs.length; iLineSpec++)
      {
        var lineBottom = lineSpecs[iLineSpec].distance * scale;
        var lineTop = lineBottom + lineSpecs[iLineSpec].size * scale;
        var type = lineSpecs[iLineSpec].corner;
        
        if(type == "edge")
        {
          var v1 = map.transform(0, lineTop);
          var v2 = map.transform(lineTop, lineTop);
          var v3 = map.transform(lineTop, 0);
          var v4 = map.transform(lineBottom, 0);
          var v5 = map.transform(lineBottom, lineBottom);
          var v6 = map.transform(0, lineBottom);

          context.beginPath();
          
          context.moveTo(v1[0], v1[1]);
          context.lineTo(v2[0], v2[1]);
          context.lineTo(v3[0], v3[1]);
          context.lineTo(v4[0], v4[1]);
          context.lineTo(v5[0], v5[1]);
          context.lineTo(v6[0], v6[1]);
          
          context.closePath();
          context.fill();
        }
        else if(type == "indent")
        {
          var radius = lineSpecs[iLineSpec].radius * scale; 
          
          context.beginPath();
                                                   
          if((radius > 0 ) && (radius <= lineBottom))
          {
            var v_a = map.transform(0, lineTop);
            context.moveTo(v_a[0], v_a[1]);

            if(lineBottom > radius)
            {                  
              v_a = map.transform(lineTop - radius, lineTop);                  
              context.lineTo(v_a[0], v_a[1]);
            }
            
            var v_b = map.transform(lineTop - radius * indent, lineTop - radius * indent);
            var v_c = map.transform(lineTop, lineTop - radius);
            
            context.quadraticCurveTo(v_b[0], v_b[1],v_c[0], v_c[1]);

            if(lineBottom > radius)
            {                  
              v_c = map.transform(lineTop, 0);
              context.lineTo(v_c[0], v_c[1]);

              v_c = map.transform(lineBottom, 0);
              context.lineTo(v_c[0], v_c[1]);
            
              v_c = map.transform(lineBottom, lineBottom - radius);
              context.lineTo(v_c[0], v_c[1]);
            }
            else
            {
              v_c = map.transform(lineBottom, 0);
              context.lineTo(v_c[0], v_c[1]);                  
            }

            var v_b = map.transform(lineBottom - radius * indent, lineBottom - radius * indent);
            var v_a = map.transform(lineBottom - radius, lineBottom);
            
            context.quadraticCurveTo(v_b[0], v_b[1],v_a[0], v_a[1]);

            if(lineBottom > radius)
            {
              v_a = map.transform(0, lineBottom);                  
              context.lineTo(v_a[0], v_a[1]);
            }
            
            context.closePath();
            context.fill();
          }
        }
      }
    };
    
    this.border = function(map, context, size, spacingScale)
    {
      var scale = size / defaultHeight;
      var spacing = 5 
      var segments = Math.floor((map.range[1] - map.range[0]) / spacing);
      var spacing = (map.range[1] - map.range[0]) / segments;
      var offsetX = map.range[0];
      
      for(var iLineSpec=0; iLineSpec <lineSpecs.length; iLineSpec++)
      {
        var lineBottom = lineSpecs[iLineSpec].distance * scale;
        var lineTop = lineBottom + lineSpecs[iLineSpec].size * scale;
         
        
        context.beginPath();
        var v = map.transform(offsetX, lineTop);
        context.moveTo(v[0], v[1]);
        
        for(var i=0; i<= segments; i++)
        {
          var v = map.transform(i * spacing + offsetX, lineTop);
          context.lineTo(v[0], v[1]);
        }

        for(var i=segments; i>=0; i--)
        {
          var v = map.transform(i * spacing + offsetX, lineBottom);
          context.lineTo(v[0], v[1]);
        }
            
        context.closePath();
        context.fill();
        }
    };
  }


function saveTemplate(){
	var s = "{\"color\":\""+textColor+"\", \"textlines\": [";
		for(var i=0;i<numLines;i++){
			if(textLines[i].show){
				s+=textLines[i].toJson()+",";
			}
		}  
		if(numLines > 0&&textLines[numLines]!=undefined&&textLines[numLines].show){
			s+=textLines[numLines].toJson();
		}
		//alert(images.length);
		s+="], \"images\": ["
		for(var i=0;i<images.length-1;i++){
			if(images[i].show){
				s+=images[i].toJson()+",";
			}
		}
		if(images.length>1&&images[images.length-1]!=undefined&&images[images.length-1].show){
			s+=images[images.length-1].toJson();
		}
		s+="], \"borders\": [";
		for(var i=0;i<borders.length-1;i++){
			if(borders[i].show){
				s+=borders[i].toJson()+",";
			}
		}
		if(borders.length>=1&&borders[borders.length-1]!=undefined&&borders[borders.length-1].show){
			s+=borders[borders.length-1].toJson();
		}
		//s+="]}";
		s+="], \"lines\": [";
		for(var i=0;i<lines.length-1;i++){
			if(lines[i].show){
				s+=lines[i].toJson()+",";
			}
		}
		if(lines.length>=1&&lines[lines.length-1]!=undefined&&lines[lines.length-1].show){
			s+=lines[lines.length-1].toJson();
		}
		s+="], \"tables\": [";
		for(var i=0;i<tables.length-1;i++){
			if(tables[i].show){
				s+=tables[i].toJson()+",";
			}
		}
		if(tables.length>=1&&tables[tables.length-1]!=undefined&&tables[tables.length-1].show){
			s+=tables[tables.length-1].toJson();
		}
		s+="]}";
		
		drawToGhost();
		var data = ghostcanvas.toDataURL();
		
		var name = $('#templateName').val();
		var category = $('#templateCategory').val();
		var xmlhttp;
		if (window.XMLHttpRequest)
		  {// code for IE7+, Firefox, Chrome, Opera, Safari
		  	xmlhttp=new XMLHttpRequest();
		}
		else
		  {// code for IE6, IE5
		  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function()
	  {
	  if (xmlhttp.readyState==4 && xmlhttp.status==200)
	    {
	    alert(xmlhttp.responseText);
	    }
	  }
		xmlhttp.open("POST","savetemplate.php",true);
		xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		xmlhttp.send("s="+escape(s)+"&name="+name+"&category="+category+"&data="+data);	
		
	
}
