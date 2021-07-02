<div class="users-setting">
	<div class="panel <?= ($is_add) ? 'panel-default' :  'panel-danger'; ?>">
		<div class="panel-heading">
			<?= (!$is_add) ? '<span data-localize="edit">編集</span>' :  '<span data-localize="ideabox">アイデアボックス</span>'; ?>
		</div>
		<div class="panel-body">
			<?php
				echo $this->Form->create('Idea', Configure::read('form_defaults'));
				echo $this->Form->input('id');
				echo $this->Form->input('Idea.body', [
					'label' => '<span data-localize="new_idea">新しいアイデア・メモ</span>',
					'autocomplete' => 'new-password'
				]);
				echo Configure::read('form_submit_before')
					.$this->Form->submit(__('保存'), Configure::read('form_submit_defaults'))
					.Configure::read('form_submit_after');
				echo $this->Form->end();
			?>
		</div>
	</div>
		
		<?php foreach ($ideas as $idea): ?>
		<div class="panel panel-default">
			<div class="pull-right">
				<p><?= h(Utils::getYMDHN($idea['Idea']['created'])); ?></p>
			</div>
			<br>
			<div class="panel-body">
				<p>
					<?php
					$body  = $idea['Idea']['body'];
					//$body  = $this->Text->autoLinkUrls($body);
					// 暫定対応
					$body  = $this->Text->autoLinkUrls($body, ['escape' => false]);
					$body  = nl2br($body);
					
					echo $body;
					?>
				</p>
				<?php
				// 自分の進捗のみ編集、削除可能とする
				if($idea['User']['id']==$loginedUser['id'])
				{
					echo '<br>';
					
					echo $this->Form->button(__('編集'), array(
						'class'		=> 'btn btn-success btn-edit', 'data-localize' => 'edit',
						'onclick'	=> "location.href='".Router::url(array('action' => 'index', $idea['Idea']['id']))."#edit'",
					));
					
					echo ' ';
					
					echo $this->Form->postLink(__('削除'),
						['action' => 'delete', $idea['Idea']['id']],
						['class'=>'btn btn-danger', 'data-localize' => 'delete'],
						__('削除してもよろしいですか?')
					);
					
					echo $this->Form->hidden('id', ['id'=>'', 'class'=>'target_id', 'value'=>$idea['Idea']['id']]);
				}
				
				?>
			</div>
		</div>
		<?php endforeach; ?>
		<?= $this->element('paging');?>
</div>
