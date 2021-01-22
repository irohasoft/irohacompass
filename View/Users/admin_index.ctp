<?php echo $this->element('admin_menu');?>
<div class="admin-users-index">
	<div class="ib-page-title"><?php echo __('ユーザ一覧'); ?></div>
	<div class="buttons_container">
		<button type="button" class="btn btn-primary btn-add" onclick="location.href='<?php echo Router::url(['action' => 'add']) ?>'">+ 追加</button>
	</div>
	<div class="ib-horizontal">
		<?php
			echo $this->Form->create('User');
			echo $this->Form->input('group_id',		[
				'label' => 'グループ : ', 
				'options'=>$groups, 
				'selected'=>$group_id, 
				'empty' => '全て', 
				'required'=>false, 
				'class' => 'form-control',
				'onchange' => 'submit(this.form);'
			]);
			echo $this->Form->input('username',		['label' => 'ログインID : ', 'required' => false]);
			echo $this->Form->input('name',			['label' => '氏名 : '  , 'required' => false]);
		?>
		<input type="submit" class="btn btn-info btn-add" value="検索">
		<?php
			echo $this->Form->end();
		?>
	</div>
	<table>
	<thead>
	<tr>
		<th nowrap><?php echo $this->Paginator->sort('username', 'ログインID'); ?></th>
		<th nowrap class="col-width"><?php echo $this->Paginator->sort('name', '氏名'); ?></th>
		<th nowrap><?php echo $this->Paginator->sort('role', '権限'); ?></th>
		<th nowrap><?php echo __('所属グループ'); ?></th>
		<th nowrap class="ib-col-datetime"><?php echo __('学習テーマ'); ?></th>
		<th class="ib-col-datetime"><?php echo $this->Paginator->sort('last_accessed', '最終アクセス日時'); ?></th>
		<th class="ib-col-datetime"><?php echo $this->Paginator->sort('created', '作成日時'); ?></th>
		<?php if($loginedUser['role']=='admin') {?>
		<th class="ib-col-action"><?php echo __('Actions'); ?></th>
		<?php }?>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($users as $user): ?>
	<tr>
		<td><?php echo h($user['User']['username']); ?>&nbsp;</td>
		<td><?php echo h($user['User']['name']); ?></td>
		<td nowrap><?php echo h(Configure::read('user_role.'.$user['User']['role'])); ?>&nbsp;</td>
		<td><div class="reader" title="<?php echo h($user[0]['group_title']); ?>"><p><?php echo h($user[0]['group_title']); ?>&nbsp;</p></div></td>
		<td><div class="reader" title="<?php echo h($user[0]['theme_title']); ?>"><p><?php echo h($user[0]['theme_title']); ?>&nbsp;</p></div></td>
		<td class="ib-col-datetime"><?php echo h(Utils::getYMDHN($user['User']['last_accessed'])); ?>&nbsp;</td>
		<td class="ib-col-datetime"><?php echo h(Utils::getYMDHN($user['User']['created'])); ?>&nbsp;</td>
		<?php if($loginedUser['role']=='admin') {?>
		<td class="ib-col-action">
			<button type="button" class="btn btn-success"
				onclick="location.href='<?php echo Router::url(['action' => 'edit', $user['User']['id']]) ?>'">編集</button>
			<?php
			if($loginedUser['role']=='admin')
			{
				echo $this->Form->postLink(__('削除'), [
					'action' => 'delete',
					$user['User']['id']
				], [
					'class' => 'btn btn-danger'
				], __('[%s] を削除してもよろしいですか?', $user['User']['name']));
			}
		?>
		</td>
		<?php }?>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<?php echo $this->element('paging');?>
</div>