<?= $this->element('admin_menu');?>
<?= $this->Html->css( 'select2.min.css');?>
<?= $this->Html->script( 'select2.min.js');?>
<?php $this->Html->scriptStart(['inline' => false]); ?>
	$(function (e) {
		$('#GroupGroup').select2({placeholder:   "所属するグループを選択して下さい。(複数選択可)", closeOnSelect: <?= (Configure::read('close_on_select') ? 'true' : 'false'); ?>,});
		$('#ThemeTheme').select2({placeholder: "利用する学習テーマを選択して下さい。(複数選択可)", closeOnSelect: <?= (Configure::read('close_on_select') ? 'true' : 'false'); ?>,});
		// パスワードの自動復元を防止
		setTimeout('$("#UserNewPassword").val("");', 500);
	});
<?php $this->Html->scriptEnd(); ?>
<div class="admin-users-edit">
<?= $this->Html->link(__('<< 戻る'), ['action' => 'index'])?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?= ($this->request->data) ? __('編集') :  __('新規ユーザ'); ?>
		</div>
		<div class="panel-body">
			<?= $this->Form->create('User', Configure::read('form_defaults')); ?>
			<?php
				$password_label = ($this->request->data) ? __('新しいパスワード') : __('パスワード');
				
				echo $this->Form->input('id');
				echo $this->Form->input('username',				['label' => 'ログインID']);
				echo $this->Form->input('User.new_password',	['label' => $password_label, 'type' => 'password', 'autocomplete' => 'new-password']);
				echo $this->Form->input('name',					['label' => '氏名']);
				
				// root アカウント、もしくは admin 権限以外の場合、権限変更を許可しない
				$disabled = (($username == 'root')||($loginedUser['role']!='admin'));
				
				echo $this->Form->input('role',	[
					'type' => 'radio',
					'before' => '<label class="col col-sm-3 control-label">権限</label>',
					'separator'=>"　", 
					'disabled'=>$disabled, 
					'legend' => false,
					'class' => false,
					'options' => Configure::read('user_role')
					]
				);
				
				echo $this->Form->input('lang',	[
					'type' => 'radio',
					'before' => '<label class="col col-sm-3 control-label">言語</label>',
					'separator'=>"　", 
					'legend' => false,
					'class' => false,
					'options' => Configure::read('lang')
					]
				);
				
				echo $this->Form->input('email',				['label' => 'メールアドレス']);
				echo $this->Form->input('comment',				['label' => '備考']);
				echo $this->Form->input('Group',				['label' => '所属グループ',	'size' => 20]);
				echo $this->Form->input('Theme',				['label' => '学習テーマ',		'size' => 20]);
			?>
			<div class="form-group">
				<div class="col col-sm-9 col-sm-offset-3">
					<?= $this->Form->submit('保存', Configure::read('form_submit_defaults')); ?>
				</div>
			</div>
			<?= $this->Form->end(); ?>
		</div>
	</div>
</div>
