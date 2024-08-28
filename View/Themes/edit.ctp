<?php if(!$is_user) echo $this->element('admin_menu');?>
<?php $this->start('script-embedded'); ?>
<script>
	var URL_NOTE	= '<?= Router::url(['controller' => 'notes', 'action' => 'page', 'admin' => false])?>/';
	
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
<div class="groups form">
<?php
	$controller = ($is_user) ? 'users_themes' : 'themes';
?>
	<?= $this->Html->link(__('<< 戻る'), ['controller' => $controller, 'action' => 'index', @$this->params['pass'][0]])?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?= ($this->isEditPage()) ? __('編集') :  __('新規作成'); ?>
		</div>
		<div class="panel-body">
		<?php
			echo $this->Form->create('Theme', Configure::read('form_defaults'));
			echo $this->Form->input('id');
			echo $this->Form->input('title', ['label' => __('学習テーマ名')]);
			echo $this->Form->input('learning_target',	[
				'label' => __('学習目標'),
				'div' => 'form-group row-body',
				]
			);
			
			if(!$is_user)
				echo $this->Form->input('user_id', ['label' => '所有者',]);
			
			echo $this->Form->block('', '※ <a href="https://ja.wikipedia.org/wiki/Markdown" target="_blank">Markdown 形式</a> で記述可能です。', false, 'row-markdown');
			
			if(Configure::read('use_irohanote_theme'))
			{
				echo $this->Form->block(__('アイデアマップ'), '<iframe id="fraIrohaNote" width="100%" height="400"></iframe>', false, 'row-irohanote');
				echo $this->Form->hidden('page_id', ['class' => 'form-group row-page-id']);
			}
			
			//echo $this->Form->input('comment',			array('label' => __('備考')));
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
