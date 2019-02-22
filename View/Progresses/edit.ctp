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

	.row-body,
	.row-irohanote,
	.row-markdown,
	.row-progress
	{
		display: none;
	}

	.content-type-text .row-body,
	.content-type-markdown .row-markdown,
	.content-type-markdown .row-body,
	.content-type-irohanote .row-irohanote,
	.progress-type-progress .row-progress
	{
		display: block;
	}
</style>
<?php $this->end(); ?>
<?php $this->start('script-embedded'); ?>
<?php echo $this->Html->script('summernote.min.js');?>
<?php echo $this->Html->script('lang/summernote-ja-JP.js');?>
<script>
	var URL_UPLOAD	= '<?php echo Router::url(array('controller' => 'tasks', 'action' => 'upload', 'admin' => false))?>/file';
	var URL_NOTE	= '<?php echo Router::url(array('controller' => 'notes', 'action' => 'page', 'admin' => false))?>/';
	
	$(document).ready(function()
	{
		$url = $('.form-control-upload');
		
		$url.after('<input id="btnUpload" type="button" value="アップロード">');
		
		// 添付ファイルアップロードボタン
		$("#btnUpload").click(function(){
			window.open(URL_UPLOAD, '_upload', 'width=650,height=500,resizable=no');
			return false;
		});
		
		// 動的な行の表示
		render();
	});

	function setURL(url, file_name)
	{
		$('.form-control-upload').val(url);
		
		if(file_name)
			$('.form-control-filename').val(file_name);
	}

	function setProgressType(element)
	{
		$('.row-progress-type input[type="radio"]').each(function(){
			$('.panel-body').removeClass('progress-type-' + $(this).val());
		});
		
		$('.panel-body').addClass('progress-type-' + $(element).val());
	}

	function setContentType(element)
	{
		$('.row-content-type input[type="radio"]').each(function(){
			$('.panel-body').removeClass('content-type-' + $(this).val());
		});
		
		$('.panel-body').addClass('content-type-' + $(element).val());
		
		if($(element).val()=='irohanote')
		{
			// ページ番号が設定されていない場合、ページ番号を生成
			var page_id = $('.row-page-id').val();
			
			if(!page_id)
			{
				page_id = Math.round(Math.random() * 1000000);
				$('.row-page-id').val(page_id);
			}
			
			document.getElementById("fraIrohaNote").src = URL_NOTE + page_id + '/edit';
			
			
			// 内容にダミーの文字列を設定
			if(!$('.row-body textarea').val())
				$('.row-body textarea').val('dummy');
		}
		else
		{
			// ダミーの文字列をクリア
			if($('.row-body textarea').val()=='dummy')
				$('.row-body textarea').val('');
		}
	}

	function render()
	{
		var element = $($('.row-progress-type input[type="radio"]:checked'));
		setProgressType(element);
		
		element = $($('.row-content-type input[type="radio"]:checked'));
		setContentType(element);
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
					'before' => '<label class="col col-sm-3 control-label">種別</label>',
					'separator'=>"　", 
					'legend' => false,
					'class' => false,
					'options' => Configure::read('progress_type'),
					'default' => 'progress',
					'div' => 'form-group row-progress-type',
					'onchange' => 'setProgressType(this)',
					)
				);
				echo $this->Form->input('content_type',	array(
					'type' => 'radio',
					'before' => '<label class="col col-sm-3 control-label">進捗の入力形式</label>',
					'separator'=>"　", 
					'legend' => false,
					'class' => false,
					'options' => Configure::read('content_type'),
					'default' => 'markdown',
					'div' => 'form-group row-content-type',
					'onchange' => 'setContentType(this)',
					)
				);
				echo $this->Form->input('body',		array(
					'label' => __('内容'),
					'div' => 'form-group row-body',
					)
				);
				
				
				if(Configure::read('use_irohanote'))
				{
					Utils::writeFormGroup('内容', 
						'<iframe id="fraIrohaNote" width="100%" height="400"></iframe>',
						false, 'row-irohanote'
					);
				}
				
				Utils::writeFormGroup('', '※ <a href="https://ja.wikipedia.org/wiki/Markdown" target="_blank">Markdown 形式</a> で記述可能です。', false, 'row-markdown');
				
				echo $this->Form->hidden('page_id', array('class' => 'form-group row-page-id'));
				
				echo $this->Form->input('file',		array('label' => __('添付ファイル'), 'class' => 'form-control form-control-upload'));

				echo $this->Form->input('status',	array(
					'type' => 'radio',
					'before' => '<label class="col col-sm-3 control-label">課題のステータス</label>',
					'separator'=>"　", 
					'disabled'=>false, 
					'legend' => false,
					'class' => false,
					'value' => $content['Task']['status'],
					'options' => Configure::read('task_status')
					)
				);
				
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
				<div class="col col-sm-9 col-sm-offset-3">
					<p><input name="is_mail" type="checkbox" style="margin-right: 5px;"> メール通知 ※学習テーマの関係者に更新が発生した旨をメールで通知します</p>
				</div>
				<div class="col col-sm-9 col-sm-offset-3">
					<?php echo $this->Form->submit('保存', Configure::read('form_submit_defaults')); ?>
				</div>
			</div>
			<input name="study_sec" type="hidden" value="0">
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>