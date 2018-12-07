if (
	(navigator.userAgent.indexOf('iPhone') > 0) || 
	(navigator.userAgent.indexOf('iPod') > 0) || 
	(navigator.userAgent.indexOf('iPad') > 0) || 
	(navigator.userAgent.indexOf('Android') > 0)
)
{
	document.write('<link rel="stylesheet" href="' + THEME_ROOT_PATH + '/css/note_touch.css">');
}
