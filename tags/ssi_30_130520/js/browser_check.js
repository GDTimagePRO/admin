function isCanvasSupported()
{
	var elem = document.createElement('canvas');
	return !!(elem.getContext && elem.getContext('2d'));
}

function readCookie(name)
{
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++)
	{
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function forceModernBrowser()
{
	$(function(){
		
		$('body').append('<div id="forceModernBrowser_IE8"><!' + '--[if IE 8]>x<![endif]--></div>');
		var isIE8 = $('#forceModernBrowser_IE8').html() === 'x';
		if(isIE8)
		{
			window.location.replace("browser_update.php");
			settimeout(function(){
				window.location.replace("browser_update.php");
			}, 1);
		}
	});
//	if((!isCanvasSupported() || !window.chrome) && (readCookie("no_chrome") != "true"))
//	{
//		window.location.replace("browser_update.php");
//		
//		settimeout(function(){
//			window.location.replace("browser_update.php");
//			document.write("");
//		}, 1);
//	}
}