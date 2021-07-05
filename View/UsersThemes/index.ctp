<?= $this->Html->script( 'Chart.bundle.js');?>
<?= $this->Html->script( 'Chart.utils.js');?>
<?php $this->start('script-embedded'); ?>
<script>
	var labels			= <?= json_encode($labels);?>;
	var login_data		= <?= json_encode($login_data);?>;
	var progress_data	= <?= json_encode($progress_data);?>;
	
	$(document).ready(function()
	{
		CommonUtil.createProgressChart(labels, login_data, progress_data, 150);
	});
</script>
<?php $this->end(); ?>
<div class="users-themes-index">
	<div class="panel panel-success">
		<div class="panel-heading"><?= __('お知らせ'); ?></div>
		<div class="panel-body">
			<?php if($info!=""){?>
			<div class="well">
				<?php
				$info = $this->Text->autoLinkUrls($info, [ 'target' => '_blank']);
				$info = nl2br($info);
				echo $info;
				?>
			</div>
			<?php }?>
			
			<?php if(count($infos) > 0){?>
			<table cellpadding="0" cellspacing="0">
			<tbody>
			<?php foreach ($infos as $info): ?>
			<tr>
				<td width="100" valign="top"><?= h(Utils::getYMD($info['Info']['created'])); ?></td>
				<td><?= $this->Html->link($info['Info']['title'], ['controller' => 'infos', 'action' => 'view', $info['Info']['id']]); ?></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
			</table>
			<div class="text-right"><a href="<?= Router::url(['controller' => 'infos', 'action' => 'index']);?>"><?= __('一覧を表示')?></a></div>
			<?php }?>
			
			<?= $no_info;?>
		</div>
	</div>
	<p class="text-right">
	</p>
	<?php if(count($records) > 0){?>
	<div class="panel panel-default">
		<div class="panel-heading"><?= __('最近の進捗'); ?></div>
		<div class="panel-body">
			<div class="chart-container">
				<canvas id="chart"></canvas>
			</div>
			
			<!--最近の進捗一覧-->
			<div class="progress-container">
			<table cellpadding="0" cellspacing="0">
			<thead>
			<tr>
				<th nowrap class="col-theme"><?= __('学習テーマ')?></th>
				<th nowrap class="col-task"><?= __('課題')?></th>
				<th nowrap><?= __('氏名')?></th>
				<!--
				<th nowrap class="ib-col-center">進捗率</th>
				<th nowrap class="ib-col-center">進捗率(全体)</th>
				<th nowrap class="ib-col-center">完了</th>
				-->
				<th class="ib-col-center" nowrap><?= __('種別')?></th>
				<th class="ib-col-datetime"><?= __('更新日時')?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($records as $record): ?>
				<tr>
					<td nowrap><div class="reader"><a href="<?= Router::url(['controller' => 'tasks', 'action' => 'index', $record['Theme']['id']]);?>"><?= h($record['Theme']['title']); ?></a></div></td>
					<td nowrap><div class="reader"><a href="<?= Router::url(['controller' => 'progresses', 'action' => 'index', $record['Task']['id']]);?>"><?= h($record['Task']['title']); ?></a></div></td>
					<td nowrap><?= h($record['User']['name']); ?>&nbsp;</td>
					<!--
					<td class="ib-col-center"><?= h($record['Record']['rate']); ?>&nbsp;</td>
					<td class="ib-col-center"><?= h($record['Record']['theme_rate']); ?>&nbsp;</td>
					<td nowrap class="ib-col-center"><?= h(Configure::read('content_status.'.$record['Record']['is_complete'])); ?>&nbsp;</td>
					-->
					<td nowrap class="ib-col-center"><?= h(Configure::read('record_type.'.$record['Record']['record_type'])); ?>&nbsp;</td>
					<td nowrap class="ib-col-date"><?= h(Utils::getYMDHN($record['Record']['created'])); ?>&nbsp;</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
			</table>
			</div>
			
			<div class="text-right"><a href="<?= Router::url(['controller' => 'records', 'action' => 'progress']);?>"><?= __('一覧を表示')?></a></div>
		</div>
	</div>
	<?php }?>
	
	<div class="panel panel-default">
		<div class="panel-heading">アイデアボックス</div>
		<div class="panel-body">
			<?php
				echo $this->Form->create('Idea', ['class' => 'idea-form']);
				echo $this->Form->textarea('body', ['class' => 'idea-body', 'placeholder' => __('新しいアイデア・メモ等を書き込んでください')]);
				echo $this->Form->submit(__('追加'), ['class' => 'btn btn-primary idea-submit']);
				echo $this->Form->end();
			?>
			<div class="text-right">
				<?= $idea_count?><?= __('件登録済み')?><br>
				<a href="<?= Router::url(['controller' => 'ideas', 'action' => 'index']);?>"><?= __('一覧を表示')?></a>
			</div>
		</div>
	</div>
	
	<div class="panel panel-info">
		<div class="panel-heading"><?= __('学習テーマ一覧')?></div>
		<div class="buttons_container">
			<button class="btn btn-primary btn-add" onclick="location.href='<?= Router::url(['controller' => 'themes', 'action' => 'add']) ?>'">+ <?= __('学習テーマを追加')?></button>
		</div>
		<div class="panel-body">
			<?php if($themes) {?>
			<div class="theme-list"><?= __('所有しているテーマ')?></div>
			<ul class="list-group">
			<?php foreach($themes as $theme): ?>
				<?php if($theme['Theme']['user_id']==$loginedUser['id']): ?>
				<a href="<?= Router::url(['controller' => 'tasks', 'action' => 'index', $theme['Theme']['id']]);?>" class="list-group-item">
					<?php if($theme[0]['left_cnt']!=0){?>
					<button type="button" class="btn btn-danger btn-rest">残り <span class="badge"><?= h($theme[0]['left_cnt']); ?></span></button>
					<?php }?>
					<h4 class="list-group-item-heading"><?= h($theme['Theme']['title']);?></h4>
					<p class="list-group-item-text">
						<span><?= __('学習開始日')?>: <?= h($theme[0]['first_date']); ?></span>
						<span><?= __('最終学習日')?>: <?= h($theme[0]['last_date']); ?></span>
					</p>
				</a>
				<?php endif;?>
			<?php endforeach;?>
			</ul>
			
			<div class="theme-list"><?= __('それ以外のテーマ')?></div>
			<ul class="list-group">
			<?php foreach($themes as $theme): ?>
				<?php if($theme['Theme']['user_id']!=$loginedUser['id']): ?>
				<a href="<?= Router::url(['controller' => 'tasks', 'action' => 'index', $theme['Theme']['id']]);?>" class="list-group-item">
					<?php if($theme[0]['left_cnt']!=0){?>
					<button type="button" class="btn btn-danger btn-rest">残り <span class="badge"><?= h($theme[0]['left_cnt']); ?></span></button>
					<?php }?>
					<h4 class="list-group-item-heading"><?= h($theme['Theme']['title']);?></h4>
					<p class="list-group-item-text">
						<span><?= __('学習開始日')?>: <?= h($theme[0]['first_date']); ?></span>
						<span><?= __('最終学習日')?>: <?= h($theme[0]['last_date']); ?></span>
					</p>
				</a>
				<?php endif;?>
			<?php endforeach;?>
			</ul>
			<?php }?>
			
			<?= $no_record;?>
		</div>
	</div>
</div>
