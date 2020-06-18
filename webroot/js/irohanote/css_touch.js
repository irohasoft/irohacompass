/**
 * iroha Note Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2018 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohacompass.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */
 
if (
	(navigator.userAgent.indexOf('iPhone') > 0) || 
	(navigator.userAgent.indexOf('iPod') > 0) || 
	(navigator.userAgent.indexOf('iPad') > 0) || 
	(navigator.userAgent.indexOf('Android') > 0)
)
{
	document.write('<link rel="stylesheet" href="' + ROOT_PATH + '/css/note_touch.css">');
}
