// -------------------------------------------------
// グローバル変数
// -------------------------------------------------
var _note_id     = "";
//var _page_id     = "";
var _isLocalMode = true;

var _leafManager = new LeafManager();
var _linkManager = new LeafLinkManager();
var _pageManager = new PageManager();
var _noteManager = new NoteManager();

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
					var url = $("#txtWebLeafURL").val();
					
					if(url=="")
					{
						alert("URLを入力して下さい");
						return;
					}
					
					if(!Util.isURL(url))
					{
						alert("正しいURLを入力して下さい");
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
	
	// 全表示ダイアログ
	$("#showAllDialog").dialog({
		autoOpen: false,
		width: 500,
		height: 500,
		zIndex: 990000000,
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
		for (var leaf in _leafManager.leafList)
		{
			if(_leafManager.leafList[leaf])
			{
				if(_leafManager.leafList[leaf].isEdit)
				{
					_leafManager.leafList[leaf].setEditMode(false);
					_leafManager.leafList[leaf].update();
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
	
	$("#btnClose").click(function()
	{
		$(".button-container p").text("現在画面イメージを作成中です...");
		$('#btnClose').hide();
		
		sendScreenshot({
			complete	: function(){
				window.close();
			}
		});
	});
	
	$(window).resize(function(){
		_leafManager.renderBackground();
	});

	_leafManager.loadData({
		display		: true,
		page_id		: _page_id
	});

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
				'<img src="' + THEME_ROOT_PATH + '/images/fb_share.png"></a>　' +
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

function sendScreenshot(params)
{
	html2canvas(
		document.querySelector("#stage"), {
			logging: true,
            profile: true,
            useCORS: true}).then(function(canvas) {
			//canvas.width  = canvas.width  * 0.5;
			//canvas.height = canvas.height * 0.5;
			
			var base64 = canvas.toDataURL('image/png');
			
			var data = {
				page_id 	: _page_id,
				page_image	: base64,
			};
			
			$.ajax({
				url		: API_BASE_URL + 'update_image' + API_EXTENSION,
				type	: 'post',
				dataType: 'xml',
				data    : data,
				timeout	: TIMEOUT_MS,
				success	:
					// XMLデータを取得
					function(xml,status)
					{
						if(status!='success')
							return;
						
						if(params.complete)
							params.complete();
					},
				error:
					// エラー
					function(data)
					{
						alert('error');
					}
			});
	});
}
