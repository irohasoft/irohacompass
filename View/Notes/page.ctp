<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
	<title>iroha Note Cloud</title>
	<meta http-equiv="Task-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="Task-Style-Type" content="text/css" />
	<meta http-equiv="Task-Script-Type" content="text/javascript" />
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta name="viewport" content="width=device-width,user-scalable=1, minimal-ui" />
	<link rel="stylesheet" href="<?php echo $this->Html->webroot;?>css/irohanote/jquery-ui-1.10.3.custom.css">
	<link rel="stylesheet" href="<?php echo $this->Html->webroot;?>css/irohanote/spectrum.css">
	<link rel="stylesheet" href="<?php echo $this->Html->webroot;?>css/irohanote/common.css?20190210">
	<link rel="stylesheet" href="<?php echo $this->Html->webroot;?>css/irohanote/note.css?20190210">
	<link rel="stylesheet" href="<?php echo $this->Html->webroot;?>css/irohanote/note_mobile.css">
	<script>
	var THEME_ROOT_PATH	= '<?php echo $this->Html->webroot;?>';
	var API_BASE_URL	= '<?php echo $this->Html->webroot;?>notes/';
	var API_EXTENSION	= '';
	var _page_id    = '<?php echo $page_id;?>';
	var _isReadOnly = <?php echo ($mode=='edit') ? 'false' : 'true';?>;
	
	</script>
	<script type="text/javascript" src="<?php echo $this->Html->webroot;?>js/irohanote/common.js?20190210"></script>
	<script type="text/javascript" src="<?php echo $this->Html->webroot;?>js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="<?php echo $this->Html->webroot;?>js/jquery-ui-1.9.2.min.js"></script>
	<script type="text/javascript" src="<?php echo $this->Html->webroot;?>js/irohanote/jquery.jsPlumb-1.5.2-min.js"></script>
	<script type="text/javascript" src="<?php echo $this->Html->webroot;?>js/irohanote/spectrum.js"></script>
	<script type="text/javascript" src="<?php echo $this->Html->webroot;?>js/irohanote/css_touch.js"></script>
	<script type="text/javascript" src="<?php echo $this->Html->webroot;?>js/irohanote/irohanote.note.js?20190210"></script>
	<script type="text/javascript" src="<?php echo $this->Html->webroot;?>js/irohanote/irohanote.page.js?20190210"></script>
	<script type="text/javascript" src="<?php echo $this->Html->webroot;?>js/irohanote/irohanote.leaf.js?20190210"></script>
	<script type="text/javascript" src="<?php echo $this->Html->webroot;?>js/irohanote/irohanote.link.js?20190210"></script>
	<script type="text/javascript" src="<?php echo $this->Html->webroot;?>js/irohanote/note.js?20190210"></script>
</head>
<body>
<div id="ai-navi">
	<div class="ai-navi-space ai-left" style="width:20px"></div>
	<div id="lblNoteTitle" class="ai-navi-item ai-left"><font color="lightgray">ノート名を取得中...</font></div>
	<div id="btnPageList" class="ai-navi-item ai-right ai-navi-menu" style="display:none;" title="選択されているページを公開・共有します">
		ページ一覧
	</div>
	<!--
	<div class="ai-navi-sepa ai-right"></div>
	-->
	<div id="btnMenu" class="ai-navi-item ai-right ai-navi-sidr" title="">
		<img src="<?php echo $this->Html->webroot;?>css/irohanote/images/menu_48_48.png" width="15">
	</div>
	<div class="ai-navi-sepa ai-right"></div>
	<div id="note_search" class="ai-navi-item ai-right" style="width:160px;padding:4px;border:0;">
		<input type="text"   id="txtKeyword" placeholder="ノート内検索">
		<input type="button" id="btnSearch"  value="検索">
	</div>
</div>
<button id="btnFullscreen"><img src="<?php echo $this->Html->webroot;?>css/irohanote/images/fullscreen.png"></button>
<button id="btnFullscreenExit"><img src="<?php echo $this->Html->webroot;?>css/irohanote/images/fullscreen_exit.png"></button>
<div id="sidr">
	<ul>
		<li><span id="btnPageShare" title="選択されているページを公開・共有します">公開設定</span></li>
		<div style="text-align:center; padding:10px;"> <a href="javascript:$.sidr();">
			<span class="ai-button">☓ 閉じる</span></a>
		</div>
	</ul>
</div>
<div id="stage">
	<img id="imgLogo" src="<?php echo $this->Html->webroot;?>css/irohanote/images/irohanotecloud.png">
	<span id="lblPageTitle"></span>
	<span id="btnPageTitleEdit" class="ai-button">編集</span>
	<div id="map">
	</div>
	<span id="btnLeafAdd" class="ai-button" title="画面上に新規カードリーフが追加されます">
		<img src="<?php echo $this->Html->webroot;?>css/irohanote/images/icon_add_card.gif">
		<div class='ai-button-title'>カード</div>
	</span>
	<span id="btnLeafWebAdd" class="ai-button" title="画面上に新規Webリーフが追加されます">
		<img src="<?php echo $this->Html->webroot;?>css/irohanote/images/icon_add_web.gif">
		<div class='ai-button-title'>Web</div>
	</span>
	<div id="divLinkMessage">リンク先のリーフを選択してください。キャンセルする場合には何もないところをクリック（タッチ）してください。</div>
	<div id="divLinkTarget"></div>
	<div id="divLinkDelete"><span id="btnLinkDelete" class="ai-button"><img src="<?php echo $this->Html->webroot;?>css/irohanote/images/btnTrash.png"/>削除</span></div>
	<div class="button-container">
		<p>画面イメージ保存のため、閉じるボタンを使用して閉じてください。</p>
		<button id="btnClose" type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"><span class="ui-button-text">閉じる</span></button>
	</div>
</div>
<div id="divLoader"><img src="<?php echo $this->Html->webroot;?>css/irohanote/images/loader_icon.gif"></div>
<div id="pageShareDialog" style="display:none;" title="公開設定">
	<p>
		<br>
		<input id="chkPageShare" type="checkbox"> ページを外部公開する<br> <font size="2" color="#666">(オンにすると表示中のページが外部から閲覧できるようになります。)</font><br>
		<br>
		<div id="divPageShareContainer" style="display:none;">
			URL <font size="2" color="#666">(メールやSNSでページのURLを送信できます。※編集はできません。)</font><br>
			<textarea id="txtPageShareURL" style="width:100%;height:50px;" readonly onclick="this.focus();this.select()"></textarea>
			<br>
			<br>
			<font size="2" color="#666">Facebook、及び Twitter に作成したページのリンクを投稿することができます。</font>
			<div id="divPageShareSNSContainer">
			</div>
		</div>
	</p>
</div>
<div id="webLeafDialog" style="display:none;" title="Web リーフ">
	<p>
		<br>
		Web リーフとして登録するURLを入力して下さい。
		<br>
		<br>
		<textarea id="txtWebLeafURL" style="width:100%;height:50px;"></textarea>
	</p>
</div>
<div id="showAllDialog" style="display:none;" title="全文表示">
	<p>
	</p>
</div>

</body>
</html>
