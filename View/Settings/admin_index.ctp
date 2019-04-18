<?php echo $this->element('admin_menu');?>
<div class="admin-settings-index">
	<div class="panel panel-default">
		<div class="panel-heading">
			<?php echo __('システム設定'); ?>
		</div>
		<div class="panel-body">
			<?php echo $this->Form->create('Setting', Configure::read('form_defaults')); ?>
			<?php
				echo $this->Form->input('title',		array('label' => 'システム名',					'value'=>$settings['title']));
				echo $this->Form->input('copyright',	array('label' => 'コピーライト',				'value'=>$settings['copyright']));
				echo $this->Form->input('color',		array('label' => 'テーマカラー',				'options'=>$colors, 'selected'=>$settings['color']));
				echo $this->Form->input('information',	array('label' => '全体のお知らせ',				'value'=>$settings['information'], 'type' => 'textarea'));
				/*
				echo $this->Form->input('mail_title',	array('label' => '進捗の更新メールのタイトル',	'value'=>$settings['mail_title']));
				echo $this->Form->input('admin_name',	array('label' => '送信者名',					'value'=>$settings['admin_name']));
				echo $this->Form->input('admin_from',	array('label' => '送信者メールアドレス',		'value'=>$settings['admin_from']));
				*/
			?>
			<div class="form-group">
				<div class="col col-sm-9 col-sm-offset-3">
					<?php echo $this->Form->end(array('label' => __('保存'), 'class' => 'btn btn-primary')); ?>
				</div>
			</div>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
	
</div>
