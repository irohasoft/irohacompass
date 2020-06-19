/**
 * iroha Note Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2018 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohacompass.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */
 
var TIMEOUT_MS		= 10000;

var PREFIX_LEAF_MENU = "menu_";
var PREFIX_LEAF      = "leaf_";
var PREFIX_PAGE      = "page_";
var LEAF_KIND_CARD   = 2;
var LEAF_KIND_WEB    = 3;
var LEAF_KIND_IMAGE  = 4;
var LEAF_KIND_GROUP  = 5;

var LS_PREFIX        = "irohanote_";
var LS_RECENT_COLOR  = "irohanote_recent_color";

var IS_LOCAL_MODE    = true;

var Util = new IrohaUtility();

function getUrlVars(url)
{
	var vars = [], hash;
	var hashes = window.location.href.slice(url.indexOf('?') + 1).split('&');
	
	for(var i = 0; i < hashes.length; i++)
	{
		hash = hashes[i].split('=');
		vars.push(hash[0]);
		vars[hash[0]] = hash[1];
	}
	
	return vars;
}

function checkError(xml)
{
	var $error_code    = $(xml).find('error_code').text();
	var $error_message = $(xml).find('error_message').text();
	
	if($error_code=="0")
		return true;
		
	alert($error_message + " (" + $error_code + ")");
	return false;
}

function xmlToString(xmlData)
{
	var xmlString;
	//IE
	if (window.ActiveXObject){
		xmlString = xmlData.xml;
	}
	// code for Mozilla, Firefox, Opera, etc.
	else{
		xmlString = (new XMLSerializer()).serializeToString(xmlData);
	}
	return xmlString;
}	


function showErrorMessage(data)
{
	
}

function showCommError(data, api_name)
{
	var err_msg = '通信エラー : ' + data.statusText;// + "\n" + data.responseText;
	
	if(api_name)
		err_msg += "\n API : " + api_name;
	
	if(location.hostname=="irohasoft.com")
		alert(err_msg);
}

function getId()
{
	var id = parseInt( new Date() /1000 ) - 1356966000;
	
	return id;
}


function isPC()
{
	var ua = window.navigator.userAgent.toLowerCase();

	if(
		(ua.indexOf('iphone') > 0) ||
		(ua.indexOf('ipad') > 0) ||
		(ua.indexOf('ipod') > 0) ||
		(ua.indexOf('android') > 0)
	)
	{
		return false;
	}

	return true;
}

function getWebPageTitle(target_url, callback)
{
	var url = API_BASE_URL + 'webpage';
	var data = new Object();
	data['url'] = target_url;
	
	$.ajax({
		url		: url,
		type	: 'post',
		dataType: 'xml',
		data    : data,
		cache   : false,
		timeout	: 3000,
		success	:
			// XMLデータを取得
			function(xml,status)
			{
				if(status!='success')return;
				
				var title = $(xml).find('title').text() || "no title";
				callback(url, title);
			},
		error:
			// エラー
			function(data)
			{
				var title = "no title";
				callback(url, title);
			}
	});

	/*
	var xhr=new XMLHttpRequest()
	
	xhr.onload = function()
	{ 
		var title = this.responseXML.title || "no title"
		callback(url, title);
	}
	
	xhr.open("GET", url ,true); 
	xhr.responseType="document"; 
	xhr.send(); 
	*/
}

function getTreeByList( list, parent_id )
{
	var tree  = new Array();

	if(!parent_id)
		parent_id = 0;

	for(var i=0; i < list.length; i++)
	{
		if(list[i].parent_id == parent_id)
		{
			var index = tree.length;

			tree[index] = list[i];
			var children = getTreeByList(list, list[i].id);

			if(children.length > 0)
				tree[index].children = children;
		}
	}

	console.log(tree);

	return tree;
}

function IrohaUtility() {}

// 連想配列を特定のキーでソート
IrohaUtility.prototype.hsort = function (hash, key)
{
	try
	{
		hash.sort(function(a, b){
			var a_s = a[key].toString().toLowerCase();
			var b_s = b[key].toString().toLowerCase();
			if (a[key]-b[key]) {
				return a[key]-b[key];
			} else if (a_s > b_s) {
				return 1;
			} else if (a_s < b_s) {
				return -1;
			} else {
				return 0;
			}
		});
	}
	catch( e )
	{
	}
	
	return hash;
}

IrohaUtility.prototype.marge = function (dataOnLocal, dataOnServer)
{
	var pages = new Array();
	var pagesOnLocalHT  = new Object();
	var pagesOnServerHT = new Object();
	var pagesHT = new Object();
	
	// ローカルのデータを連想配列化
	for(var i=0; i<dataOnLocal.length; i++)
	{
		pagesOnLocalHT[dataOnLocal[i].page_id] = dataOnLocal[i];
		pagesHT[dataOnLocal[i].page_id] = dataOnLocal[i];
	}
	
	// サーバのデータを連想配列化
	for(var i=0; i<dataOnServer.length; i++)
	{
		pagesOnServerHT[dataOnServer[i].page_id] = dataOnServer[i];
	}
	
	// サーバのデータを取得（ローカルに存在する場合、更新IDをチェック）
	for(var i=0; i<dataOnServer.length; i++)
	{
		var page = dataOnServer[i];
		
		if(pagesOnLocalHT[page.page_id])
		{
			// ページIDをキーにローカルの更新IDを取得
			var update_id = pagesOnLocalHT[page.page_id].update_id;
			
			if(update_id)
			{
				// ローカルデータに更新IDが存在する場合、上書きしない
				if(update_id > 0)
				{
					continue;
				}
			}
			
			// サーバのデータがローカルに存在し、更新IDが設定されていない場合、ローカルのデータを上書き
			pagesHT[page.page_id] = page;
		}
		else
		{
			// サーバのデータがローカルに存在しない場合、新規にサーバのデータを追加
			pagesHT[page.page_id] = page;
		}
	}
	
	// 連想配列から配列に変更
	for (var i in pagesHT)
	{
		pages[pages.length] = pagesHT[i];
	}
	
	return pages;
}

IrohaUtility.prototype.isSP = function ()
{
	var userAgent = window.navigator.userAgent.toLowerCase();

	if(
		(navigator.userAgent.indexOf('iPhone') > 0 && navigator.userAgent.indexOf('iPad') == -1) || 
		(navigator.userAgent.indexOf('iPod') > 0) || 
		(navigator.userAgent.indexOf('Android') > 0 && navigator.userAgent.indexOf('Mobile') > 0)
	)
	{
		return true;
	}
	else
	{
		return false;
	}
}

IrohaUtility.prototype.isTablet = function ()
{
	var userAgent = window.navigator.userAgent.toLowerCase();

	if(
		(navigator.userAgent.indexOf('Android') > 0 && navigator.userAgent.indexOf('Mobile') == -1) || 
		(navigator.userAgent.indexOf('iPad') > 0)
	)
	{
		return true;
	}
	else
	{
		return false;
	}
}

IrohaUtility.prototype.isPC = function ()
{
	if (
		('createTouch' in document)|| 
		('ontouchstart' in document)
	)
	{
		return false;
	}
	else
	{
		return true;
	}
}

IrohaUtility.prototype.isIE = function ()
{
	// IEであるか否かの判定
	var isIE = false; // IEか否か
	var version = null; // IEのバージョン
	var ua = navigator.userAgent;
	if( ua.match(/MSIE/) || ua.match(/Trident/) ) {
	    isIE = true;
	    version = ua.match(/(MSIE\s|rv:)([\d\.]+)/)[2];
	}
	
	return isIE;
}


IrohaUtility.prototype.getIEVer = function ()
{
	var ua=window.navigator.userAgent;

	var ver=0;//IEバージョン番号

	if(ua.match(/Trident/) && !ua.match(/MSIE/))
	{
		ver = 11;
	}

	//IE判定
	if(ua.match(/MSIE/) || ua.match(/Trident/)) {
		var re=new RegExp("MSIE ([0-9]{1,}[¥.0-9]{0,})");
		if (re.exec(ua) != null){
			ver = parseFloat(RegExp.$1);
		}
	}
	
	return ver;
}

IrohaUtility.prototype.isURL = function (url)
{
	if(!url)
		return  false;
	
	return (url.match(/^(http|https):\/\//i));
}

IrohaUtility.prototype.sleep = function (callback, time)
{
	setTimeout(callback, time);
}

IrohaUtility.prototype.setLocalStorage = function(key, data)
{
	try
	{
		localStorage[key] = JSON.stringify(data);
	}
	catch (e)
	{
		console.log(e);
	}
}

IrohaUtility.prototype.getLocalStorage = function(key)
{
	var result = new Array();
	try
	{
		if(localStorage[key])
			result = JSON.parse(localStorage[key])

		return result;
	}
	catch (e)
	{
		console.log(e);
		return result;
	}
}

IrohaUtility.prototype.removeLocalStorage = function(key)
{
	localStorage.removeItem(key);
}

IrohaUtility.prototype.setEncodeText = function(targetElement, value)
{
	targetElement.html(targetElement.text(value).html().replace(/\n/g, '<br>'));
}

IrohaUtility.prototype.getAccessToken = function()
{
	return localStorage.getItem("access_token");
}
