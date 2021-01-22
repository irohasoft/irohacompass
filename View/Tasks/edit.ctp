<?php if(!$is_user) echo $this->element('admin_menu');?>
<?php $this->start('css-embedded'); ?>
<?php echo $this->Html->css('summernote.css');?>
<?php $this->end(); ?>
<?php $this->start('script-embedded'); ?>
<?php echo $this->Html->script('summernote.min.js');?>
<?php echo $this->Html->script('lang/summernote-ja-JP.js');?>
<script>
	//$('input[name="data[Task][kind]"]:radio').val(['text']);
	var _editor;
	var URL_UPLOAD	= '<?php echo Router::url(['controller' => 'tasks', 'action' => 'upload', 'admin' => false])?>/file';
	var URL_NOTE	= '<?php echo Router::url(['controller' => 'notes', 'action' => 'page', 'admin' => false])?>/';
	
	$(document).ready(function()
	{
		$url = $('.form-control-upload');

		$url.after('<button id="btnUpload"><span data-localize="upload">Upload</span></button>');

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
<?php
$rate_list = [
	'0'  => '0%',
	'10' => '10%',
	'20' => '20%',
	'30' => '30%',
	'40' => '40%',
	'50' => '50%',
	'60' => '60%',
	'70' => '70%',
	'80' => '80%',
	'90' => '90%',
	'100' => '100%',
];
?>
<div class="tasks-edit">
	<?php
		$controller = ($is_user) ? 'users_themes' : 'themes';
		
		$this->Html->addCrumb('学習テーマ一覧', ['controller' => $controller, 'action' => 'index']);
		$this->Html->addCrumb(h($theme['Theme']['title']), ['action' => 'index',$this->params['pass'][0]]);
		echo $this->Html->getCrumbs(' / ');
	?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?php echo (($this->action == 'admin_edit')||($this->action == 'edit')) ? '<span data-localize="edit">編集</span>' :  '<span data-localize="add">新規課題</span>'; ?>
		</div>
		<div class="panel-body">
			<?php echo $this->Form->create('Task', Configure::read('form_defaults')); ?>
			<?php
				echo $this->Form->input('id');
				echo $this->Form->input('title',	['label' => '<span data-localize="title">課題タイトル</span>']);
				
				echo $this->Form->input('priority',	[
					'type' => 'radio',
					'before' => '<label class="col col-sm-3 control-label"><span data-localize="priority">優先度</span></label>',
					'separator'=>"　", 
					'disabled'=>false, 
					'legend' => false,
					'class' => false,
					'value' => $priority,
					'options' => Configure::read('task_priority')
					]
				);
				
				echo $this->Form->input('status',	[
					'type' => 'radio',
					'before' => '<label class="col col-sm-3 control-label"><span data-localize="status">ステータス</span></label>',
					'separator'=>"　", 
					'disabled'=>false, 
					'legend' => false,
					'class' => false,
					'value' => $status,
					'options' => Configure::read('task_status')
					]
				);
				
				echo $this->Form->input('body',		['label' => '<span data-localize="content">課題の内容</span>']);
				Utils::writeFormGroup('', '※ <a href="https://ja.wikipedia.org/wiki/Markdown" target="_blank">Markdown 形式</a> で記述可能です。');
				
				echo $this->Form->input('deadline', [
					'type' => 'date',
					'dateFormat' => 'YMD',
					'monthNames' => false,
					'minYear' => date('Y') - 4,
					'maxYear' => date('Y') + 4,
					'separator' => ' / ',
					'label'=> '<span data-localize="deadline">期日</span>',
					'class'=>'form-control date',
					'style' => 'display: inline;',
					'value' => $deadline,
				]);
				
				if(Configure::read('use_irohanote'))
				{
					Utils::writeFormGroup('<span data-localize="ideamap">アイデアマップ</span>', 
						'<iframe id="fraIrohaNote" width="100%" height="400"></iframe>'.
						false, 'row-irohanote');
				}
				
				echo $this->Form->hidden('page_id', ['class' => 'form-group row-page-id']);
				
				echo $this->Form->input('file',		['label' => '<span data-localize="attachment">添付ファイル</span>', 'class' => 'form-control form-control-upload']);
				echo $this->Form->input('rate',		[
					'label' => '<span data-localize="progress_rate">進捗率</span>', 
					'options'=>$rate_list, 
					'class' => 'form-control',
				]);
				echo $this->Form->hidden('file_name', ['class' => 'form-control-filename']);
			?>
			<div class="form-group">
				<div class="col col-sm-9 col-sm-offset-3">
					<?php echo $this->Form->submit('保存', Configure::read('form_submit_defaults')); ?>
				</div>
			</div>
			<input name="study_sec" type="hidden" value="0">
			<?php echo $this->Form->end(); ?>
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
