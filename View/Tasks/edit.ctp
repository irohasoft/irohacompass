<?php if(!$is_user) echo $this->element('admin_menu');?>
<?php $this->start('css-embedded'); ?>
<?= $this->Html->css('summernote.css');?>
<?php $this->end(); ?>
<?php $this->start('script-embedded'); ?>
<?= $this->Html->script('summernote.min.js');?>
<?= $this->Html->script('lang/summernote-ja-JP.js');?>
<script>
	//$('input[name="data[Task][kind]"]:radio').val(['text']);
	var _editor;
	var URL_UPLOAD	= '<?= Router::url(['controller' => 'tasks', 'action' => 'upload', 'admin' => false])?>/file';
	var URL_NOTE	= '<?= Router::url(['controller' => 'notes', 'action' => 'page', 'admin' => false])?>/';
	var UPLOAD_LABEL = '<?= __('アップロード')?>';
	
	$(document).ready(function()
	{
		$url = $('.form-control-upload');

		$url.after('<button id="btnUpload">' + UPLOAD_LABEL + '</button>');

		$("#btnUpload").click(function(){
			//window.open(URL_UPLOAD, '_upload', 'width=650,height=500,resizable=no');
			//ファイルアップロードダイアログの iframe にURLを設定
			$("#uploadFrame").attr("src", URL_UPLOAD);
			//ファイルアップロードダイアログを表示
			$('#uploadDialog').modal('show');
			return false;
		});

		// カードが存在しない場合、ページIDを削除
		$("form").submit(function(){
			var cnt = document.getElementById('fraIrohaNote').contentWindow.getLeafCount();
			
			if(cnt==0)
				$('.row-page-id').val('');
		});
		
		// ページIDの設定
		setPageID();
	});

	function setURL(url, file_name)
	{
		$('.form-control-upload').val(url);
		
		if(file_name)
			$('.form-control-filename').val(file_name);

		$('#uploadDialog').modal('hide');
	}
	
	function closeDialog(url, file_name)
	{
		$('#uploadDialog').modal('hide');
	}
	
	function setPageID()
	{
		// ページ番号が設定されていない場合、ページ番号を生成
		var page_id = $('.row-page-id').val();
		
		if(!page_id)
		{
			page_id = Math.round(Math.random() * 1000000);
			$('.row-page-id').val(page_id);
		}
		
		document.getElementById("fraIrohaNote").src = URL_NOTE + page_id + '/edit';
	}
</script>
<?php $this->end(); ?>
<div class="tasks-edit">
	<?php
		$controller = ($is_user) ? 'users_themes' : 'themes';
		
		$this->Html->addCrumb(__('学習テーマ一覧'), ['controller' => $controller, 'action' => 'index']);
		$this->Html->addCrumb(h($theme['Theme']['title']), ['action' => 'index', $theme['Theme']['id']]);
		echo $this->Html->getCrumbs(' / ');
	?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?= ($this->isEditPage()) ? __('編集') :  __('新規課題'); ?>
		</div>
		<div class="panel-body">
			<?php
				echo $this->Form->create('Task', Configure::read('form_defaults'));
				echo $this->Form->input('id');
				echo $this->Form->input('title',			['label' => __('課題タイトル')]);
				echo $this->Form->inputRadio('priority',	['label' => __('優先度'), 'options' => Configure::read('task_priority'), 'default' => '2']);
				echo $this->Form->inputRadio('status',		['label' => __('ステータス'), 'options' => Configure::read('task_status'), 'default' => '1']);
				echo $this->Form->input('body',				['label' => __('課題の内容')]);
				echo $this->Form->block('', '※ <a href="https://ja.wikipedia.org/wiki/Markdown" target="_blank">Markdown 形式</a> で記述可能です。');
				
				echo $this->Form->input('deadline', [
					'type' => 'date',
					'dateFormat' => 'YMD',
					'monthNames' => false,
					'minYear' => date('Y') - 4,
					'maxYear' => date('Y') + 4,
					'separator' => ' / ',
					'label'=> __('期日'),
					'class'=>'form-control date',
					'style' => 'display: inline;',
					'value' => $deadline,
				]);
				
				if(Configure::read('use_irohanote_task'))
				{
					echo $this->Form->block(__('アイデアマップ'), '<iframe id="fraIrohaNote" width="100%" height="400"></iframe>'. false, 'row-irohanote');
					echo $this->Form->hidden('page_id', ['class' => 'form-group row-page-id']);
				}
				
				echo $this->Form->input('file',		['label' => __('添付ファイル'), 'class' => 'form-control form-control-upload']);
				echo $this->Form->input('rate',		['label' => __('進捗率'), 'options' => Configure::read('rate_list')]);
				
				echo $this->Form->hidden('file_name', ['class' => 'form-control-filename']);
				echo '<input name="study_sec" type="hidden" value="0">';
				
				echo Configure::read('form_submit_before')
					.$this->Form->submit(__('保存'), Configure::read('form_submit_defaults'))
					.Configure::read('form_submit_after');
				echo $this->Form->end();
			?>
		</div>
	</div>
</div>

<!--ファイルアップロードダイアログ-->
<div class="modal fade" id="uploadDialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-id='1'>
	<div class="modal-dialog">
		<div class="modal-content" style="width:660px;">
			<div class="modal-body" id='modal-body_1'>
				<iframe id="uploadFrame" width="100%" style="height: 440px;" scrolling="no" frameborder="no"></iframe>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
