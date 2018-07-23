function isCanvasSupported()
{
	var elem = document.createElement('canvas');
	return !!(elem.getContext && elem.getContext('2d'));
}

function forceModernBrowser()
{
	if(!isCanvasSupported())
	{
		window.location = "browser_update.php";
		document.write("");
	}
}