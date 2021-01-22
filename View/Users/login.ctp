<div class="users-login">
	<div class="panel panel-info form-signin">
		<div class="panel-heading">
			学習者ログイン
		</div>
		<div class="panel-body">
			<div class="text-right"><a href="../admin/users/login">管理者ログインへ</a></div>
			<?= $this->Flash->render('auth'); ?>
			<?= $this->Form->create('User'); ?>
			
			<div class="form-group">
				<?= $this->Form->input('username', ['label' => __('ログインID / Login ID'), 'class'=>'form-control', 'value' => $username]); ?>
			</div>
			<div class="form-group">
				<?= $this->Form->input('password', ['label' => __('パスワード / Password'), 'class'=>'form-control', 'value' => $password]);?>
				<input type="checkbox" name="data[User][remember_me]" checked="checked" value="1" id="remember_me"><?= __('ログイン状態を保持 / Remeber me')?>
				<?= $this->Form->unlockField('remember_me'); ?>
			</div>
			<?= $this->Form->end(['label' => 'ログイン / Login', 'class'=>'btn btn-lg btn-primary btn-block']); ?>
		</div>
	</div>
</div>
