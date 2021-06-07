<div class="progresss-move">
<?= $this->Html->link(__('<< 戻る'), ['action' => 'index', $task_id])?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?= __('移動'); ?>
		</div>
		<div class="panel-body">
			<?php
				echo $this->Form->create('Progress', Configure::read('form_defaults'));
				echo $this->Form->input('id');
				echo $this->Form->input('task_id',		['label' => __('移動先の課題')]);
				echo Configure::read('form_submit_before')
					.$this->Form->submit(__('保存'), Configure::read('form_submit_defaults'))
					.Configure::read('form_submit_after');
				echo $this->Form->end();
			?>
		</div>
	</div>
</div>