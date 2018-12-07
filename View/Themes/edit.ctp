<?php if(!$is_user) echo $this->element('admin_menu');?>
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