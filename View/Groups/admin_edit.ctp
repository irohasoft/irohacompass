<?php echo $this->element('admin_menu');?>
<?php echo $this->Html->css( 'select2.min.css');?>
<?php echo $this->Html->script( 'select2.min.js');?>
<?php $this->Html->scriptStart(['inline' => false]); ?>
	$(function (e) {
		$('#UserUser').select2({placeholder:   "所属させるユーザを選択して下さい。(複数選択可)", closeOnSelect: <?php echo (Configure::read('close_on_select') ? 'true' : 'false'); ?>,});
		$('#ThemeTheme').select2({placeholder: "利用する学習テーマを選択して下さい。(複数選択可)", closeOnSelect: <?php echo (Configure::read('close_on_select') ? 'true' : 'false'); ?>,});
	});
<?php $this->Html->scriptEnd(); ?>
<div class="admin-groups-edit">
<?php echo $this->Html->link(__('<< 戻る'), ['action' => 'index'])?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?php echo ($this->action == 'admin_edit') ? __('編集') :  __('新規グループ'); ?>
		</div>
		<div class="panel-body">
			<?php echo $this->Form->create('Group', Configure::read('form_defaults')); ?>
			<?php
				echo $this->Form->input('id');
				echo $this->Form->input('title',	['label' => __('グループ名')]);
				echo $this->Form->input('User',		['label' => __('所属ユーザ'),		'size' => 20]);
				echo $this->Form->input('Theme',	['label' => __('学習テーマ'),		'size' => 20]);
				echo $this->Form->input('comment',	['label' => __('備考')]);
			?>
			<div class="form-group">
				<div class="col col-sm-9 col-sm-offset-3">
					<?php echo $this->Form->submit('保存', Configure::read('form_submit_defaults')); ?>
				</div>
			</div>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>