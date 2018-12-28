<?php if(!$is_user) echo $this->element('admin_menu');?>
<?php $this->start('script-embedded'); ?>
<script>
	$(document).ready(function()
	{
		// ノート作成ツールを開く
		$(".btn-note").click(function(){
			var page_id = $('#ThemePageId').val();
			
			if(!page_id)
			{
				page_id = Math.round(Math.random() * 1000000);
				$('#ThemePageId').val(page_id);
			}
			
			window.open('<?php echo Router::url(array('controller' => 'notes', 'action' => 'page'))?>/'+page_id, '_note', 'width=1000,height=700,resizable=yes');
			return false;
		});
	});
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
				/*
				echo $this->Form->input('opened', array(
					'type' => 'datetime',
					'dateFormat' => 'YMD',
					'monthNames' => false,
					'timeFormat' => '24',
					'separator' => ' - ',
					'label'=> '公開日時',
					'style' => 'width:initial; display: inline;'
				));
				*/
				echo $this->Form->input('introduction',	array('label' => __('学習目標')));
				
				if(Configure::read('use_irohanote'))
					Utils::writeFormGroup('ノート', '<button class="btn btn-info btn-note">iroha Note</button> ※ 創造技法を用いて獲得した知識、アイデアをまとめます');
				
				echo $this->Form->hidden('page_id');
				
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