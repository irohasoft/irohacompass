<?php //echo $this->element('menu');?>
<?php echo $this->Html->script( 'Chart.bundle.js');?>
<?php echo $this->Html->script( 'Chart.utils.js');?>
<?php $this->start('css-embedded'); ?>
<style>
.btn-rest
{
	float: right;
}

.chart-container
{
	height: 200px;
}

@media only screen and (max-width:800px)
{
	a
	{
		display: block;
	}
	
	.list-group-item-text span
	{
		display: block;
	}
	
	table tr td
	{
		padding: 2px;
		font-size: 12px;
	}
	
	.col-theme,
	.col-task
	{
		width:30%;
	}
}
</style>
<?php $this->end(); ?>
<?php $this->start('script-embedded'); ?>
<script>
	var labels			= <?php echo json_encode($labels);?>;
	var login_data		= <?php echo json_encode($login_data);?>;
	var progress_data	= <?php echo json_encode($progress_data);?>;
	
	$(document).ready(function()
	{
		CommonUtil.createProgressChart(labels, login_data, progress_data, 200);
	});
</script>
<?php $this->end(); ?>
<div class="themes index">
	<div class="panel panel-success">
		<div class="panel-heading"><?php echo __('お知らせ'); ?></div>
		<div class="panel-body">
			<?php if($info!=""){?>
			<div class="well">
				<?php
				$info = $this->Text->autoLinkUrls($info, array( 'target' => '_blank'));
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
				<td width="100" valign="top"><?php echo h(Utils::getYMD($info['Info']['created'])); ?></td>
				<td><?php echo $this->Html->link($info['Info']['title'], array('controller' => 'infos', 'action' => 'view', $info['Info']['id'])); ?></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
			</table>
			<div class="text-right"><a href="<?php echo Router::url(array('controller' => 'infos', 'action' => 'index'));?>">一覧を表示</a></div>
			<?php }?>
			
			<?php echo $no_info;?>
		</div>
	</div>
	
	<?php if(count($records) > 0){?>
	<div class="panel panel-default">
		<div class="panel-heading"><?php echo __('最近の進捗'); ?></div>
		<div class="panel-body">
			<div class="chart-container">
				<canvas id="chart"></canvas>
			</div>
			<table cellpadding="0" cellspacing="0">
			<thead>
			<tr>
				<th nowrap class="col-theme">学習テーマ</th>
				<th nowrap class="col-task">課題</th>
				<th nowrap>氏名</th>
				<!--
				<th nowrap class="ib-col-center">進捗率</th>
				<th nowrap class="ib-col-center">進捗率(全体)</th>
				<th nowrap class="ib-col-center">完了</th>
				-->
				<th class="ib-col-center" nowrap>種別</th>
				<th class="ib-col-datetime">更新日時</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($records as $record): ?>
				<tr>
					<td><a href="<?php echo Router::url(array('controller' => 'tasks', 'action' => 'index', $record['Theme']['id']));?>"><?php echo h($record['Theme']['title']); ?></a></td>
					<td><a href="<?php echo Router::url(array('controller' => 'progresses', 'action' => 'index', $record['Task']['id']));?>"><?php echo h($record['Task']['title']); ?></a></td>
					<td><?php echo h($record['User']['name']); ?>&nbsp;</td>
					<!--
					<td class="ib-col-center"><?php echo h($record['Record']['rate']); ?>&nbsp;</td>
					<td class="ib-col-center"><?php echo h($record['Record']['theme_rate']); ?>&nbsp;</td>
					<td nowrap class="ib-col-center"><?php echo h(Configure::read('content_status.'.$record['Record']['is_complete'])); ?>&nbsp;</td>
					-->
					<td nowrap class="ib-col-center"><?php echo h(Configure::read('record_type.'.$record['Record']['record_type'])); ?>&nbsp;</td>
					<td class="ib-col-date"><?php echo h(Utils::getYMDHN($record['Record']['created'])); ?>&nbsp;</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
			</table>
			<div class="text-right"><a href="<?php echo Router::url(array('controller' => 'records', 'action' => 'progress'));?>">一覧を表示</a></div>
		</div>
	</div>
	<?php }?>
	
	<div class="panel panel-info">
	<div class="panel-heading"><?php echo __('学習テーマ一覧'); ?></div>
	<div class="buttons_container">
		<button type="button" class="btn btn-primary btn-add" onclick="location.href='<?php echo Router::url(array('controller' => 'themes', 'action' => 'add')) ?>'">+ 学習テーマを追加</button>
	</div>
	<div class="panel-body">
		<ul class="list-group">
		<?php foreach ($themes as $theme): ?>
		<?php //debug($theme)?>
			<a href="<?php echo Router::url(array('controller' => 'tasks', 'action' => 'index', $theme['Theme']['id']));?>" class="list-group-item">
				<?php if($theme[0]['left_cnt']!=0){?>
				<button type="button" class="btn btn-danger btn-rest">残り <span class="badge"><?php echo h($theme[0]['left_cnt']); ?></span></button>
				<?php }?>
				<h4 class="list-group-item-heading"><?php echo h($theme['Theme']['title']);?></h4>
				<p class="list-group-item-text">
					<span>学習開始日: <?php echo h($theme[0]['first_date']); ?></span>
					<span>最終学習日: <?php echo h($theme[0]['last_date']); ?></span>
				</p>
			</a>
		<?php endforeach; ?>
		<?php echo $no_record;?>
		</ul>
	</div>
	</div>
</div>
