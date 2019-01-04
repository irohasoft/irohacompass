/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2018 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohacompass.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */
 
// -------------------------------------------------
// ページマネージャクラス
// -------------------------------------------------
function PageManager()
{
	this.pageList      = new Array();
	this.pageData      = new Array();
	this.selectedNode  = null;
}

PageManager.prototype.loadData = function (params)
{
	this.pageData = new Array();
	var data = {
		note_id			: _note_id
	};
	
	var url  = API_BASE_URL + 'page_list' + API_EXTENSION;
	
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
				
				$(xml).find('note').each(function ()
				{
					var note_title = $(this).find('note_title').text();
					
					//$("#lblNoteTitle").text("ノート : " + note_title);
					$("#lblNoteTitle").text(note_title);
				});
				
				//alert(xmlToString(xml));
				$(xml).find('page').each(function()
				{
					var data = {
						id			: $(this).find('page_id').text(),
						name		: $(this).find('page_title').text(),
						parent_id	: $(this).find('parent_id').text(),
						open_level	: $(this).find('open_level').text()
					};

					_pageManager.pageData[_pageManager.pageData.length] = data
					// var data = new Page();
					// var page = new Page($(this));
					// page.display();
				});
				
				_pageManager.pageData = getTreeByList(_pageManager.pageData);

				if(params.display)
					_pageManager.displayItems();
				// $('#divPageList').trigger('page_loaded');
			},
		error:
			// エラー
			function(data)
			{
				showCommError(data, 'page_list');
			}
	});
}

PageManager.prototype.displayItems = function ()
{
	$("#tree").tree({
		data		: this.pageData,
		dragAndDrop	: true,
		saveState	: false,
		onCreateLi: function(node, $li) {
			// Add 'icon' span before title
			// $li.find('.jqtree-title').before('<span class="icon"></span>');
			$li.addClass("jqtree-node-" + node.id);
			$li.data("id", node.id)
		}
	});

	$('#tree').bind(
		'tree.select',
		function(event) {
			if (event.node) {
				var node = event.node;
				var selectedNode = _pageManager.selectedNode;

				// 前回と同じページの場合、再ロードしない
				if(selectedNode)
					if(selectedNode.id==node.id)
						return;

				_pageManager.selectedNode = node;
				_linkManager.clear();
				_leafManager.clear();
				_leafManager.loadData({
					display		: true,
					page_id		: node.id
				});

				this.page_title = node.name;
				$("#lblPageTitle").html(this.page_title);

				//localStorage.setItem(LS_PREFIX + "selected_node", node.id);

				Util.setLocalStorage(LS_PREFIX + "node_state_" + _note_id, $('#tree').tree("getState"));
			}
			else {
				// event.node is null
				// a node was deselected
				// e.previous_node contains the deselected node
				_pageManager.selectNode(event.previous_node.id);
			}
		}
	);

	$('#tree').bind(
		'tree.dblclick',
		function(event) {
			_pageManager.edit(event.node.id, event.node.name);
		}
	);

	$('#tree').bind(
		'mousedown',
		function(event) {
			event.preventDefault();
			event.stopImmediatePropagation();
			event.stopPropagation();
		}
	);

	$('#tree').bind(
		'tree.move',
		function(event) {
			// 移動後の状態を保存
			event.preventDefault();
			event.move_info.do_move();

			// if(event.move_info.position=="inside")
			// {
			// 	var data = {
			// 		cmd				: "move",
			// 		page_id			: event.move_info.moved_node.id,
			// 		parent_id		: event.move_info.target_node.id
			// 	};
			// }
			// else
			// {
				var parent_id = "";
				var order_list = new Array();
				var children = event.move_info.moved_node.parent.children;

				for (var i = 0; i < children.length; i++) {
					order_list[order_list.length] = children[i].id;
				};

				if(event.move_info.position=="inside")
				{
					parent_id = event.move_info.target_node.id;
				}
				else
				{
					var node_parent = event.move_info.target_node.parent;
					parent_id = node_parent ? node_parent.id : "";
				}
				
				var data = {
					cmd				: "order",
					page_id			: event.move_info.moved_node.id,
					parent_id		: parent_id,
					order_list		: order_list
				};
			// }

			_pageManager.sendData({
				data	: data,
			});
		}
	);

	var node_state = Util.getLocalStorage(LS_PREFIX + "node_state_" + _note_id);

	if(_page_id=="")
	{
		if(
			(node_state)&&
			(node_state!="")
		)
		{
			//this.selectNode(localStorage.getItem(LS_PREFIX + "selected_node"));
			this.selectNode(node_state.selected_node);
			console.log("node_state::" + node_state);
			$('#tree').tree("setState", node_state);
		}
		else
		{
			this.selectFirst();
		}
	}
	else
	{
		this.selectNode(_page_id);
	}

	$(".jqtree-element").droppable({
		accept		: '.clsLeafContainer',
		hoverClass	: 'droppable-hover',
		tolerance	: 'pointer',
		drop		: function(event,uis)
		{
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
			}
		}
	});
}

PageManager.prototype.add = function ()
{
	var page_title = prompt("ページのタイトルを入力して下さい", "");
	var page_id    = getId();
	
	if(!page_title)
		return;
	
	if(page_title=="")
		return;
	
	var data = {
		note_id			: _note_id,
		cmd				: "add",
		page_id			: page_id,
		page_title		: page_title
	};
	
	var data = {
		cmd				: "add",
		page_id			: page_id,
		page_title		: page_title
	};
	
	this.sendData({
		data		: data,
		complete	: function(data){
			// ページが選択されていない場合
			if(_pageManager.getSelectedNode()==false)
			{
				$('#tree').tree(
					'appendNode',
					{
						id		: data.page_id,
						label	: data.page_title
					}
				);
			}
			else
			{
				$('#tree').tree(
					'addNodeAfter',
					{
						id		: data.page_id,
						label	: data.page_title
					},
					_pageManager.selectedNode
				);
			}
			_pageManager.selectNode(data.page_id);
		}
	});
}

PageManager.prototype.remove = function ()
{
	var node = this.getSelectedNode();

	if(!node)
		return;

	var page_id    = node.id;
	var page_title = node.name;
	
	if(!confirm("「" + page_title + "」を削除してもよろしいですか？"))
		return;
	
	var data = {
		cmd				: "delete",
		page_id			: page_id
	};
	
	this.sendData({
		data		: data,
		complete	: function(){
			$('#tree').tree('removeNode', _pageManager.getSelectedNode());
			_pageManager.selectFirst();
		}
	});
}

PageManager.prototype.selectFirst = function ()
{
	var node = $('#tree').tree('getTree').children[0];
	
	if(!node)
		return;
	
	this.selectNode(node.id);
}

PageManager.prototype.selectNode = function (page_id)
{
	var node = $("#tree").tree('getNodeById', page_id);
	$("#tree").tree('selectNode', node);

	localStorage.setItem(LS_PREFIX + "selected_node", page_id);
}

PageManager.prototype.edit = function (page_id, page_title)
{
	var new_title = prompt("新しいタイトルを入力して下さい", page_title);
	
	if(!new_title)
		return;
	
	if(new_title=="")
		return;
	
	_pageManager.update(page_id, new_title);
}

PageManager.prototype.update = function (page_id, page_title)
{
	var data = {
		cmd				: "update",
		page_id			: page_id,
		page_title		: page_title
	};
	
	this.sendData({
		data		: data,
		complete	: function(data, xml){
			_pageManager.updateNode(_pageManager.selectedNode, data.page_title);
			$("#lblPageTitle").text(data.page_title);
		}
	});
}

PageManager.prototype.updateOrder = function ()
{
	var data = {
		cmd				: "zorder",
		order_list		: $("#sortable").sortable("toArray")
	};
	
	this.sendData({
		data	: data
	});
}

PageManager.prototype.setOpenStatus = function (open_level, callback)
{
	var data = {
		cmd				: "open",
		page_id			: _pageManager.selectedNode.id,
		open_level		: open_level
	};
	
	this.sendData({
		data		: data,
		complete	: callback
	});
}

PageManager.prototype.sendData = function (params)
{
	var data = {
		note_id			: _note_id
	};
	
	var data = $.extend(data, params.data);
	
	$.ajax({
		url		: API_BASE_URL + 'page_control' + API_EXTENSION,
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
				showCommError(data, 'page_control');
			}
	});
}

PageManager.prototype.getNodeById = function(page_id)
{
	var node = $("#tree").tree('getNodeById', page_id);

	return node;
}

PageManager.prototype.getSelectedNode = function(page_id)
{
	var node = $("#tree").tree('getSelectedNode');
	return node;
}

PageManager.prototype.updateNode = function(node, page_title)
{
	$("#tree").tree('updateNode', node, page_title);
	return node;
}

PageManager.prototype.save = function()
{
	localStorage[this.getKey(_note_id)] = $('#tree').tree('toJson');
}

PageManager.prototype.getKey = function(note_id)
{
	return LS_PREFIX + 'page_list_' + note_id;
}