<?php echo $this->element('admin_menu');?>
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
</style>
<?php $this->end(); ?>
<?php $this->start('script-embedded'); ?>
<script>
</script>
<?php $this->end(); ?>

<div class="tasks form">
	<?php
		$this->Html->addCrumb('アンケート一覧', array('action' => 'index_enq'));
		echo $this->Html->getCrumbs(' / ');
	?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?php echo ($this->action == 'admin_edit_enq') ? __('編集') :  __('新規アンケート'); ?>
		</div>
		<div class="panel-body">
			<?php echo $this->Form->create('Task', Configure::read('form_defaults')); ?>
			<?php
				echo $this->Form->input('id');
				echo $this->Form->input('title',	array('label' => 'アンケートタイトル'));
				echo $this->Form->input('comment', array('label' => '備考'));
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
