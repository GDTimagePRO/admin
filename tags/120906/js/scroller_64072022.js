
//create an image scroller object
var imageScroller_64072022;
		
//delay all code execution untill after page load
addAnEvent(window, "load", loadScroller_64072022);
		
function loadScroller_64072022() {
        
imageScroller_64072022 = new ImageScroller("imageScrollerFrame_64072022", "imageScrollerImageRow_64072022");
//**    [begin] Modify these to change your images  **//
imageScroller_64072022.setThumbnailWidth(90);
imageScroller_64072022.setThumbnailHeight(34);
imageScroller_64072022.setScrollSpeed(21);
imageScroller_64072022.setScrollAmount(2);
imageScroller_64072022.setThumbsShown(3);
imageScroller_64072022.setAutoLoop("True");
imageScroller_64072022.setScrollType(0);
imageScroller_64072022.setThumbnailPadding(3);

imageScroller_64072022.addThumbnail("images/thmb_1.jpg", "javascript:doImageSwap('images/orig_1.jpg');", "", "");
imageScroller_64072022.addThumbnail("images/thmb_2.jpg", "javascript:doImageSwap('images/orig_2.jpg');", "", "");
imageScroller_64072022.addThumbnail("images/thmb_3.jpg", "javascript:doImageSwap('images/orig_3.jpg');", "", "");
imageScroller_64072022.addThumbnail("images/thmb_4.jpg", "javascript:doImageSwap('images/orig_4.jpg');", "", "");
imageScroller_64072022.addThumbnail("images/thmb_5.jpg", "javascript:doImageSwap('images/orig_5.jpg');", "", "");
imageScroller_64072022.addThumbnail("images/thmb_6.jpg", "javascript:doImageSwap('images/orig_6.jpg');", "", "");
imageScroller_64072022.addThumbnail("images/thmb_7.jpg", "javascript:doImageSwap('images/orig_7.jpg');", "", "");
imageScroller_64072022.addThumbnail("images/thmb_8.jpg", "javascript:doImageSwap('images/orig_8.jpg');", "", "");
imageScroller_64072022.addThumbnail("images/thmb_9.jpg", "javascript:doImageSwap('images/orig_9.jpg');", "", "");
imageScroller_64072022.addThumbnail("images/thmb_10.jpg", "javascript:doImageSwap('images/orig_10.jpg');", "", "");
imageScroller_64072022.addThumbnail("images/thmb_11.jpg", "javascript:doImageSwap('images/orig_11.jpg');", "", "");
imageScroller_64072022.addThumbnail("images/thmb_12.jpg", "javascript:doImageSwap('images/orig_12.jpg');", "", "");

//**    [end]   Modify these to change your images  **//			    			    
imageScroller_64072022.enableThumbBorder(true);
imageScroller_64072022.setClickOpenType(1);
imageScroller_64072022.setNumOfImageToScroll(1);
imageScroller_64072022.renderScroller();
	        };
			
			
