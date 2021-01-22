/**
 * iroha Note Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */
 
// -------------------------------------------------
//  リーフマネージャクラス
// -------------------------------------------------
function LeafManager($data)
{
	this.leafList    = new Array();
	this.leafData    = new Array();
	this.newLeaf     = null;
	this.max_height  = 0;
	this.max_width   = 0;
	this.LEAF_WIDTH  = 210;
	this.drag_start_top = 0;		// ドラッグ開始位置
	this.drag_start_left = 0;		// ドラッグ開始位置
	this.isReadOnly  = false;
}

LeafManager.prototype.loadData = function (params)
{
	var data = {
		note_id			: _note_id,
		page_id			: params.page_id
	};
	
	var url  = API_BASE_URL + 'leaf_list' + API_EXTENSION;
	
	$("#divLoader").show();
	//alert(url);
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

				$(xml).find('leaf').each(function ()
				{
					var data = {
						leaf_id			: $(this).find('leaf_id').text(),
						leaf_kind		: $(this).find('leaf_kind').text(),
						leaf_title		: $(this).find('leaf_title').text().replace(/'/g, "&#039;"),
						leaf_content	: $(this).find('leaf_content').text().replace(/'/g, "&#039;"),
						leaf_top		: $(this).find('leaf_top').text(),
						leaf_left		: $(this).find('leaf_left').text(),
						leaf_width		: $(this).find('leaf_width').text(),
						leaf_height		: $(this).find('leaf_height').text(),
						leaf_color		: $(this).find('leaf_color').text(),
						isFold			: ($(this).find('leaf_fold').text()=="1") ? true : false
					};

					_leafManager.leafData[_leafManager.leafData.length] = data;
				});

				if(params.display)
					_leafManager.displayItems();

				if(params.complete)
					params.complete(data, xml);

				$(".jqtree-element").droppable({
					accept		: '.clsLeafContainer',
					hoverClass	: 'droppable-hover',
					tolerance	: 'pointer',
					drop		: function(event,uis)
					{
						/*
						var msg	= "<p>"+ uis.draggable[0].id
								+ " が "+this.id
								+ " へドロップされました</p>"
						$(this).append(msg);
						*/
						
						var leaf = _leafManager.leafList[uis.draggable[0].id];
						var node = _pageManager.getNodeById($(this).parent().data("id"));
						
						if(!node)
							return;

						if(_pageManager.selectedNode.id==node.id)
						{
							alert("同じノートへは移動できません");
							return;
						}
						
						if(confirm("ノート(" + node.name + ")へ移動してもしてもよろしいですか？"))
						{
							leaf.change_page(node.id);
							leaf.clsLeafContainer.remove();
							_leafManager.leafList[leaf.id] = null;
						}
					}
				});
			},
		error:
			// エラー
			function(data)
			{
				showCommError(data, 'leaf_list');
				$("#divLoader").hide();
			}
	});
}

LeafManager.prototype.displayItems = function ()
{
	var noteList = Util.getLocalStorage(LS_PREFIX + 'note_list');

	for(var i=0; i<noteList.length; i++)
	{
		if(noteList[i].note_id==_note_id)
		{
			$("#lblNoteTitle").text(noteList[i].note_title);
		}
	}

	for (var i = 0; i < this.leafData.length; i++)
	{
		var leaf = new Leaf(this.leafData[i]);
		
		leaf.display();
	};

	jsPlumb.importDefaults({
		DragOptions : { cursor: "pointer", zIndex:2000 },
		HoverClass:"connector-hover",
		zIndex:20
	});
	
	this.renderBackground();

	_linkManager.loadData({
		page_id	: _pageManager.getSelectedNode().id,
		display : true
	});
	
	$("#divLoader").hide();
}

LeafManager.prototype.add = function (params)
{
	/*
	if(!_pageManager.getSelectedNode())
	{
		alert("リーフ追加の対象となるページが選択されていません。");
		return;
	}
	*/
	
	var leaf    = new Leaf(null);

	leaf.init(params.leaf_kind);
	leaf.leaf_id = getId();

	this.newLeaf = leaf;
	
	if(parseInt(params.leaf_kind)==LEAF_KIND_WEB)
	{
		leaf.leaf_content = params.url;
		
		getWebPageTitle(
			params.url, 
			function(url, title)
			{
				leaf.leaf_title = title;
				
				var data = {
					cmd				: "add",
					leaf_id			: leaf.leaf_id,
					leaf_kind		: leaf.leaf_kind,
					leaf_title		: leaf.leaf_title,
					leaf_content	: leaf.leaf_content,
					leaf_top		: leaf.leaf_top,
					leaf_left		: leaf.leaf_left,
					leaf_width		: leaf.leaf_width,
					leaf_height		: leaf.leaf_height,
					leaf_color		: leaf.leaf_color,
					page_id			: _pageManager.getSelectedNode().id
				};
				

				leaf.sendData({
					data		: data, 
					complete	: function(){
						//leaf.leaf_content = url;
						leaf.display();
					}
				});

			}
		);
	}
	else
	{
		var data = {
			cmd				: "add",
			leaf_id			: leaf.leaf_id,
			leaf_kind		: leaf.leaf_kind,
			leaf_title		: leaf.leaf_title,
			leaf_content	: leaf.leaf_content,
			leaf_top		: leaf.leaf_top,
			leaf_left		: leaf.leaf_left,
			leaf_width		: leaf.leaf_width,
			leaf_height		: leaf.leaf_height,
			leaf_color		: leaf.leaf_color,
			page_id			: _pageManager.getSelectedNode().id
		};
		
		leaf.sendData({
			data		: data,
			complete	: function(){
				_leafManager.newLeaf.display();
				_leafManager.newLeaf.setEditMode(true);
			}
		});
	}
	
}


LeafManager.prototype.copy = function (original_leaf)
{
	var leaf    = new Leaf(null);
	var leaf_id = getId();
	
	leaf.init(original_leaf.leaf_kind);
	leaf.leaf_id   = leaf_id;
	
	leaf.leaf_title		= original_leaf.leaf_title;
	leaf.leaf_content	= original_leaf.leaf_content;
	leaf.leaf_top		= parseInt(original_leaf.leaf_top) + 20;
	leaf.leaf_left		= parseInt(original_leaf.leaf_left) + 20;
	leaf.leaf_width		= original_leaf.leaf_width;
	leaf.leaf_height	= original_leaf.leaf_height;
	leaf.leaf_color		= original_leaf.leaf_color;
	leaf.isFold			= original_leaf.isFold;
	
	var data = {
		cmd				: "add",
		leaf_id			: leaf.leaf_id,
		leaf_kind		: leaf.leaf_kind,
		leaf_title		: leaf.leaf_title,
		leaf_content	: leaf.leaf_content,
		leaf_top		: leaf.leaf_top,
		leaf_left		: leaf.leaf_left,
		leaf_width		: leaf.leaf_width,
		leaf_height		: leaf.leaf_height,
		leaf_color		: leaf.leaf_color,
		fold			: (leaf.isFold) ? "1" : "0",
		page_id			: _pageManager.getSelectedNode().id
	};
	
	this.newLeaf = leaf;
	
	leaf.sendData({
		data		: data,
		complete	: function(){
			_leafManager.newLeaf.display();
			_leafManager.leafList[PREFIX_LEAF + data.leaf_id] = _leafManager.newLeaf;
		}
	});
}

LeafManager.prototype.clear = function ()
{
	$("#map").html("");
	$(".sp-container").remove();
	
	_leafManager = new LeafManager();
	_linkManager = new LeafLinkManager();
}

LeafManager.prototype.renderBackground = function ()
{
	this.max_height = window.innerHeight - 128;// $(window).height() - 128;
	this.max_width  = window.innerWidth;// $(window).width();
	
	for (var leaf in _leafManager.leafList)
	{
		if(this.leafList[leaf])
		{
			var height = this.leafList[leaf].clsLeafContainer.offset().top + this.leafList[leaf].clsLeafContainer.height() - 128;
			var width = this.leafList[leaf].clsLeafContainer.offset().left + this.leafList[leaf].clsLeafContainer.width();
			
			if(height > this.max_height)
				this.max_height = height + 200;
				//this.max_height = height + 128;
				
			if(width > this.max_width)
				this.max_width = width + 50;
		}
	}
	
	$("#stage").height(this.max_height);
	$("#stage").width(this.max_width);
}

LeafManager.prototype.refreshAllColorPickers = function(rgb)
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

LeafManager.prototype.getKey = function (page_id)
{
	return LS_PREFIX + "page_" + page_id;
}


LeafManager.prototype.clearSelection = function ()
{
	for (var leaf_id in this.leafList)
	{
		if(this.leafList[leaf_id])
		{
			this.leafList[leaf_id].unselect();
		}
	}
}

LeafManager.prototype.isMultSelect = function ()
{
	var cnt = 0;
	
	for (var leaf_id in this.leafList)
	{
		if(this.leafList[leaf_id].isSelect())
		{
			cnt++;
		}
	}
	
	return (cnt > 1);
}


// -------------------------------------------------
// リーフクラス
// -------------------------------------------------
function Leaf(data)
{
	if(data)
	{
		this.leaf_id		= data.leaf_id;
		this.leaf_kind		= data.leaf_kind;
		this.leaf_title		= data.leaf_title;
		this.leaf_content	= data.leaf_content;
		this.leaf_top		= data.leaf_top;
		this.leaf_left		= data.leaf_left;
		this.leaf_width		= data.leaf_width;
		this.leaf_height	= data.leaf_height;
		this.leaf_color		= data.leaf_color;
		this.isFold			= data.isFold;
		
		// リーフがページエリアから上、もしくは左にはみ出している場合は座標を0に設定
		if(this.leaf_top < 0)
			this.leaf_top = 0;
			
		if(this.leaf_left < 0)
			this.leaf_left = 0;
	}
	
	this.isEdit = false;
}

Leaf.prototype.init = function (leaf_kind)
{
	this.leaf_id		= 0;
	this.leaf_kind		= leaf_kind;
	this.leaf_title		= "";
	this.leaf_content	= "";
	this.leaf_top		= Math.floor(Math.random()*200+1) + 100;
	this.leaf_left		= Math.floor(Math.random()*300+1) + 200;
	
	switch (parseInt(this.leaf_kind))
	{
		case LEAF_KIND_CARD:
			this.leaf_width		= 240;
			this.leaf_height	= 105;
			this.leaf_color		= "#6699CC";
			break;
		case LEAF_KIND_WEB:
			this.leaf_width		= 180;
			this.leaf_height	= 60;
			this.leaf_color		= "#66CC66";
			break;
		case LEAF_KIND_IMAGE:
			this.leaf_width		= 240;
			this.leaf_height	= 105;
			this.leaf_color		= "#FF9900";
			break;
		case LEAF_KIND_GROUP:
			this.leaf_width		= 380;
			this.leaf_height	= 260;
			this.leaf_color		= "#666666";
			break;
	}
	this.isEdit			= false;
}

Leaf.prototype.display = function ()
{
	var zIndex = getId();
	
	switch (parseInt(this.leaf_kind))
	{
		case LEAF_KIND_CARD:
			$("#map").append(
				"<div id='leaf_" + this.leaf_id + "' class='clsLeafContainer draggable resizable ui-widget-content ui-draggable' style='" +
					"width  : " + this.leaf_width  + "px;" +
					"height : " + this.leaf_height + "px;" +
					"left   : " + this.leaf_left   + "px;" +
					"top    : " + this.leaf_top    + "px;" +
					"z-index : " + zIndex +
					"'>" + 
					"<div class='clsLeafMenuContainer'>" +
					"  <div class='clsLeafMenu'>" +
					"    <input class='clsColorPicker' type='text' >" +
					"    <span class='clsLeafLinkButton ai-button'><img src='" + ROOT_PATH + "images/btnLink.png'/></span>" +
					"    <span class='clsLeafCopyButton ai-button'><img src='" + ROOT_PATH + "images/btnCopy.png'/></span>" +
					"    <span class='clsLeafEditButton ai-button'><img src='" + ROOT_PATH + "images/btnEdit.png'/></span>" +
					"  </div>" + 
					"</div>" + 
					"<div class='clsLeafMainContainer'>" +
					"  <table class='clsLeafGrid'>" + 
					"  <tr height='22'>" + 
					"    <td rowspan='3' width='0px'></td>" + 
					"    <td><input type='text' placeholder='(non title)' value='" + this.leaf_title + "' class='clsLeafTitle'></td>" + 
					"    <td class='clsLeafTitleMargin'></td>" + 
					"  </tr>" + 
					"  <tr height='100%'>" + 
					"    <td colspan='2' valign='top'><textarea placeholder='(none)' class='clsLeafContent'>" + this.leaf_content + "</textarea></td>" + 
					"  </tr>" + 
					"  <tr class='clsBottomRow' height='20'>" + 
					"    <td colspan='2' align='right'>" + 
					"      <span class='clsLeafSaveButton ai-button'><img src='" + ROOT_PATH + "images/btnSave.png'/>保存</span>" +
					"    </td>" + 
					"  </tr>" + 
					"" + 
					"" + 
					"" + 
					"" + 
					"  </table>" + 
					"  <div class='clsLeafColorBar'></div>" + 
					"  <div class='clsLeafCover'></div>" + 
					"  <div class='clsLeafShowAllButton ai-button' style='display:none'>全文表示</div>" +
					"  <div class='clsLeafFoldButton'>▲</div>" + 
					"  <div class='clsLeafDeleteButton ai-close'></div>" + 
					"</div>" + 
				"</div>"
			);
			break;
		case LEAF_KIND_WEB:
			$("#map").append(
				"<div id='leaf_" + this.leaf_id + "' class='clsLeafContainer draggable resizable ui-widget-content ui-draggable' style='" +
					"width  : " + this.leaf_width  + "px;" +
					"height : " + this.leaf_height + "px;" +
					"left   : " + this.leaf_left   + "px;" +
					"top    : " + this.leaf_top    + "px;" +
					"z-index : " + zIndex +
					"'>" + 
					"<div class='clsLeafMenuContainer'>" +
					"  <div class='clsLeafMenu'>" +
					"    <input class='clsColorPicker' type='text' >" +
					"    <span class='clsLeafLinkButton ai-button'><img src='" + ROOT_PATH + "images/btnLink.png'/></span>" +
					"    <span class='clsLeafCopyButton ai-button'><img src='" + ROOT_PATH + "images/btnCopy.png'/></span>" +
					"    <span class='clsLeafEditButton ai-button'><img src='" + ROOT_PATH + "images/btnEdit.png'/></span>" +
					"  </div>" + 
					"</div>" + 
					"<div class='clsLeafMainContainer'>" +
					"  <table class='clsLeafGrid'>" + 
					"  <tr height='22'>" + 
					"    <td rowspan='3' width='0px'></td>" + 
					"    <td><input type='text' placeholder='(non title)' value='" + this.leaf_title + "' class='clsLeafTitle'></td>" + 
					"    <td class='clsLeafTitleMargin'></td>" + 
					"  </tr>" + 
					"  <tr height='32' height='100%'>" + 
					"    <td colspan='2' valign='top' align='center'>" +
					"      <textarea placeholder='(none)' class='clsLeafContent' style='display:none;'>" + this.leaf_content + "</textarea>" +
					"    </td>" + 
					"  </tr>" + 
					"  <tr class='clsBottomRow' height='20'>" + 
					"    <td colspan='2' align='right'><span class='clsLeafSaveButton ai-button'><img src='" + ROOT_PATH + "images/btnSave.png'/>保存</span></td>" + 
					"  </tr>" + 
					"  </table>" + 
					"  <div class='clsLeafColorBar'></div>" + 
					"  <div class='clsLeafCover' title='" + this.leaf_title + "'></div>" + 
					"  <div class='clsLeafWebButton'>" +
					"    <img src='" + ROOT_PATH + "images/icon_list_web.png' title='" + this.leaf_content + "' border=0></div>" + 
					"  <div class='clsLeafFoldButton'>▲</div>" + 
					"  <div class='clsLeafDeleteButton ai-close'></div>" + 
					"  <a href='" + this.leaf_content + "' target='_blank'><div class='clsLeafWebCover'></div></a>" + 
					"</div>" + 
				"</div>"
			);
			break;
		case LEAF_KIND_IMAGE:
			$("#map").append(
				"<div id='leaf_" + this.leaf_id + "' class='clsLeafContainer draggable resizable ui-widget-content ui-draggable' style='" +
					"width  : " + this.leaf_width  + "px;" +
					"height : " + this.leaf_height + "px;" +
					"left   : " + this.leaf_left   + "px;" +
					"top    : " + this.leaf_top    + "px;" +
					"z-index : " + zIndex +
					"'>" + 
					"<div class='clsLeafMenuContainer'>" +
					"  <div class='clsLeafMenu'>" +
					"    <input class='clsColorPicker' type='text' >" +
					"    <span class='clsLeafLinkButton ai-button'><img src='" + ROOT_PATH + "images/btnLink.png'/></span>" +
					"    <span class='clsLeafCopyButton ai-button'><img src='" + ROOT_PATH + "images/btnCopy.png'/></span>" +
					"    <span class='clsLeafEditButton ai-button'><img src='" + ROOT_PATH + "images/btnEdit.png'/></span>" +
					"  </div>" + 
					"</div>" + 
					"<div class='clsLeafMainContainer'>" +
					"  <table class='clsLeafGrid'>" + 
					"  <tr height='22'>" + 
					"    <td rowspan='3' width='0px'></td>" + 
					"    <td><input type='text' placeholder='(non title)' value='" + this.leaf_title + "' class='clsLeafTitle'></td>" + 
					"    <td class='clsLeafTitleMargin'></td>" + 
					"  </tr>" + 
					"  <tr height='32' height='100%'>" + 
					"    <td colspan='2' valign='top' align='center'>" +
					"      <textarea placeholder='(none)' class='clsLeafContent' style='display:none;'>" + this.leaf_content + "</textarea>" +
					"    </td>" + 
					"  </tr>" + 
					"  <tr class='clsBottomRow' height='20'>" + 
					"    <td colspan='2' align='right'><span class='clsLeafSaveButton ai-button'><img src='" + ROOT_PATH + "images/btnSave.png'/>保存</span></td>" + 
					"  </tr>" + 
					"  </table>" + 
					"  <div class='clsLeafColorBar'></div>" + 
					"  <div class='clsLeafCover' title='" + this.leaf_title + "'></div>" + 
					"  <div class='clsLeafImage'  style='background-image:url(" + this.leaf_content + ")'></div>" +
					"  <div class='clsLeafFoldButton'>▲</div>" + 
					"  <div class='clsLeafDeleteButton ai-close'></div>" + 
					"  <a href='" + this.leaf_content + "' target='_blank'><div class='clsLeafWebCover'></div></a>" + 
					"</div>" + 
				"</div>"
			);
			break;
		case LEAF_KIND_GROUP:
			$("#map").append(
				"<div id='leaf_" + this.leaf_id + "' class='clsGroupContainer draggable resizable ui-draggable' style='" +
					"width  : " + this.leaf_width  + "px;" +
					"height : " + this.leaf_height + "px;" +
					"left   : " + this.leaf_left   + "px;" +
					"top    : " + this.leaf_top    + "px;" +
					"'>" + 
					"<div class='clsLeafMenuContainer'>" +
					"  <div class='clsLeafMenu'>" +
					"    <input class='clsColorPicker' type='text' >" +
//					"    <span class='clsLeafLinkButton ai-button'><img src='" + ROOT_PATH + "images/btnLink.png'/></span>" +
					"    <span class='clsLeafEditButton ai-button'><img src='" + ROOT_PATH + "images/btnEdit.png'/></span>" +
					"  </div>" + 
					"</div>" + 
					"<div class='clsLeafMainContainer'>" +
					"  <input type='text' placeholder='(non title)' value='" + this.leaf_title + "' class='clsLeafTitle'>" + 
					"  <div class='clsLeafCover' title='" + this.leaf_title + "'></div>" + 
					"  <div class='clsLeafSave'>" + 
					"    <span class='clsLeafSaveButton ai-button'><img src='" + ROOT_PATH + "images/btnSave.png'/>保存</span>" + 
					"  </div>" + 
					"  <div class='clsLeafDeleteButton ai-close'></div>" + 
					"</div>" + 
				"</div>"
			);
			break;
	}
	
	this.leaf_name				= "leaf_" + this.leaf_id;
	this.clsLeafContainer		= $( "#leaf_" + this.leaf_id );
	this.clsLeafMenuContainer	= $( "#leaf_" + this.leaf_id + " .clsLeafMenuContainer");
	this.clsLeafMenu			= $( "#leaf_" + this.leaf_id + " .clsLeafMenu");
	this.clsLeafMainContainer	= $( "#leaf_" + this.leaf_id + " .clsLeafMainContainer");
	this.clsLeafGrid			= $( "#leaf_" + this.leaf_id + " .clsLeafGrid");
	this.clsLeafTitle			= $( "#leaf_" + this.leaf_id + " .clsLeafTitle");
	this.clsLeafContent			= $( "#leaf_" + this.leaf_id + " .clsLeafContent" );
	this.clsLeafEditButton		= $( "#leaf_" + this.leaf_id + " .clsLeafEditButton");
	this.clsLeafLinkButton		= $( "#leaf_" + this.leaf_id + " .clsLeafLinkButton");
	this.clsLeafCopyButton		= $( "#leaf_" + this.leaf_id + " .clsLeafCopyButton");
	this.clsLeafFoldButton		= $( "#leaf_" + this.leaf_id + " .clsLeafFoldButton");
	this.clsLeafDeleteButton	= $( "#leaf_" + this.leaf_id + " .clsLeafDeleteButton");
	this.clsLeafSaveButton		= $( "#leaf_" + this.leaf_id + " .clsLeafSaveButton");
	this.clsLeafWebButton		= $( "#leaf_" + this.leaf_id + " .clsLeafWebButton");
	this.clsLeafShowAllButton	= $( "#leaf_" + this.leaf_id + " .clsLeafShowAllButton");
	this.clsBottomRow			= $( "#leaf_" + this.leaf_id + " .clsBottomRow");
	this.clsLeafCover			= $( "#leaf_" + this.leaf_id + " .clsLeafCover");
	this.clsLeafColorBar		= $( "#leaf_" + this.leaf_id + " .clsLeafColorBar");
	this.clsLeafWebCover		= $( "#leaf_" + this.leaf_id + " .clsLeafWebCover");
	this.clsLeafTitleMargin		= $( "#leaf_" + this.leaf_id + " .clsLeafTitleMargin");
	this.clsColorPicker			= $( "#leaf_" + this.leaf_id + " .clsColorPicker");
	this.displayCount           = 0;
	this.clsLeafColorBar.css('background-color', this.leaf_color);
	
	if(this.leaf_kind==LEAF_KIND_GROUP)
		this.clsLeafContainer.css('border-color', this.leaf_color);
	
	if(parseInt(this.leaf_kind)!=LEAF_KIND_WEB)
		this.clsLeafContainer.resizable();
	
	if(this.isFold)
		this.fold(false);

	_leafManager.leafList[PREFIX_LEAF + this.leaf_id] = this;
	
	// 表示モードの場合、ここで終了
	if(_leafManager.isReadOnly)
	{
		this.clsLeafWebButton.bind('click', {leaf: this}, function(e)
		{
			window.open(e.data.leaf.getURL());
		});
		
		if(parseInt(this.leaf_kind)!=LEAF_KIND_WEB)
			this.clsLeafContainer.resizable('disable');
		
		return;
	}
	
	var t = this;
	
	var interval = (Util.isPC()) ? 200 : 1000;
	
	setInterval(function(){
		if(t.displayCount==0)
			return;
		
		if(t.displayCount==1)
		{
			t.clsLeafMenu.hide( 'blind', {direction: "down", 
				complete: function()
				{
					t.clsLeafFoldButton.hide();
					t.clsLeafDeleteButton.hide();
					t.clsLeafTitleMargin.width(0);
					t.clsLeafMenuContainer.width(0);
				}
			}, 500 );
		}
		
		t.displayCount--;
		
	}, interval);
	
	this.clsLeafContainer.bind('mousedown', {leaf: this}, function (e)
	{
		var leaf = e.data.leaf;
		var zIndex = getId();
		
		// 他のリーフの選択状態を解除し、指定されたリーフを選択
		if((!e.shiftKey)&&(!e.ctrlKey)&&(!leaf.isSelect()))
			_leafManager.clearSelection();
		
		leaf.select();
		
		// グループの場合、表示順を更新しない
		if(leaf.leaf_kind==LEAF_KIND_GROUP)
			zIndex = zIndex.toString().substr(-5);
		
		// 表示順を更新
		$(this).css({ zIndex: zIndex });
		leaf.updateZOrder();
		
	});
	
	this.clsLeafContainer.mousemove(function()
	{
		jsPlumb.repaintEverything();
	});
	
	this.clsLeafContainer.draggable({
		start: function(event, ui) // ドラッグ開始
		{
			_leafManager.drag_start_top = this.offsetTop;
			_leafManager.drag_start_left = this.offsetLeft;
			console.log('start : ' + this.offsetTop + ', ' + this.offsetLeft);
		},
		drag: function(event, ui) // ドラッグ中
		{
			var leaf = _leafManager.leafList[this.id];
			var diff_top  = _leafManager.drag_start_top  - this.offsetTop;
			var diff_left = _leafManager.drag_start_left - this.offsetLeft;
			
			leaf.leaf_top  = this.offsetTop;
			leaf.leaf_left = this.offsetLeft;
			
			if(_leafManager.isMultSelect())
			{
				for (var leaf_id in _leafManager.leafList)
				{
					if(_leafManager.leafList[leaf_id].isSelect())
					{
						var new_top  = _leafManager.leafList[leaf_id].leaf_top  - diff_top;
						var new_left = _leafManager.leafList[leaf_id].leaf_left - diff_left;
						
						_leafManager.leafList[leaf_id].clsLeafContainer.offset({ top: new_top, left: new_left });
					}
				}
			}
			
			//console.log('diff : ' + diff_top + ', ' + diff_left);
			//top.alert();
		},
		stop: function(event, ui) // ドラッグ修了
		{
			for (var leaf_id in _leafManager.leafList)
			{
				var leaf = _leafManager.leafList[leaf_id];
				
				if(leaf.isSelect())
					leaf.updateLocation();
			}
			
			_leafManager.renderBackground();
		}
	});
	
	if(parseInt(this.leaf_kind)!=LEAF_KIND_WEB)
		this.clsLeafContainer.resizable();
	
	this.clsLeafContainer.bind('resizestop', {leaf: this}, function (e) {
		var leaf = e.data.leaf;
		
		leaf.leaf_width  = e.currentTarget.clientWidth;
		leaf.leaf_height = e.currentTarget.clientHeight;
		leaf.updateSize();
		leaf.renderShowAllButton();
	});
	
	this.clsLeafContainer.bind('resize', {leaf: this}, function (e) {
		e.data.leaf.resizeTextarea();
	});
	
	this.clsLeafContainer.bind('click', {leaf: this}, function(e)
	{
		e.stopPropagation();
	});
	
	this.clsLeafWebButton.bind('click', {leaf: this}, function(e)
	{
		window.open(e.data.leaf.getURL());
	});

	this.setEditMode(false);
	this.resizeTextarea();
	
	var eventName = (Util.isPC()) ? 'click' : 'touchstart';
	
	eventName = 'click';
	
	this.clsLeafCover.bind(eventName, {leaf: this}, function(e)
	{
		if(_linkManager.linkMode)
		{
			_linkManager.linkSource.setLinkTarget(false);
			_linkManager.hideNavi();
			_linkManager.linkMode = false;
			
			if(_linkManager.linkSource.leaf_id==e.data.leaf.leaf_id)
			{
				top.alert("同じリーフにはリンクできません");
				return;
			}
			else
			{
				e.data.leaf.setLinkTarget(false);
				e.data.leaf.linkTo(_linkManager.linkSource);
			}
		}
		
		_linkManager.unselect();
	});
	
	this.clsLeafCover.bind('mouseenter', {leaf: this}, function(e)
	{
		if(_linkManager.linkMode)
		{
			e.data.leaf.setLinkTarget(true);
		}
	});
	
	this.clsLeafCover.bind('mouseout', {leaf: this}, function(e)
	{
		if(_linkManager.linkMode)
		{
			if(e.data.leaf.leaf_id!=_linkManager.linkSource.leaf_id)
				e.data.leaf.setLinkTarget(false);
		}
	});
	
	this.clsLeafContainer.bind('mousemove', {leaf: this}, function(e)
	{
		e.data.leaf.displayCount = 5;
	});
	
	this.clsLeafMainContainer.bind('mouseenter', {leaf: this}, function(e)
	{
		e.data.leaf.clsLeafMenu.show( 'blind', {direction: "down"}, 200 );
		e.data.leaf.clsLeafDeleteButton.show();
		e.data.leaf.clsLeafTitleMargin.width(40);
		e.data.leaf.clsLeafMenuContainer.width(180);
		
		if(!e.data.leaf.isEdit)
		{
			e.data.leaf.clsLeafFoldButton.show();
		}
	});
	
	/*
	this.clsLeafMainContainer.bind('mouseout', {leaf: this}, function(e)
	{
	});
	*/
	
	this.clsLeafCover.bind('dblclick', {leaf: this}, function(e)
	{
		e.data.leaf.setEditMode(true);
	});
	
	this.clsLeafEditButton.bind(eventName, {leaf: this}, function(e)
	{
		e.data.leaf.setEditMode(true);
	});
	
	this.clsLeafLinkButton.bind(eventName, {leaf: this}, function(e)
	{
		e.data.leaf.setLinkMode(true);
		e.data.leaf.setLinkTarget(true);
		e.stopPropagation();
	});
	
	this.clsLeafSaveButton.bind(eventName, {leaf: this}, function(e)
	{
		e.data.leaf.setEditMode(false);
		e.data.leaf.update();
	});
	
	this.clsLeafFoldButton.bind(eventName, {leaf: this}, function(e)
	{
		var leaf = e.data.leaf;
		
		if(leaf.isFold)
		{
			leaf.unfold(true);
		}
		else
		{
			leaf.fold(true);
		}
	});
	
	this.clsLeafDeleteButton.bind(eventName, {leaf: this}, function(e)
	{
		if(top.confirm("削除してもよろしいですか？"))
		{
			e.data.leaf.remove();
			e.data.leaf.clsLeafContainer.remove();
			delete _leafManager.leafList[e.data.leaf.leaf_name];
		}
	});
	
	this.clsLeafCopyButton.bind(eventName, {leaf: this}, function(e)
	{
		_leafManager.copy(e.data.leaf);
	});
	
	this.clsLeafShowAllButton.bind(eventName, {leaf: this}, function(e)
	{
		var leaf = e.data.leaf;

		$("#showAllDialog p").html(leaf.leaf_content.replace(/\n/g, '<br>'));
		$("#showAllDialog").dialog({title: leaf.leaf_title});
		$("#showAllDialog").dialog( "open" );
	});
	
	this.setColorPiclor();
	this.clsColorPicker.bind('change', {leaf: this}, function(e, color)
	{
		var leaf = e.data.leaf;
		var rgb  = color.toHexString();

		leaf.leaf_color = color.toHexString();
		leaf.clsLeafColorBar.css('background-color', leaf.leaf_color);
		
		if(leaf.leaf_kind==LEAF_KIND_GROUP)
			leaf.clsLeafContainer.css('border-color', leaf.leaf_color);
		
		leaf.updateColor();

		_leafManager.refreshAllColorPickers(rgb);
	});
}

Leaf.prototype.setColorPiclor = function ()
{
	var list = Util.getLocalStorage(LS_RECENT_COLOR);

	this.clsColorPicker.spectrum({
		showPaletteOnly: true,
		showPalette:true,
		color: this.leaf_color,
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

Leaf.prototype.update = function ()
{
	this.leaf_title   = this.clsLeafTitle.val();
	this.leaf_content = this.clsLeafContent.val();
	
	console.log(this.leaf_title);
	
	var data = {
		cmd				: "update",
		leaf_title		: this.leaf_title,
		leaf_content	: this.leaf_content
	};
	
	this.sendData({
		data	: data
	});
}


Leaf.prototype.remove = function ()
{
	var data = {
		cmd				: "delete",
		leaf_id			: this.leaf_id
	};
	
	this.sendData({
		data	: data
	});
	
	for (var link_con_id in _linkManager.linkList)
	{
		var link = _linkManager.linkList[link_con_id];
		
		if(link)
		{
			if(
				(link.leaf_name ==this.leaf_name)||
				(link.leaf_name2==this.leaf_name)
			)
			{$
				_linkManager.linkList[link_con_id] = null;
				jsPlumb.detach(link.con);
			}
		}
	}
}

Leaf.prototype.change_page = function (page_id)
{
	var data = {
		cmd				: "change_page",
		page_id			: page_id,
		leaf_id			: this.leaf_id
	};
	
	this.sendData({
		data	: data
	});
	
	for (var link_con_id in _linkManager.linkList)
	{
		var link = _linkManager.linkList[link_con_id];
		
		if(link)
		{
			if(
				(link.leaf_name ==this.leaf_name)||
				(link.leaf_name2==this.leaf_name)
			)
			{$
				_linkManager.linkList[link_con_id] = null;
				jsPlumb.detach(link.con);
			}
		}
	}
}

Leaf.prototype.fold = function (is_update)
{
	this.clsLeafContainer.css("min-height", "25px");
	this.clsLeafContainer.css("height", "25px");
	this.clsLeafMainContainer.css("min-height", "25px");
	this.clsLeafMainContainer.css("height", "25px");
	this.clsLeafFoldButton.text("▼");
	this.clsLeafContent.hide();
	this.clsLeafShowAllButton.hide();
	
	if(parseInt(this.leaf_kind)==LEAF_KIND_WEB)
	{
		this.clsLeafWebButton.hide();
		this.clsLeafWebCover.show();
		this.clsLeafTitle.addClass("linked");
	}
	else
	{
		this.clsLeafContainer.resizable("disable");
	}
	
	this.isFold = true;
	
	if(is_update)
		this.updateFold();
	
	jsPlumb.repaintEverything();
}

Leaf.prototype.unfold = function (is_update)
{
	this.clsLeafContainer.css("min-height", "60px");
	this.clsLeafContainer.css("height", this.leaf_height + "px");
	this.clsLeafMainContainer.css("min-height", "60px");
	this.clsLeafMainContainer.css("height", "100%");
	this.clsLeafFoldButton.text("▲");
	
	if(parseInt(this.leaf_kind)==LEAF_KIND_WEB)
	{
		this.clsLeafWebButton.show();
		this.clsLeafWebCover.hide();
		this.clsLeafTitle.removeClass("linked");
	}
	else
	{
		this.clsLeafContainer.resizable("enable");
		this.clsLeafWebButton.hide();
		this.clsLeafContent.show();
		this.resizeTextarea();
		this.renderShowAllButton();
	}
	
	this.isFold = false;
	
	if(is_update)
		this.updateFold();
	
	jsPlumb.repaintEverything();
}

Leaf.prototype.resizeTextarea = function ()
{
	this.clsLeafContent.width( this.clsLeafMainContainer.width()  - 25);
	this.clsLeafContent.height(this.clsLeafMainContainer.height() - 30);
}

Leaf.prototype.renderShowAllButton = function ()
{
	if(this.isEdit)
	{
		this.clsLeafShowAllButton.hide();
		return;
	}

	if(!this.clsLeafContent.get(0))
		return;

	// 内容が枠からはみ出ている場合のみ、全表示ボタンを表示
	if(this.clsLeafContent.get(0).scrollHeight > (this.clsLeafContent.height() + 10))
	{
		this.clsLeafShowAllButton.show();
	}
	else
	{
		this.clsLeafShowAllButton.hide();
	}
}

Leaf.prototype.updateLocation = function ()
{
	this.leaf_top  = this.clsLeafContainer.offset().top;
	this.leaf_left = this.clsLeafContainer.offset().left;
	
	var data = {
		cmd				: "move",
		leaf_top		: this.leaf_top,
		leaf_left		: this.leaf_left
	};
	
	this.sendData({
		data	: data
	});
}

Leaf.prototype.updateSize = function ()
{
	var data = {
		cmd				: "size",
		leaf_width		: this.leaf_width,
		leaf_height		: this.leaf_height
	};
	
	this.sendData({
		data	: data
	});
}

Leaf.prototype.updateZOrder = function ()
{
	var data = {
		cmd				: "zorder"
	};
	
	this.sendData({
		data	: data
	});
}

Leaf.prototype.updateFold = function ()
{
	var data = {
		cmd				: "fold",
		fold			: (this.isFold) ? 1 : 0
	};
	
	this.sendData({
		data	: data
	});
}

Leaf.prototype.updateColor = function ()
{
	var data = {
		cmd				: "color",
		leaf_color		: this.leaf_color
	};
	
	this.sendData({
		data	: data
	});
}

Leaf.prototype.sendData = function (params)
{
	var data = {
		note_id			: _note_id,
		leaf_id			: this.leaf_id
	};
	
		var data = $.extend(data, params.data);
	
	$.ajax({
		url		: API_BASE_URL + 'leaf_control' + API_EXTENSION,
		type	: 'post',
		dataType: 'xml',
		data    : data,
		timeout	: TIMEOUT_MS,
		success	:
			// XMLデータを取得
			function(xml,status)
			{
				if(status!='success')return;
				
				if(checkError(xml))
				{
						if(params.complete)
							params.complete(data, xml);
				}
			},
		error:
			// エラー
			function(data)
			{
				showCommError(data, 'leaf_control');
			}
	});
}

Leaf.prototype.setEditMode = function (isEditMode)
{
	if(isEditMode)
	{
		this.clsLeafCover.hide();
		this.clsBottomRow.show();
		this.clsLeafSaveButton.show();
		this.clsLeafTitle.css("border", "1px solid");
		this.clsLeafContent.css("border", "1px solid");
		this.clsLeafContainer.draggable("disable");
		this.clsLeafFoldButton.hide();
		this.clsLeafTitle.focus();
		if(this.isFold)
			this.unfold(true);
	}
	else
	{
		this.clsLeafCover.show();
		this.clsBottomRow.hide();
		this.clsLeafSaveButton.hide();
		this.clsLeafTitle.css("border", "none");
		this.clsLeafContent.css("border", "none");
		this.clsLeafContainer.draggable("enable");
	}
	
	this.isEdit = isEditMode;
	this.renderShowAllButton();
	/*
	this.clsLeafTitle.css("color", (this.clsLeafTitle.val()=="(non title)") ? "#aaa" : "#333"); 
	this.clsLeafContent.css("color", (this.clsLeafContent.val()=="(none)") ? "#aaa" : "#333"); 
	*/
}

Leaf.prototype.setLinkTarget = function (isTarget)
{
	if(isTarget)
	{
		this.clsLeafMainContainer.css("border", "2px solid #ff0");
	}
	else
	{
		this.clsLeafMainContainer.css("border", "none");
	}
}

Leaf.prototype.setLinkMode = function (isLinkMode)
{
	_linkManager.linkMode   = isLinkMode;
	_linkManager.linkSource = this;
	
	$("#divLinkTarget").css("top",  this.leaf_top  - 30);
	$("#divLinkTarget").css("left", this.leaf_left + 30);

	_linkManager.linkNavi = jsPlumb.connect({
		source : this.leaf_name , 
		target : "divLinkTarget"
		},
		_linkManager.linkNaviParam
	);
	
	$("#divLinkMessage").show();
}


Leaf.prototype.linkTo = function (targetLeaf)
{
	var link_id = getId();
	
	if(_linkManager.linkNavi)
		jsPlumb.detach(_linkManager.linkNavi);
	
	var data = {
		cmd				: "add",
		link_id			: link_id,
		leaf_id			: this.leaf_id,
		leaf_id2		: targetLeaf.leaf_id,
		note_id			: _note_id,
		page_id			: _pageManager.getSelectedNode().id
	};
	
	_linkManager.sendData({
		data		: data, 
		complete	: function(){
		var link = new LeafLink(
			data.link_id, 
			"leaf_" + data.leaf_id, 
			"leaf_" + data.leaf_id2
		);
		}
	});
	_linkManager.linkMode = false;
}


Leaf.prototype.select = function ()
{
	this.clsLeafContainer.addClass('ai-selected');
}

Leaf.prototype.unselect = function ()
{
	this.clsLeafContainer.removeClass('ai-selected');
}

Leaf.prototype.isSelect = function ()
{
	return this.clsLeafContainer.hasClass('ai-selected');
}

// HTMLエンコードされているURLを復元
Leaf.prototype.getURL = function ()
{
	var url = this.leaf_content;
	
	url = url.replace(/&amp;/g, '&');
	
	return url;
}
