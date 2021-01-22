<div class="users-setting">
	<div class="panel panel-default">
		<div class="panel-heading">
			<span data-localize="ideabox">アイデアボックス</span>
		</div>
		<div class="panel-body">
			<?php
				echo $this->Form->create('Idea', Configure::read('form_defaults'));
				echo $this->Form->input('Idea.body', [
					'label' => '<span data-localize="new_idea">新しいアイデア・メモ</span>',
					'autocomplete' => 'new-password'
				]);
			?>
			<div class="form-group">
				<div class="col col-sm-9 col-sm-offset-3">
					<?php echo $this->Form->submit('追加', Configure::read('form_submit_defaults')); ?>
				</div>
			</div>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
		
		<?php foreach ($ideas as $idea): ?>
		<div class="panel panel-default">
			<div class="pull-right">
				<p><?php echo h(Utils::getYMDHN($idea['Idea']['created'])); ?></p>
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
					/*
					echo $this->Form->button('編集', array(
						'class'		=> 'btn btn-success btn-edit',
						'onclick'	=> "location.href='".Router::url(array('action' => 'index', $idea['Idea']['id']))."#edit'",
					));
					*/
					
					echo '<br>';
					echo $this->Form->postLink(__('削除'),
						['action' => 'delete', $idea['Idea']['id']],
						['class'=>'btn btn-default', 'data-localize' => 'delete'],
						__('削除してもよろしいですか?')
					);
					
					/*
					echo $this->Form->postLink(__('削除'), 
							array('action' => 'delete', $idea['Idea']['id']), 
							array('class'=>'text-danger'), 
							__('削除してもよろしいですか?')
					);
					*/
					echo $this->Form->hidden('id', ['id'=>'', 'class'=>'target_id', 'value'=>$idea['Idea']['id']]);
				}
				
				?>
			</div>
		</div>
		<?php endforeach; ?>
		<?php echo $this->element('paging');?>
</div>
