<?php if(!$is_user) echo $this->element('admin_menu');?>
<?php $this->start('css-embedded'); ?>
<style type='text/css'>

</style>
<?php $this->end(); ?>
<?php $this->start('script-embedded'); ?>
<script>
	$(document).ready(function()
	{
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
<div class="groups form">
<?php
	$controller = ($is_user) ? 'users_themes' : 'themes';
?>
	<?php echo $this->Html->link(__('<< 戻る'), array('controller' => $controller, 'action' => 'index', @$this->params['pass'][0]))?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?php echo (($this->action == 'admin_edit')||($this->action == 'edit')) ? __('編集') :  __('新規学習テーマ'); ?>
		</div>
		<div class="panel-body">
			<?php echo $this->Form->create('Theme', Configure::read('form_defaults')); ?>
			<?php
				echo $this->Form->input('id');
				echo $this->Form->input('title',	array('label' => __('学習テーマ名')));
				echo $this->Form->input('introduction',		array(
					'label' => __('学習目標'),
					'div' => 'form-group row-body',
					)
				);
				Utils::writeFormGroup('', '※ <a href="https://ja.wikipedia.org/wiki/Markdown" target="_blank">Markdown 形式</a> で記述可能です。', false, 'row-markdown');
				
				if(Configure::read('use_irohanote'))
				{
					Utils::writeFormGroup('マップ', 
						'<iframe id="fraIrohaNote" width="100%" height="400"></iframe>',
						false, 'row-irohanote'
					);
				}
				
				echo $this->Form->hidden('page_id', array('class' => 'form-group row-page-id'));
				
				//echo $this->Form->input('comment',			array('label' => __('備考')));
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