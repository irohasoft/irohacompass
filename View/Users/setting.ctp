<div class="users-setting">
	<div class="breadcrumb">
<?php
	$this->Html->addCrumb('HOME', array(
		'controller' => 'users_themes',
		'action' => 'index'
	));
	echo $this->Html->getCrumbs(' / ');
?>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<span data-localize="setting">設定</span>
		</div>
		<div class="panel-body">
			<?php
				echo $this->Form->create('User', Configure::read('form_defaults'));
				echo $this->Form->input('User.new_password', array(
					'label' => '<span data-localize="new_password">新しいパスワード</span>',
					'type' => 'password',
					'autocomplete' => 'new-password'
				));
				
				echo $this->Form->input('User.new_password2', array(
					'label' => '<span data-localize="new_password">新しいパスワード (確認用)</span>',
					'type' => 'password',
					'autocomplete' => 'new-password'
				));
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
