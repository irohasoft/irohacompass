<?php if(!$is_user) echo $this->element('admin_menu');?>
<?php $this->start('css-embedded'); ?>
<?php echo $this->Html->css('summernote.css');?>
<style type='text/css'>
	input[name="data[Task][url]"]
	{
		display:inline-block;
		margin-right:10px;
	}
	label span
	{
		font-weight: normal;
	}
	
	.date
	{
		width		: 100px;
	}
</style>
<?php $this->end(); ?>
<?php $this->start('script-embedded'); ?>
<?php echo $this->Html->script('summernote.min.js');?>
<?php echo $this->Html->script('lang/summernote-ja-JP.js');?>
<script>
	//$('input[name="data[Task][kind]"]:radio').val(['text']);
	var _editor;
	
	$(document).ready(function()
	{
		$url = $('.form-control-upload');

		$url.after('<input id="btnUpload" type="button" value="アップロード">');

		$("#btnUpload").click(function(){
			window.open('<?php echo Router::url(array('controller' => 'tasks', 'action' => 'upload'))?>/file', '_upload', 'width=650,height=500,resizable=no');
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
		
		document.getElementById("fraIrohaNote").src = '<?php echo Router::url(array('controller' => 'notes', 'action' => 'page'))?>/' + page_id + '/edit';
	}
</script>
<?php $this->end(); ?>
<?php
$rate_list = array(
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
);
?>
<div class="tasks form">
	<?php
		$controller = ($is_user) ? 'users_themes' : 'themes';
		
		$this->Html->addCrumb('学習テーマ一覧', array('controller' => $controller, 'action' => 'index'));
		$this->Html->addCrumb(h($theme['Theme']['title']), array('action' => 'index',$this->params['pass'][0]));
		echo $this->Html->getCrumbs(' / ');
	?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?php echo (($this->action == 'admin_edit')||($this->action == 'edit')) ? __('編集') :  __('新規課題'); ?>
		</div>
		<div class="panel-body">
			<?php echo $this->Form->create('Task', Configure::read('form_defaults')); ?>
			<?php
				echo $this->Form->input('id');
				echo $this->Form->input('title',	array('label' => '課題タイトル'));
				
				echo $this->Form->input('priority',	array(
					'type' => 'radio',
					'before' => '<label class="col col-md-3 control-label">優先度</label>',
					'separator'=>"　", 
					'disabled'=>false, 
					'legend' => false,
					'class' => false,
					'value' => $priority,
					'options' => Configure::read('task_priority')
					)
				);
				
				echo $this->Form->input('status',	array(
					'type' => 'radio',
					'before' => '<label class="col col-md-3 control-label">状態</label>',
					'separator'=>"　", 
					'disabled'=>false, 
					'legend' => false,
					'class' => false,
					'value' => $status,
					'options' => Configure::read('task_status')
					)
				);
				
				echo $this->Form->input('body',		array('label' => '課題の内容'));
				Utils::writeFormGroup('', '※ <a href="https://ja.wikipedia.org/wiki/Markdown" target="_blank">Markdown 形式</a> で記述可能です。');
				
				echo $this->Form->input('deadline', array(
					'type' => 'date',
					'dateFormat' => 'YMD',
					'monthNames' => false,
					'minYear' => date('Y') - 4,
					'maxYear' => date('Y') + 4,
					'separator' => ' / ',
					'label'=> '期日',
					'class'=>'form-control date',
					'style' => 'display: inline;',
					'value' => $deadline,
				));
				
				if(Configure::read('use_irohanote'))
				{
					Utils::writeFormGroup('マップ', 
						'<iframe id="fraIrohaNote" width="100%" height="400"></iframe>'.
						false, 'row-irohanote');
				}
				
				echo $this->Form->hidden('page_id', array('class' => 'form-group row-page-id'));
				
				echo $this->Form->input('file',		array('label' => __('添付ファイル')));
				echo $this->Form->input('rate',		array(
					'label' => '進捗率', 
					'options'=>$rate_list, 
					'class' => 'form-control',
				));
				echo $this->Form->hidden('file_name', array('class' => 'form-control-filename'));
			?>
			<div class="form-group">
				<div class="col col-md-9 col-md-offset-3">
					<?php echo $this->Form->submit('保存', Configure::read('form_submit_defaults')); ?>
				</div>
			</div>
			<input name="study_sec" type="hidden" value="0">
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>
