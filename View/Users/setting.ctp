<div class="users-setting">
	<div class="breadcrumb">
<?php
	$this->Html->addCrumb('HOME', [
		'controller' => 'users_themes',
		'action' => 'index'
	]);
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
				echo $this->Form->input('User.new_password', [
					'label' => '<span data-localize="new_password">新しいパスワード</span>',
					'type' => 'password',
					'autocomplete' => 'new-password'
				]);
				
				echo $this->Form->input('User.new_password2', [
					'label' => '<span data-localize="new_password">新しいパスワード (確認用)</span>',
					'type' => 'password',
					'autocomplete' => 'new-password'
				]);
				echo Configure::read('form_submit_before')
					.$this->Form->submit(__('保存'), Configure::read('form_submit_defaults'))
					.Configure::read('form_submit_after');
				echo $this->Form->end();
			?>
		</div>
	</div>
</div>
