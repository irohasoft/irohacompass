/**
 * iroha Note Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2018 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohacompass.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */
 
var DEFAUT_NOTE_COLOR = "#999999";

// -------------------------------------------------
// ノートマネージャクラス
// -------------------------------------------------
function NoteManager()
{
	this.noteList             = new Array();
	this.noteData             = new Array();
	this.selected_note        = null;
}

NoteManager.prototype.loadData = function (params)
{
	$('#sortable').html("");
	
	var data = new Object();
	
	var url  = API_BASE_URL + 'note_list' + API_EXTENSION;
		
	if(params.display)
		$("#divLoader").show();	
		
	$.ajax({
		url		: url,
		type	: 'get',
		dataType: 'xml',
		data    : data,
		timeout	: TIMEOUT_MS,
		success	:
			// XMLデータを取得
			function(xml,status)
			{
				if(status!='success')return;
				
				$('#sortable').html("");
				$('#divMessage').html("ノートが存在しません。<br>新しくノートを追加して下さい。");
				
				$(xml).find('note').each(function()
				{
						var data = {
							note_id		: $(this).find('note_id').text(),
							note_title	: $(this).find('note_title').text(),
							note_color	: $(this).find('note_color').text(),
							is_cloud	: true
						};

						if(data.note_color=="")
							data.note_color = DEFAUT_NOTE_COLOR;

						_noteManager.noteData[_noteManager.noteData.length] = data;

						var note = new Note(data);
					note.display();
					$('#divMessage').html("");
				});
				
				//$('#lstNoteList').trigger('note_loaded');
				$("#divLoader").hide();
			},
		error:
			// エラー
			function(data)
			{
				showCommError(data, 'note_list');
				$("#divLoader").hide();
			}
	});
}




NoteManager.prototype.displayItems = function ()
{
	for(var i=0; i<this.noteData.length; i++)
	{
		var note = new Note(this.noteData[i]);
		note.display();
	}

	$("#divLoader").hide();

	if(this.noteData.length > 0)
		$('#divMessage').html("");
}

NoteManager.prototype.add = function ()
{
	var note_title = prompt("新しいノートのタイトルを入力して下さい");
	var note_id    = getId();
	
	if(!note_title)
		return;
	
	if(note_title=="")
		return;
	
	$("#divLoader").show();
	
	var data = {
		cmd				: "add",
		note_id			: note_id,
		note_title		: note_title,
		note_color		: DEFAUT_NOTE_COLOR
	};
	
	this.sendData({
		is_cloud	:false,
		data		:data,
		reload		:true
	});
}

NoteManager.prototype.remove = function ()
{
	var data = {
		cmd				: "delete",
		note_id			: this.selected_note.note_id
	};
	
	this.sendData({
		is_cloud	:this.selected_note.is_cloud,
		data		:data,
		reload		:true
	});
}

NoteManager.prototype.update = function ()
{
	var data = {
		cmd				: "update",
		note_id			: this.selected_note.note_id,
		note_title		: this.selected_note.note_title,
		note_color		: this.selected_note.note_color
	};
	
	this.sendData({
		is_cloud	:this.selected_note.is_cloud,
		data		:data,
		reload		:true
	});
}

NoteManager.prototype.displayNote = function ()
{
	//var note = new Note($(this));
	
	//note.display();
}

NoteManager.prototype.updateOrder = function ()
{
	var data = {
		cmd				: "order",
		order_list		: $("#sortable").sortable("toArray")
	};
	
	this.sendData({
		is_cloud	:this.selected_note.is_cloud,
		data		:data,
		reload		:true
	});
}

NoteManager.prototype.sendData = function (params)
{
	$.ajax({
		url		: API_BASE_URL + 'note_control' + API_EXTENSION,
		type	: 'post',
		dataType: 'xml',
		data    : params.data,
		timeout	: TIMEOUT_MS,
		success	:
			// XMLデータを取得
			function(xml,status)
			{
				if(status!='success')return;
				
				if(checkError(xml))
				{
						if(params.reload)
						{
							_noteManager.loadData({
								display : true
							});
						}
						
						if(params.complete)
							params.complete(data, xml);
				}
			},
		error:
			// エラー
			function(data)
			{
				showCommError(data, 'note_control');
			}
	});
}

NoteManager.prototype.refreshAllColorPickers = function(rgb)
{
	var list = Util.getLocalStorage(LS_RECENT_COLOR);

	if(list.indexOf(rgb) == -1)
		list.unshift(rgb);

	list = list.slice(0, 6);
	Util.setLocalStorage(LS_RECENT_COLOR, list);

	for (var leaf_id in this.leafList)
	{
		if(this.leafList[leaf_id])
		{
			this.leafList[leaf_id].setColorPiclor();
		}
	}
}

NoteManager.prototype.search = function (params)
{
	var page_info_list = _noteManager.getPageInfoList(params.data.note_id);
	var result         = new Array();
	var keyword        = params.data.keyword;
	var note_id        = (params.data.note_id) ? params.data.note_id : "";
	
	$.ajax({
		url		: API_BASE_URL + 'search_list' + API_EXTENSION,
		type	: 'get',
		dataType: 'xml',
		data	: params.data,
		timeout	: TIMEOUT_MS,
		success	:
			// XMLデータを取得
			function(xml,status)
			{
				if(status!='success')return;
				
				if(checkError(xml))
				{
					var item = new Array();
					
					$(xml).find('leaf').each(function()
					{
						var item = {
							note_id		: $(this).find('note_id').text(),
							note_title	: $(this).find('note_title').text(),
							page_id		: $(this).find('page_id').text(),
							page_title	: $(this).find('page_title').text(),
							leaf_id		: $(this).find('leaf_id').text(),
							leaf_title	: $(this).find('leaf_title').text(),
							is_cloud	: true
						};
						
						result[result.length] = item;
					});
					
					if(params.complete)
						params.complete(result);
				}
			},
		error:
			// エラー
			function(data)
			{
				showCommError(data, 'search_list');
			}
	});
}

NoteManager.prototype.getPageInfoList = function (note_id)
{
	var note_list = Util.getLocalStorage(LS_PREFIX + 'note_list');
	var temp_list = new Array();
	var page_info_list = new Array();
	
	for (var i = 0; i < note_list.length; i++)
	{
		if((note_id)&&(note_list[i].note_id != note_id))
			continue;
		
		var tree = Util.getLocalStorage(_pageManager.getKey(note_list[i].note_id));
		var list = _noteManager.getList(tree);
		
		for (var j = 0; j < list.length; j++)
		{
			var info = new Array();
			
			info.note_id	= note_list[i].note_id;
			info.note_title	= note_list[i].note_title;
			
			info.page_id	= list[j].id;
			info.page_title	= list[j].name;
			
			page_info_list["page_" + list[j].id] = info;
		}
		
		temp_list = temp_list.concat(list);
	}
	
	return page_info_list;
}

// -------------------------------------------------
// ノートクラス
// -------------------------------------------------
function Note(data)
	{
	if(!data)
		return;
	
	this.note_id		= data.note_id;
	this.note_title		= data.note_title;
	this.note_color		= data.note_color;
	this.is_cloud		= data.is_cloud ? true : false;

	_noteManager.noteList["note_" + this.note_id] = this;
}

Note.prototype.display = function ()
{
	var zIndex = getId();
	
	// var note_id		= $(this).find('note_id').text();
	// var note_title	= $(this).find('note_title').text();
	
	$('#sortable').append(
		'<li id="note_' + this.note_id + '" class="ui-state-default">' +
		'  <div class="clsNoteContainer">' + 
		'    <div class="clsNoteMainContainer">' + 
		'      <div class="clsNoteTitle">' + this.note_title + '</div>' + 
		'    </div>' + 
		'    <div class="clsNoteMenuContainer">' + 
		'      <div class="clsNoteMenu">' + 
		'        <input class="clsColorPicker" type="text" >' +
		'        <span class="ai-button clsNoteEditButton" title="ノートのタイトルを編集します">' +
		'            <span class="glyphicon glyphicon-check"></span> 編集' +
		'        </span>' + 
		'      </div>' + 
		'      <div class="clsNoteDelete">' + 
		'        <span class="clsNoteDeleteButton ai-close"></span>' + 
		'      </div>' + 
		'      <span class="ai-button clsNoteOpenButton" title="このノートを開きます">開く</span>' + 
		'    </div>' + 
		'    <div class="clsNoteColorBar"></div>' + 
		'  </div>' + 
		'</li>'
	);
	$('#sortable').val(this.note_id);
	
	this.note_name				= "note_" + this.note_id;
	this.clsNoteContainer		= $( "#note_" + this.note_id );
	this.clsNoteMenuContainer	= $( "#note_" + this.note_id + " .clsNoteMenuContainer");
	this.clsNoteMenu			= $( "#note_" + this.note_id + " .clsNoteMenu");
	this.clsNoteMainContainer	= $( "#note_" + this.note_id + " .clsNoteMainContainer");
	this.clsNoteGrid			= $( "#note_" + this.note_id + " .clsNoteGrid");
	this.clsNoteTitle			= $( "#note_" + this.note_id + " .clsNoteTitle");
	this.clsNoteContent			= $( "#note_" + this.note_id + " .clsNoteContent" );
	this.clsNoteEditButton		= $( "#note_" + this.note_id + " .clsNoteEditButton");
	this.clsNoteLinkButton		= $( "#note_" + this.note_id + " .clsNoteLinkButton");
	this.clsNoteOpenButton		= $( "#note_" + this.note_id + " .clsNoteOpenButton");
	this.clsNoteDelete			= $( "#note_" + this.note_id + " .clsNoteDelete");
	this.clsNoteDeleteButton	= $( "#note_" + this.note_id + " .clsNoteDeleteButton");
	this.clsNoteSaveButton		= $( "#note_" + this.note_id + " .clsNoteSaveButton");
	this.clsBottomRow			= $( "#note_" + this.note_id + " .clsBottomRow");
	this.clsNoteCover			= $( "#note_" + this.note_id + " .clsNoteCover");
	this.clsNoteColorBar		= $( "#note_" + this.note_id + " .clsNoteColorBar");
	this.clsColorPicker			= $( "#note_" + this.note_id + " .clsColorPicker");
	this.displayCount           = 0;
	
	this.clsColorPicker.spectrum({
		showPaletteOnly: true,
		showPalette:true,
		color: this.note_color,
		palette: [
			['#FFFFFF',  '#FFFFFF',  '#FFFFFF',  '#FFFFFF',  '#FFFFFF',  '#FFFFFF'],  
			['#000000',  '#333333',  '#666666',  '#999999',  '#CCCCCC',  '#FFFFFF'],  
			['#000033',  '#003333',  '#006633',  '#009933',  '#00CC33',  '#00FF33'],  
			['#660000',  '#663300',  '#666600',  '#669900',  '#66CC00',  '#66FF00'],  
			['#660066',  '#663366',  '#666666',  '#669966',  '#66CC66',  '#66FF66'],  
			['#6600CC',  '#6633CC',  '#6666CC',  '#6699CC',  '#66CCCC',  '#66FFCC'],  
			['#0000CC',  '#0033CC',  '#0066CC',  '#0099CC',  '#00CCCC',  '#00FFCC'],  
			['#3300FF',  '#3333FF',  '#3366FF',  '#3399FF',  '#33CCFF',  '#33FFFF'],  
			['#CC0066',  '#CC3366',  '#CC6666',  '#CC9966',  '#CCCC66',  '#CCFF66'],  
			['#FF0033',  '#FF3366',  '#FF6666',  '#FF9966',  '#FFCC66',  '#FFFF66'],  
			['#FF00CC',  '#FF33CC',  '#FF66CC',  '#FF99CC',  '#FFCCCC',  '#FFFFCC']
		]
		/*,
		change: function(color) {
		}
		*/
	});

	this.setColorPiclor();
	this.clsColorPicker.bind('change', {note: this}, function(e, color)
	{
		var note = e.data.note;
		var rgb  = color.toHexString();

		note.note_color = color.toHexString();
		note.clsNoteColorBar.css('background-color', rgb);
		note.updateColor();

		_noteManager.refreshAllColorPickers(rgb);
	});

	this.clsNoteColorBar.css('background-color', this.note_color);

	var t = this;
	var interval = (Util.isPC()) ? 200 : 1000;
	
	setInterval(function(){
		if(t.displayCount==0)
			return;
		
		if(t.displayCount==1)
		{
			t.clsNoteMenu.hide( 'blind', {direction: "down"}, 500 );
			t.clsNoteDelete.hide();
			t.clsNoteOpenButton.fadeOut();
		}
		
		t.displayCount--;
		
	}, interval);
	
	this.clsNoteContainer.bind('click', {note: this}, function (evt)
	{
		evt.stopPropagation();
		evt.data.note.select();
	});

	var eventName = (Util.isPC()) ? 'click' : 'touchstart';

	this.clsNoteOpenButton.bind(eventName, {note: this}, function (evt)
	{
		var url = ROOT_PATH + "notes/note/" + evt.data.note.note_id;

		//if(evt.data.note.is_cloud)
		//	url += "&is_cloud=true"

		location.href = url;
	});

	this.clsNoteDeleteButton.bind('mousedown', {note: this}, function (evt)
	{
		evt.stopPropagation();
	});

	this.clsNoteDeleteButton.bind(eventName, {note: this}, function (evt)
	{
		evt.stopPropagation();
		
		var note = evt.data.note;
		
		if(confirm("ノート「" + note.note_title +  "」を削除しますか？\n注意：ノートに含まれるページ、リーフも全て削除されます。"))
		{
			//_noteManager.selected_note = note;
			_noteManager.remove();
		}
	});

	this.clsNoteEditButton.bind(eventName, {note: this}, function (evt)
	{
		evt.stopPropagation();
		
		var note = evt.data.note;
		var note_title = prompt("ノートのタイトルを入力して下さい", note.note_title);
		
		if(!note_title)
			return;
		
		if(note_title=="")
			return;
		
		note.note_title = note_title;
		
		_noteManager.update();
	});
	
	this.clsNoteContainer.bind('mousemove', {note: this}, function(evt)
	{
		evt.data.note.displayCount = 10;
	});
	
	this.clsNoteContainer.bind('mouseenter', {note: this}, function(evt)
	{
		evt.data.note.select();
		evt.data.note.clsNoteMenu.show( 'blind', {direction: "down"}, 200 );
		evt.data.note.clsNoteDelete.show();
		evt.data.note.clsNoteOpenButton.fadeIn();
	});
}

Note.prototype.setColorPiclor = function ()
{
	var list = Util.getLocalStorage(LS_RECENT_COLOR);

	this.clsColorPicker.spectrum({
		showPaletteOnly: true,
		showPalette:true,
		color: this.note_color,
		palette: [
			list,  
			['#000000',  '#333333',  '#666666',  '#999999',  '#CCCCCC',  '#FFFFFF'],  
			['#000033',  '#003333',  '#006633',  '#009933',  '#00CC33',  '#00FF33'],  
			['#660000',  '#663300',  '#666600',  '#669900',  '#66CC00',  '#66FF00'],  
			['#660066',  '#663366',  '#666666',  '#669966',  '#66CC66',  '#66FF66'],  
			['#6600CC',  '#6633CC',  '#6666CC',  '#6699CC',  '#66CCCC',  '#66FFCC'],  
			['#0000CC',  '#0033CC',  '#0066CC',  '#0099CC',  '#00CCCC',  '#00FFCC'],  
			['#3300FF',  '#3333FF',  '#3366FF',  '#3399FF',  '#33CCFF',  '#33FFFF'],  
			['#CC0066',  '#CC3366',  '#CC6666',  '#CC9966',  '#CCCC66',  '#CCFF66'],  
			['#FF0033',  '#FF3366',  '#FF6666',  '#FF9966',  '#FFCC66',  '#FFFF66'],  
			['#FF00CC',  '#FF33CC',  '#FF66CC',  '#FF99CC',  '#FFCCCC',  '#FFFFCC']
		]
		/*,
		change: function(color) {
		}
		*/
	});
}

Note.prototype.select = function ()
{
	if(_noteManager.selected_note)
	{
		_noteManager.selected_note.unselect();
	}
	
	_noteManager.selected_note = this;
	this.clsNoteMenuContainer.show();
}

Note.prototype.unselect = function ()
{
	this.clsNoteMenuContainer.hide();
}

Note.prototype.updateColor = function ()
{
	var data = {
		cmd				: "color",
		note_id			: this.note_id,
		note_color		: this.note_color
	};
	
	_noteManager.sendData({
		is_cloud	:this.is_cloud,
		data		: data
	});
}
