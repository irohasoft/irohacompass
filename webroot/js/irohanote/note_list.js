/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2018 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohacompass.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */
 
// -------------------------------------------------
// グローバル変数
// -------------------------------------------------
var _noteManager = new NoteManager();

// -------------------------------------------------
// 初期処理
// -------------------------------------------------
$(document).ready(function(){
	if(isPC())
	{
		$( "#btnImport" ).show();
	}
	
	$( "#sortable" ).sortable(
	{
		update: function( event, ui )
		{
			_noteManager.updateOrder()
		}
	});
	$( "#sortable" ).disableSelection();
	
	$("#stage").click(function()
	{
		if(_noteManager.selected_note)
			_noteManager.selected_note.unselect();
	});

	// 検索ダイアログ
	$("#searchDialog").dialog({
		autoOpen: false,
		width: 800,
		height: 600,
		zIndex: 99999999,
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

	$("#btnImport").click(function()
	{
		location.href = "./import/";
	});

	$("#btnExport").click(function()
	{
		location.href = "./export/";
	});

	$("#btnNoteAdd").click(function()
	{
		_noteManager.add();
	});

	$("#btnMenu").click(function()
	{
		$.sidr('open');
	});

	$("#btnSearch").click(function()
	{
		if($("#txtKeyword").val()=="")
		{
			top.alert("検索キーワードを入力してください");
			return;
		}
		
		var data = {
			is_cloud	: true,
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
							"/note/?note_id=" + result[i].note_id + 
							"&select_page_id=" + result[i].page_id +
							"&is_cloud=" + ((result[i].is_cloud) ? "true" : "");
						
						$(
							'<tr>'+
								  '<td><a href="' + url + '">' + result[i].note_title + '</a></td>' +
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

	_noteManager.loadData({
		display	:true
	});

	$('#simple-menu').sidr(
	{
		side     : 'right',
		displace : false
	});

});

