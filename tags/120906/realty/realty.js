var WIDTH;
var HEIGHT;
var textColor = "#0054A4";
var context1,context2,context3;
var first = "";
var last = "";
var address1 = "";
var address2 = "";
var monogram = "";
var currentTemplate = 1;

WebFontConfig = {
        custom: { families: ['FrutigerLight' ],
    urls: [ 'http://www.cameronmcguinness.com/ssi/fonts/FrutigerLight.css']}};
      (function() {
        var wf = document.createElement('script');
        wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
            '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
        wf.type = 'text/javascript';
        wf.async = 'true';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(wf, s);
      })();

TextLine = function(){
	this.id = -1;
	this.text = "";
	this.fontsize = 6;
	this.fontfamily = "Segoe UI Light";
	this.bold = 0;
	this.italic = 0;
	this.underline = 0;
	this.x = 0;
	this.y = 0;
	this.align = "left";
	this.show = true;
	this.type = "Straight";
	this.radius = 0;
	this.toJson = function(){
		var s = '{"id": '+this.id+', "text": "'+this.text+'", "fontsize": '+this.fontsize+', "font": "'+this.fontfamily+'", "x": '+this.x+', "y": '+this.y+', "bold": '+this.bold+', "italic": '+this.italic+',"underline": '+this.underline+',"type": "'+this.type+'", "radius": '+this.radius+'}';
		return s;
	}
}

Border = function(){
	this.id = -1;
	this.x = 0;
	this.y = 0;
	this.width=100;
	this.height = 100;
	this.type_id = 0;
	this.line_width = 2;
	this.sides = "1111";
	this.radius = 1;
	this.show = true;
	this.toJson = function(){
		var s = '{"id": '+this.id+', "x": '+this.x+', "y": '+this.y+', "width": '+this.width+', "height": '+this.height+', "type_id": '+this.type_id+', "line_width": '+this.line_width+', "sides": "'+this.sides+'", "radius": '+this.radius+'}';
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
	this.toJson = function(){
		var s = '{"id": '+this.id+', "x": '+this.x+', "y": '+this.y+', "x2": '+this.x2+', "y2": '+this.y2+', "type_id": '+this.type_id+', "line_width": '+this.line_width+'}';
		return s;
		
	}
}

function drawStraightTextLine(textLine,context){
	context.fillText(textLine.text,textLine.x,textLine.y);
}


//draws the text line to the canvas
drawTextLine = function(textLine,context){
	if(textLine.show){
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
		drawStraightTextLine(textLine,context);
	}
	
}

function drawBorder(border,context){
	if(border.show){
		context.lineWidth = border.line_width;
		context.strokeStyle = textColor;
		border.radius = parseInt(border.radius);
		border.width = parseInt(border.width);
		border.height = parseInt(border.height);
		border.x = parseInt(border.x);
		border.y = parseInt(border.y);
		//alert(border.radius);
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

function parseJSON(s, context) {
    context.fillStyle = "#ffffff";
    context.fillRect(0, 0, WIDTH, HEIGHT);
    context.fillStyle = textColor;
    var count = 0;

	var object = jQuery.parseJSON(s);
	for(var i=0;i<object.textlines.length;i++){
		var line = object.textlines[i]; 
		var textLine = new TextLine();
		textLine = new TextLine();
		textLine.id = line.id;
		textLine.text = line.text;
		//alert("First: |"+first+"| Line: |"+textLine.text);
		if(i==0&&first!=""&&context!=context3){
			textLine.fontfamily = "FrutigerLight";
			textLine.text = first+" "+last;
        }
        if (i == 0 && first != "" && context == context3) {
        	textLine.fontfamily = "FrutigerLight";
            textLine.text = last;
        }
		if(i==1&&address1!=""){
			textLine.fontfamily = "FrutigerLight";
			textLine.text = address1;
		}
		if(i==2&&address2!=""){
			textLine.fontfamily = "FrutigerLight";
			textLine.text = address2;
		}
		if(i==3&&monogram!=""){
			textLine.fontfamily = "FrutigerLight";
			textLine.text = monogram;
		}
		textLine.x = line.x;
		textLine.y = line.y;
		if(textLine.x < 1) textLine.x = textLine.x*WIDTH;
		if(textLine.y < 1) textLine.y = textLine.y*HEIGHT;
		textLine.fontfamily = "Segoe UI Light";
		textLine.fontsize = line.fontsize;
		textLine.bold = line.bold;
		textLine.italic = line.italic;
		textLine.underline = line.underline;
		textLine.type = line.type;
		textLine.align = line.align;
		textLine.show = true;
		textLine.radius = line.radius;
		drawTextLine(textLine,context);
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
		border.line_width = b.line_width;
		border.radius = b.radius;
		if(border.radius <= 0){
			border.radius = 1;
		}
		border.sides = b.sides;
		drawBorder(border,context);
	}
	
	for(var i=0;i<object.lines.length;i++){
		var l = object.lines[i];
		var line1 = new Line();
		if(l.x2 < 1){
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
		drawLine(line1,context);
	}
	
}

$(document).ready(function (){
	
    var canvas1 = document.getElementById("canvas1");
    var canvas2 = document.getElementById("canvas2");
    var canvas3 = document.getElementById("canvas3");
    WIDTH = canvas1.width;
    HEIGHT = canvas1.height;
    context1 = canvas1.getContext("2d");
    context2 = canvas2.getContext("2d");
    context3 = canvas3.getContext("2d");
    
    json1 = $('#ctl00_ctl00_cph1_cph1_template1').val();
    parseJSON(json1, context1);
    json2 = $('#ctl00_ctl00_cph1_cph1_template2').val();
    parseJSON(json2, context2);
    json3 = $('#ctl00_ctl00_cph1_cph1_template3').val();
    parseJSON(json3, context3);
    //changeText();
});

clearAll = function () {
    context1.clearRect(0, 0, WIDTH, HEIGHT);
    context2.clearRect(0, 0, WIDTH, HEIGHT);
    context3.clearRect(0, 0, WIDTH, HEIGHT);    
}


changeText = function () {
    first = $('#ctl00_ctl00_cph1_cph1_first').val();
    last = $('#ctl00_ctl00_cph1_cph1_last').val();
    address1 = $('#ctl00_ctl00_cph1_cph1_address1').val();
    address2 = $('#ctl00_ctl00_cph1_cph1_address2').val();
    monogram = $('#ctl00_ctl00_cph1_cph1_monogram').val();

    clearAll();
    parseJSON(json1, context1);
    parseJSON(json2, context2);
    parseJSON(json3, context3);
    
    changeCanvas(currentTemplate);
}

function changeCanvas(template) {
	currentTemplate = template;
    var image = document.getElementById("canvas"+template).toDataURL("image/png");
    $('#ctl00_ctl00_cph1_cph1_canvasImageContent').val(image);
    $('input:radio[name=canvasChoice]')[template-1].checked = true;
}


