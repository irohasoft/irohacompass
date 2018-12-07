<?php if(!$is_user) echo $this->element('admin_menu');?>
<div class="progresses form">
<?php echo $this->Html->css('summernote.css');?>
<?php $this->start('css-embedded'); ?>
<style type='text/css'>
	#ProgressOptionList
	{
	    width: 200px;
	}
	
	#ProgressOptionList option
	{
		border-top:    2px double #ccc;
		border-right:  2px double #aaa;
		border-bottom: 2px double #aaa;
		border-left:   2px double #ccc;
		/*
		background-color: #fff;
		font-family: Verdana, Geneva, sans-serif;
		*/
		color: #444455;
		width: 160px;
		margin:6px;
		padding: 5px;
	}
	
	input[name="data[Progress][image]"]
	{
		display:inline-block;
		width:85%;
		margin-right:10px;
	}
</style>
<?php $this->end(); ?>
<?php $this->start('script-embedded'); ?>
<?php echo $this->Html->script('summernote.min.js');?>
<?php echo $this->Html->script('lang/summernote-ja-JP.js');?>
<script>
	$(document).ready(function()
	{
		$url = $('.form-control-upload');

		$url.after('<input id="btnUpload" type="button" value="アップロード">');

		$("#btnUpload").click(function(){
			window.open('<?php echo Router::url(array('controller' => 'tasks', 'action' => 'upload'))?>/file', '_upload', 'width=650,height=500,resizable=no');
			return false;
		});
		
		// ノート作成ツールを開く
		$(".btn-note").click(function(){
			var page_id = $('#ProgressPageId').val();
			
			if(!page_id)
			{
				page_id = Math.round(Math.random() * 1000000);
				$('#ProgressPageId').val(page_id);
			}
			
			window.open('<?php echo Router::url(array('controller' => 'notes', 'action' => 'page'))?>/'+page_id, '_note', 'width=1000,height=700,resizable=yes');
			return false;
		});
		
		
		render();
	});
	
	function setURL(url, file_name)
	{
		$('.form-control-upload').val(url);
		
		if(file_name)
			$('.form-control-filename').val(file_name);
	}

	function render()
	{
		if($('#ProgressProgressTypeProgress:checked').val())
		{
			//$('label[for="ProgressBody"]').text('進捗内容');
			$('.row-progress').show();
		}
		else
		{
			//$('label[for="ProgressBody"]').text('コメント内容');
			$('.row-progress').hide();
		}
	}
</script>
<?php $this->end(); ?>
<?php //debug($this->request->data);?>
	<div class="ib-breadcrumb">
	<?php 
		$controller = ($is_user) ? 'users_themes' : 'themes';
		
		$this->Html->addCrumb('学習テーマ一覧', array('controller' => $controller, 'action' => 'index'));
		$this->Html->addCrumb($content['Theme']['title'],  array('controller' => 'tasks', 'action' => 'index', $content['Theme']['id']));
		$this->Html->addCrumb($content['Task']['title'], array('controller' => 'progresses', 'action' => 'index', $content['Task']['id']));
		
		echo $this->Html->getCrumbs(' / ');
		
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
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?php echo ($this->action == 'admin_edit') ? __('編集') :  __('新規'); ?>
		</div>
		<div class="panel-body">
			<?php echo $this->Form->create('Progress', Configure::read('form_defaults')); ?>
			<?php
				echo $this->Form->input('id');
				echo $this->Form->input('title',	array('label' => __('タイトル')));
				echo $this->Form->input('progress_type',	array(
					'type' => 'radio',
					'before' => '<label class="col col-md-3 col-sm-4 control-label">種別</label>',
					'separator'=>"　", 
					'legend' => false,
					'class' => false,
					'options' => Configure::read('progress_type'),
					'default' => 'progress',
					'onchange' => 'render()'
					)
				);
				echo $this->Form->input('body',		array('label' => __('内容')));
				
				Utils::writeFormGroup('', '※ <a href="https://ja.wikipedia.org/wiki/Markdown" target="_blank">Markdown 形式</a> で記述可能です。');
				
				if(Configure::read('use_irohanote'))
					Utils::writeFormGroup('ノート', '<button class="btn btn-info btn-note">iroha Note</button> ※ 創造技法を用いて獲得した知識、アイデアをまとめます');
				
				echo $this->Form->hidden('page_id');
				
				echo $this->Form->input('file',		array('label' => __('添付ファイル'), 'class' => 'form-control form-control-upload'));
				echo $this->Form->input('rate',		array(
					'label' => '進捗率', 
					'options'=>$rate_list, 
					'class' => 'form-control',
					'value' => $content['Task']['rate'],
					'div' => 'form-group row-progress',
				));
				echo $this->Form->hidden('file_name', array('class' => 'form-control-filename'));
			?>
			<div class="form-group">
				<div class="col col-md-9 col-md-offset-3">
					<p><input name="is_mail" type="checkbox" style="margin-right: 5px;"> メール通知 ※学習テーマの関係者に更新が発生した旨をメールで通知します</p>
				</div>
				<div class="col col-md-9 col-md-offset-3">
					<?php echo $this->Form->submit('保存', Configure::read('form_submit_defaults')); ?>
				</div>
			</div>
			<input name="study_sec" type="hidden" value="0">
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>