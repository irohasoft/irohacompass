<?= $this->element('admin_menu');?>
<div class="admin-settings-index">
	<div class="panel panel-default">
		<div class="panel-heading">
			<?= __('システム設定'); ?>
		</div>
		<div class="panel-body">
			<?php
				echo $this->Form->create('Setting', Configure::read('form_defaults'));
				echo $this->Form->input('title',		['label' => 'システム名',					'value'=>$settings['title']]);
				echo $this->Form->input('copyright',	['label' => 'コピーライト',				'value'=>$settings['copyright']]);
				echo $this->Form->input('color',		['label' => 'テーマカラー',				'options'=>$colors, 'selected'=>$settings['color']]);
				echo $this->Form->input('information',	['label' => '全体のお知らせ',				'value'=>$settings['information'], 'type' => 'textarea']);
				/*
				echo $this->Form->input('mail_title',	array('label' => '進捗の更新メールのタイトル',	'value'=>$settings['mail_title']));
				echo $this->Form->input('admin_name',	array('label' => '送信者名',					'value'=>$settings['admin_name']));
				echo $this->Form->input('admin_from',	array('label' => '送信者メールアドレス',		'value'=>$settings['admin_from']));
				*/
				echo Configure::read('form_submit_before')
					.$this->Form->submit(__('保存'), Configure::read('form_submit_defaults'))
					.Configure::read('form_submit_after');
				echo $this->Form->end();
			?>
		</div>
	</div>
	
</div>
