function javaInit(width, height, _initStateJSON, imageURL) {
	_system.onInit(300, 150);
	System.IMAGE_SERVICE = imageURL;
	_system.setState(JSON.parse(_initStateJSON));
	_system.setPageType(System.PAGE_TYPE_BOX);
	var hdScaleWidth = width / _system.getPageWidth(); 
	var hdScaleHeight = height / _system.getPageHeight(); 
	var hdScale = Math.min(hdScaleWidth,hdScaleHeight); 
	_system.scene.scale = hdScale;
	return JSON.stringify(_system.scene.getSSRDO(Scene.DISPLAY_GROUP_CONTENT));

}

//java.lang.System.out.println(javaInit(898, 898, '{"selected":"0","elements":[{"className":"ImageElement","editAllowMove":true,"position":{"x1":-169,"y1":-169,"x2":169,"y2":169},"updateSizeOnLoad":false,"maintainAspectRatio":true,"imageSrc":{"type":1,"id":"session/2aeae800e4a0a7003c0d6fa0bbbe3fd210d69db4/UU0_@@(MONOC).png","color":"000000"},"visible":true,"angle":0,"showMore":false,"title":"Click on Change image to upload a photo or image","displayGroup":3,"dopt":{"tooltip":"","maxSize":0,"visibility":0},"id":"user_upload","script":{"src":" ","vars":{}},"config":{"controls":["flipHorizontal","flipVertical"]}}],"pageParams":{"type":1,"width":338,"height":338},"scene":{"colors":{"ink":{"name":"Midnight-Black","value":"000000"}},"backgroundColor":"#FFFFFF"}}', "http://localhost:8080/ARTServer/GetImage"));