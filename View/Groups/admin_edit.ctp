<?= $this->element('admin_menu');?>
<?= $this->Html->css( 'select2.min.css');?>
<?= $this->Html->script( 'select2.min.js');?>
<?php $this->Html->scriptStart(['inline' => false]); ?>
	$(function (e) {
		$('#UserUser').select2({placeholder:   "所属させるユーザを選択して下さい。(複数選択可)", closeOnSelect: <?= (Configure::read('close_on_select') ? 'true' : 'false'); ?>,});
		$('#ThemeTheme').select2({placeholder: "利用する学習テーマを選択して下さい。(複数選択可)", closeOnSelect: <?= (Configure::read('close_on_select') ? 'true' : 'false'); ?>,});
	});
<?php $this->Html->scriptEnd(); ?>
<div class="admin-groups-edit">
<?= $this->Html->link(__('<< 戻る'), ['action' => 'index'])?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?= $this->isEditPage() ? __('編集') :  __('新規グループ'); ?>
		</div>
		<div class="panel-body">
			<?php
				echo $this->Form->create('Group', Configure::read('form_defaults'));
				echo $this->Form->input('id');
				echo $this->Form->input('title',	['label' => __('グループ名')]);
				echo $this->Form->input('User',		['label' => __('所属ユーザ'),		'size' => 20]);
				echo $this->Form->input('Theme',	['label' => __('学習テーマ'),		'size' => 20]);
				echo $this->Form->input('comment',	['label' => __('備考')]);
				echo Configure::read('form_submit_before')
					.$this->Form->submit(__('保存'), Configure::read('form_submit_defaults'))
					.Configure::read('form_submit_after');
				echo $this->Form->end();
			?>
		</div>
	</div>
</div>