/**
 * iroha Note Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */
 
// -------------------------------------------------
// リンクマネージャクラス
// -------------------------------------------------
function LeafLinkManager()
{
	this.linkList    = new Array();
	this.linkData    = new Array();
	this.linkMode    = false;
	this.linkSource  = null;
	this.linkNavi    = null;
	this.isReadOnly  = false;

	this.deleteTargetLink = null;
	this.deleteTarget = null;
	
	this.linkPaintStyle = {
			lineWidth		: 5,
			strokeStyle		: "#666",
			outlineColor	: "transparent", 
			outlineWidth	: 30
		};

	this.linkSelectPaintStyle = {
			lineWidth		: 7,
			strokeStyle		: "#000",
			outlineColor	: "transparent", 
			outlineWidth	: 30
		};

	this.linkParam = {
		anchors		: [ "Center", "Center" ],
		endpoints	: ["Blank", "Blank" ],
		connector	: "Straight",
		paintStyle	: this.linkPaintStyle,
		reattach	: true
	};


	this.linkNaviParam = {
		anchors		: [ "Center", "Center" ],
		endpoints	: ["Blank", "Blank" ],
		connector	: "Straight",
		paintStyle	: {
			lineWidth		: 5,
			strokeStyle		:"#FFFF00"
		},
		reattach	: true
	};
}

LeafLinkManager.prototype.loadData = function (params)
{
	var data = {
		note_id			: _note_id,
			page_id		: params.page_id
	};
	
	$.ajax({
		url		: API_BASE_URL + 'link_list' + API_EXTENSION,
		type	: 'get',
		dataType: 'xml',
		data    : data,
		timeout	: TIMEOUT_MS,
		success	:
			// XMLデータを取得
			function(xml,status)
			{
				if(status!='success')return;
				
				$(xml).find('link').each(function (){
					var link = new LeafLink(
						$(this).find('link_id').text(),
						"leaf_" + $(this).find('leaf_id').text(),
						"leaf_" + $(this).find('leaf_id2').text()
					);
				});
			},
		error:
			// エラー
			function(data)
			{
				showCommError(data, 'link_list');
			}
	});
}

LeafLinkManager.prototype.displayItems = function ()
{
	for (var i = 0; i < this.linkData.length; i++)
	{
		var link = new LeafLink(
			this.linkData[i].link_id,
			"leaf_" + this.linkData[i].leaf_id,
			"leaf_" + this.linkData[i].leaf_id2
		);
	}
}

LeafLinkManager.prototype.unselect = function ()
{
	if(_linkManager.deleteTargetLink)
		_linkManager.deleteTargetLink.unselect();
}

LeafLinkManager.prototype.clear = function ()
{
	jsPlumb.reset();
}

LeafLinkManager.prototype.hideNavi = function ()
{
	// リンク中状態を解除
	if(_linkManager.linkNavi)
	{
		try
		{
			jsPlumb.detach(_linkManager.linkNavi);
			_linkManager.linkSource.clsLeafMainContainer.css("border", "none");
			$("#divLinkMessage").hide();
		}
		catch(e)
		{
			alert();
		}
		
		_linkManager.linkNavi = null;
	}
}

LeafLinkManager.prototype.hideSelection = function ()
{
	var connections = jsPlumb.getConnections();
	
	for(var i=0; i<connections.length; i++)
	{
		connections[i].setPaintStyle(_linkManager.linkPaintStyle);
	}
}

LeafLinkManager.prototype.sendData = function (params)
{
	var data = {
		note_id			: _note_id
	};
	
		var data = $.extend(data, params.data);
	
	$.ajax({
		url		: API_BASE_URL + 'link_control' + API_EXTENSION,
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
				showCommError(data, 'link_control');
			}
	});
}

// -------------------------------------------------
// リンククラス
// -------------------------------------------------
function LeafLink(link_id, link_from, link_to)
{
	this.link_id	= link_id;
	this.leaf_name	= link_from;
	this.leaf_name2	= link_to;
	
	if(
		(!link_id)||
		(!link_from)||
		(!link_to)
	)
		return null;
	
	if(
		($("#"+link_from).size()==0)||
		($("#"+link_to).size()==0)
	)
		return null;
	
	this.con = jsPlumb.connect({
		source: link_from, 
		target: link_to
		},
		_linkManager.linkParam
	);
	
	_linkManager.linkList[this.con.getId()] = this;
	
	// 表示モードの場合
	if(_linkManager.isReadOnly)
		return;
	
	this.con.bind("click", function(con, evt) {
		evt.stopPropagation();
		
		
		$("#" + con.getId()).css("opacity", "0.1");
		
		if(_linkManager.deleteTargetLink)
			_linkManager.deleteTargetLink.unselect();
		
		_linkManager.deleteTargetLink = _linkManager.linkList[con.getId()];
		_linkManager.deleteTargetLink.select(evt);
	});
}

LeafLink.prototype.remove = function ()
{
	var data = {
		cmd				: "delete",
		link_id			: _linkManager.linkList[this.con.getId()].link_id
	};
	
	_linkManager.sendData({
		data	: data
	});
	
	_linkManager.deleteTargetLink = null;
	_linkManager.linkList[this.con.getId()] = null;
	jsPlumb.detach(this.con);
	$("#divLinkDelete").hide();
	
}

LeafLink.prototype.select = function (evt)
{
	this.con.setPaintStyle(_linkManager.linkSelectPaintStyle);
	$("#divLinkDelete").show();
	$('#divLinkDelete').css("top",  (evt.pageY - 20) + "px");
	$('#divLinkDelete').css("left", (evt.pageX - 20) + "px");
}

LeafLink.prototype.unselect = function ()
{
	this.con.setPaintStyle(_linkManager.linkPaintStyle);
	$("#divLinkDelete").hide();
}

LeafLink.prototype.sendData = function (params)
{
	var data = {
		note_id			: _note_id
	};
	
	var data = $.extend(data, params.data);
	
	$.ajax({
		url		: API_BASE_URL + 'link_control' + API_EXTENSION,
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
					if(callback)
						callback(data, xml);
				}
			},
		error:
			// エラー
			function(data)
			{
				showCommError(data, 'link_control');
			}
	});
}

