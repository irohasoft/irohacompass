<?php //echo $this->element('menu');?>
<?= $this->Html->script( 'Chart.bundle.js');?>
<?= $this->Html->script( 'Chart.utils.js');?>
<?php $this->start('css-embedded'); ?>
<style type='text/css'>
#RecordFromDateYear,
#RecordToDateYear
{
	width		: 100px;
}

#RecordFromDateMonth,
#RecordToDateMonth,
#RecordFromDateDay,
#RecordToDateDay
{
	width		: 80px;
}

#RecordThemeId
{
	max-width	: 200px;
}

input[type='text'], textarea,
.form-control, 
label
{
	font-size	: 12px;
	font-weight	: normal;
	height		: 30px;
	padding		: 4px;
}

.ib-search-buttons
{
	float		: right;
}

.ib-search-buttons .btn
{
	margin-right: 10px;
}

table tr td
{
	padding		: 5px;
}

.ib-row
{
	width: 100%;
	height: 40px;
}

.ib-horizontal
{
	padding: 20px;
}

.chart-container
{
height: 300px;
}

<?php if($is_popup) {?>
.header,
.breadcrumb,
.footer,
.record-container
{
	display : none;
}

#content
{
	min-height: 400px;
}
<?php }?>

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
	
	.ib-row,
	.ib-search-date-container
	{
		height: initial;
	}
	
	.ib-horizonta,
	.input
	{
		padding: 2px;
		float: initial;
		width: 100%;
	}
	
	.ib-horizontal label
	{
		float: initial;
		text-align: initial;
		width: 100%;
	}
}
</style>
<?php $this->end(); ?>
<?php $this->start('script-embedded'); ?>
<script>
	var labels			= <?= json_encode($labels);?>;
	var login_data		= <?= json_encode($login_data);?>;
	var progress_data	= <?= json_encode($progress_data);?>;
	
	$(document).ready(function()
	{
		CommonUtil.createProgressChart(labels, login_data, progress_data, 300);
	});
</script>
<?php $this->end(); ?>

<ol class="breadcrumb">
<?php
	$this->Html->addCrumb('HOME', [
		'controller' => 'users_themes',
		'action' => 'index'
	]);
	echo $this->Html->getCrumbs(' / ');
?>
</ol>
<div class="infos index">
	<div class="panel panel-success">
		<div class="panel-heading"><?= __('最近の進捗'); ?></div>
		<div class="chart-container">
			<canvas id="chart"></canvas>
		</div>
		<div class="record-container">
			<div class="ib-horizontal">
				<?php
					echo $this->Form->create('Record');
					echo '<div class="ib-search-buttons">';
					echo $this->Form->submit(__('検索'),	['class' => 'btn btn-info', 'div' => false]);
					echo '</div>';
					
					echo '<div class="ib-row">';
					echo $this->Form->input('theme_id',	['label' => '学習テーマ :', 'options'=>$themes, 'selected'=>$theme_id, 'empty' => '全て', 'required'=>false, 'class'=>'form-control']);
					echo $this->Form->input('contenttitle',	['label' => '課題名 :', 'value'=>$contenttitle, 'class'=>'form-control']);
					echo '</div>';
					
					echo '<div class="ib-search-date-container">';
					echo $this->Form->input('from_date', [
						'type' => 'date',
						'dateFormat' => 'YMD',
						'monthNames' => false,
						'timeFormat' => '24',
						'minYear' => date('Y') - 5,
						'maxYear' => date('Y'),
						'separator' => ' / ',
						'label'=> '対象日時 : ',
						'class'=>'form-control',
						'style' => 'display: inline;',
						'value' => $from_date
					]);
					echo $this->Form->input('to_date', [
						'type' => 'date',
						'dateFormat' => 'YMD',
						'monthNames' => false,
						'timeFormat' => '24',
						'minYear' => date('Y') - 5,
						'maxYear' => date('Y'),
						'separator' => ' / ',
						'label'=> '～',
						'class'=>'form-control',
						'style' => 'display: inline;',
						'value' => $to_date
					]);
					echo '</div>';
					echo $this->Form->end();
				?>
			</div>
			<div class="panel-body">
				<table cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th nowrap><?= $this->Paginator->sort('theme_id', '学習テーマ'); ?></th>
					<th nowrap><?= $this->Paginator->sort('content_id', '課題'); ?></th>
					<th nowrap><?= $this->Paginator->sort('user_id', '氏名'); ?></th>
					<!--
					<th nowrap class="ib-col-center"><?= $this->Paginator->sort('rate', '進捗率'); ?></th>
					<th nowrap class="ib-col-center"><?= $this->Paginator->sort('theme_rate', '進捗率(全体)'); ?></th>
					<th nowrap class="ib-col-center"><?= $this->Paginator->sort('is_complete', '完了'); ?></th>
					<th class="ib-col-center"><?= $this->Paginator->sort('study_sec', '学習時間'); ?></th>
					-->
					<th class="ib-col-center" nowrap><?= $this->Paginator->sort('record_type', '種別'); ?></th>
					<th class="ib-col-datetime"><?= $this->Paginator->sort('created', '学習日時'); ?></th>
				</tr>
				</thead>
				<tbody>
					<?php foreach ($records as $record): ?>
					<tr>
						<td><a href="<?= Router::url(['controller' => 'tasks', 'action' => 'index', $record['Theme']['id']]);?>"><?= h($record['Theme']['title']); ?></a></td>
						<td><a href="<?= Router::url(['controller' => 'progresses', 'action' => 'index', $record['Task']['id']]);?>"><?= h($record['Task']['title']); ?></a></td>
						<td><?= h($record['User']['name']); ?>&nbsp;</td>
						<!--
						<td class="ib-col-center"><?= h($record['Record']['rate']); ?>&nbsp;</td>
						<td class="ib-col-center"><?= h($record['Record']['theme_rate']); ?>&nbsp;</td>
						<td nowrap class="ib-col-center"><?= h(Configure::read('content_status.'.$record['Record']['is_complete'])); ?>&nbsp;</td>
						<td class="ib-col-center"><?= h(Utils::getHNSBySec($record['Record']['study_sec'])); ?>&nbsp;</td>
						-->
						<td nowrap class="ib-col-center"><?= h(Configure::read('record_type.'.$record['Record']['record_type'])); ?>&nbsp;</td>
						<td class="ib-col-date"><?= h(Utils::getYMDHN($record['Record']['created'])); ?>&nbsp;</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
				</table>
				<?= $this->element('paging');?>
			</div>
		</div>
	</div>
</div>