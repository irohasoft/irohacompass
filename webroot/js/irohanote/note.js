/**
 * iroha Note Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */
 
// -------------------------------------------------
// グローバル変数
// -------------------------------------------------
var _note_id     = "";
//var _page_id     = "";
var _isLocalMode = true;

var _noteManager = new NoteManager();
var _pageManager = new PageManager();
var _leafManager = new LeafManager();
var _linkManager = new LeafLinkManager();

_leafManager.isReadOnly = _isReadOnly;
_linkManager.isReadOnly = _isReadOnly;

// -------------------------------------------------
// 初期処理
// -------------------------------------------------
$(document).ready(function(){
	// スマートフォン以外の場合のみ、ページ一覧画面の移動／リサイズを許可する (Android Chrome 対応)
	if(!Util.isSP())
	{
		$("#divPageList").draggable();
		$("#divPageList").resizable();
	}
	
	// Webリーフダイアログ
	$("#webLeafDialog").dialog({
		autoOpen: false,
		width: 380,
		height: 280,
		zIndex: 990000001,
		modal: true,
		dialogClass: 'no-title-dialog',
		buttons: [
			{
				text: "キャンセル",
				click: function() {
					$(this).dialog( "close" );
				}
			},
			{
				text: "登録",
				click: function() {
					var url = $("#txtWebLeafURL").val();
					
					if(url=="")
					{
						top.alert("URLを入力して下さい");
						return;
					}
					
					if(!Util.isURL(url))
					{
						top.alert("正しいURLを入力して下さい");
						return;
					}
					
					_leafManager.add({
						leaf_kind	: LEAF_KIND_WEB,
						url			: url
					});

					$(this).dialog( "close" );
				}
			}
		]
	});
	
	// 画像リーフダイアログ
	$("#imageLeafDialog").dialog({
		autoOpen: false,
		width: 380,
		height: 280,
		zIndex: 990000000,
		modal: true,
		dialogClass: 'no-title-dialog',
		buttons: [
			{
				text: "キャンセル",
				click: function() {
					$(this).dialog( "close" );
				}
			},
			{
				text: "登録",
				click: function() {
					var url = $("#txtImageLeafURL").val();
					
					if(url=="")
					{
						alert("URLを入力して下さい");
						return;
					}
					
					_leafManager.add({
						leaf_kind	: LEAF_KIND_IMAGE,
						url			: url
					});

					$(this).dialog( "close" );
				}
			}
		]
	});
	
	// 全表示ダイアログ
	$("#showAllDialog").dialog({
		autoOpen: false,
		width: 500,
		height: 500,
		zIndex: 990000001,
		modal: true,
		buttons: [
			{
				text: "閉じる",
				click: function() {
					$(this).dialog( "close" );
				}
			}
		]
	});


	$("#btnLeafAdd").click(function()
	{
		_leafManager.add({
			leaf_kind	: LEAF_KIND_CARD
		});
	});
	
	$("#btnLeafWebAdd").click(function()
	{
		$("#txtWebLeafURL").val("")
		$("#webLeafDialog").dialog( "open" );
	});
	
	$("#btnLeafImageAdd").click(function()
	{
		$("#txtImageLeafURL").val("");
		$("#imageLeafDialog").dialog( "open" );
	});
	
	$("#btnGroupAdd").click(function()
	{
		_leafManager.add({
			leaf_kind	: LEAF_KIND_GROUP
		});
	});
	
	$("#btnLinkDelete").click(function()
	{
		if(_linkManager.deleteTargetLink)
			_linkManager.deleteTargetLink.remove();
	});
	
	$("#btnPageListClose").click(function()
	{
		$("#divPageList").hide();
		$("#btnPageList").show();
	});
	
	$("#btnPageList").click(function()
	{
		$("#divPageList").show();
		$("#btnPageList").hide();
		//$("#divPageList").toggle();
	});
	
	$("#btnPageTitleEdit").click(function()
	{
		_pageManager.edit(_pageManager.getSelectedNode().id, _pageManager.getSelectedNode().name);
	});
	
	// タッチデバイスの場合のみページタイトルの編集ボタンを表示する
	if(!isPC())
		$("#btnPageTitleEdit").show();
	
	$("#btnSearch").click(function()
	{
		if($("#txtKeyword").val()=="")
		{
			top.alert("検索キーワードを入力してください");
			return;
		}
		
		var data = {
			is_cloud	: true,
			note_id		: _note_id,
			keyword		: $("#txtKeyword").val()
		}
		
		$("#searchDialog table tbody").html("");
		
		_noteManager.search({
			data		: data,
			is_cloud	: true,
			complete	: function(result)
			{
				if(result.length == 0)
				{
					$(
						'<tr>'+
							  '<td colspan="4" align="center"><b>該当するリーフは存在しません</b></td>' + 
						'</tr>'
					).appendTo('#searchDialog tbody');
				}
				else
				{
					for(var i=0; i < result.length; i++)
					{
						var url = 
							"javascript:openPage(" + result[i].page_id + ")";
						
						$(
							'<tr>'+
								  '<td><a href="' + url + '">' + result[i].page_title + '</a></td>' +
								  '<td><a href="' + url + '">' + result[i].leaf_title + '</a></td>' +
							'</tr>'
						).appendTo('#searchDialog tbody');
					}
				}
				
				$("#searchDialog").dialog( "open" );
			}
		});
	});
	

	$("#txtKeyword").keypress(function(e)
	{
		if ( e.which == 13 ) {
			$("#btnSearch").click();
		}
	});

	$("#map").click(function()
	{
		_linkManager.linkMode = false;
		
		// 全てのリーフの編集状態を解除
		for (var leaf_id in _leafManager.leafList)
		{
			var leaf = _leafManager.leafList[leaf_id];
			
			if(leaf)
			{
				leaf.unselect();
				
				if(leaf.isEdit)
				{
					leaf.setEditMode(false);
					leaf.update();
				}
			}
		}
		
		// リンク中状態を解除
		_linkManager.hideNavi();
		
		// リンクを選択を解除
		_linkManager.hideSelection();
		
		$("#divLinkDelete").hide();
	});
	
	$( "#map" ).mousemove(function(e)
	{
		if(_linkManager.linkMode)
		{
			$('#divLinkTarget').css("top",  e.pageY - 25 + "px");
			$('#divLinkTarget').css("left", e.pageX + "px");
			jsPlumb.repaintEverything();
		}
	});
	
	$('#divPageList').bind('page_loaded',function(ev)
	{
		$( "#sortable" ).sortable(
		{
			stop: function( event, ui )
			{
				_pageManager.updateOrder()
			}
		});
		
		// PC以外（タッチデバイス）、かつページのスクロール可能な場合、スクロール用のボタンを表示する
		if($("#divPageListContainer").get(0).scrollHeight > $("#divPageListContainer").height())
		{
			if(!isPC())
				$("#divPageListControl").show();
		}
		/*
		$( "#sortable" ).mousedown(function(){
			event.stopPropagation();
		});
		*/
		_pageManager.selectFirst();
	});
	
	// 全画面
	$("#btnFullscreen").click(function()
	{
		$(parent.document).find('#fraIrohaNote').addClass('irohanote-fullscreen');
		$(parent.document).find('#irohanote-frame-' + _page_id).addClass('irohanote-fullscreen');
		$(parent.document).find('body').addClass('no-scroll');
		$("#btnFullscreen").hide();
		$("#btnFullscreenExit").show();
	});
	
	// 全画面解除
	$("#btnFullscreenExit").click(function()
	{
		$(parent.document).find('#fraIrohaNote').removeClass('irohanote-fullscreen');
		$(parent.document).find('#irohanote-frame-' + _page_id).removeClass('irohanote-fullscreen');
		$(parent.document).find('body').removeClass('no-scroll');
		$("#btnFullscreenExit").hide();
		$("#btnFullscreen").show();
	});
	
	$(window).resize(function(){
		_leafManager.renderBackground();
	});

	_leafManager.loadData({
		display		: true,
		page_id		: _page_id,
		complete	: function()
		{
			if(_isReadOnly)
			{
				$(".stage-button-block").hide();
			}
		}
	});

	if(LANG=='en')
		$("[data-localize]").localize(ROOT_PATH + "locales/app", { language: "en" });

	setUploader()
});

function openPage(page_id)
{
	_pageManager.selectNode(page_id);
	$("#searchDialog").dialog("close");
}

function renderPageOpenDialog()
{
	window.FB		= undefined;
	//window.twttr	= undefined;
	
	$("#fb-root").remove();
	//$(".twitter-share-button").remove();
	
	if(_pageManager.getSelectedNode().open_level=="1")
	{
		var url = location.protocol + "//" + location.hostname + "/viewer/?id=" + _pageManager.getSelectedNode().id;
		
		_pageManager.setOpenStatus(1, function(){
			$("#divPageShareContainer").show();
			$("#txtPageShareURL").val(url);
			$("#divPageShareSNSContainer").html(
//				'<a name="fb_share" id="lnkPageShare" type="button" share_url="' + url + '">シェア</a>' + 
//				'<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>　' +
				'<a href="#" onclick="showFBShare(' + "'" + url + "'" + ');">' +
				'<img src="' + ROOT_PATH + '/images/fb_share.png"></a>　' +
				"<script>function showFBShare(url){window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(url)+'&t='+encodeURIComponent(document.title),null,'width=550px,height=350px');return false;}</script>"+
				'<a href="https://twitter.com/share" class="twitter-share-button" data-url="' + url + '" data-lang="ja" data-count="none">ツイート</a>' +
				"<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>"
			);
			
			if(window.twttr)
				window.twttr.widgets.load();
		});
		
		$("#chkPageShare").prop("checked", true);
	}
	else
	{
		$("#divPageShareSNSContainer").html("");
		_pageManager.setOpenStatus(0, function(){
			$("#divPageShareContainer").hide();
		});
		
		$("#chkPageShare").prop("checked", false);
	}
}


function getLeafCount()
{
	return Object.keys(_leafManager.leafList).length;
}

function setUploader()
{
	$(document).on('change', 'input[name="file"]', function()
	{
		var $file = $('input[name="file"]');
		
		var formData = new FormData();
		
		if (!$file.val())
			return;
		
		formData.append( 'file', $file.prop("files")[0] );
		formData.append( 'dir', $file.val());
		
		var postData = {
			type : "POST",
			dataType : "text",
			data : formData,
			processData : false,
			contentType : false
		};
		
		$.ajax(
			ROOT_PATH + 'tasks/upload_image', 
			postData).done( function(text)
			{
				if(!JSON.parse(text)[0])
				{
					alert('画像のアップロードに失敗しました');
					return;
				}
				
				$('#txtImageLeafURL').val(JSON.parse(text)[0]);
				$('.image-preview').children().remove();
				$('.image-preview').append('<img src="' + JSON.parse(text)[0] + '">');
				console.log(text);
			}
		);
	});
}

