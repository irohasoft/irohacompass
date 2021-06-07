<div class="progresses form">
	<ol class="breadcrumb">
<?php
	if(!$this->isAdminPage() && $this->isRcordPage())
	{
		$theme_url = ['controller' => 'tasks', 'action' => 'index', $content['Theme']['id']];
		
		$this->Html->addCrumb('コース一覧', ['controller' => 'users_themes', 'action' => 'index']);
		$this->Html->addCrumb($content['Theme']['title'], ['controller' => 'tasks', 'action' => 'index_enq', $content['Theme']['id']]);
		
		echo $this->Html->getCrumbs(' / ');
	}
?>
	</ol>
	
	<div id="lblStudySec" class="btn btn-info"></div>
	<div class="ib-page-title"><?= $content['Task']['title']; ?></div>
	<?php $this->start('css-embedded'); ?>
	<style type='text/css'>
		.radio-group
		{
			font-size:18px;
			padding:10px;
			line-height: 180%;
		}
		
		input[type=radio]
		{
			padding:10px;
		}
		
		.form-inline
		{
		}
		
		#lblStudySec
		{
			position: fixed;
			top: 50px;
			right: 20px;
			display: none;
		}
		
		.progress-text,
		.correct-text
		{
			padding: 10px;
			border-radius	: 6px;
		}
		
		img{
			max-width		: 100%;
		}
		
		.result-table
		{
			margin			: 10px;
			width			: 250px;
		}
		
		<?php if($this->action=='admin_record_enq'){?>
		.ib-navi-item
		{
			display: none;
		}
		<?php }?>
	</style>
	<?php $this->end(); ?>
	<?php $this->start('script-embedded'); ?>
	<script>
		var studySec  = 0;
		var mode      = '<?= $mode ?>';
		var timerID   = null;
		
		$(document).ready(function()
		{
			setStudySec();
			timerID = setInterval("setStudySec();", 1000);
		});
		
		function setStudySec()
		{
			$("#ProgressStudySec").val(studySec);
			studySec++;
		}
	</script>
	<?php $this->end(); ?>

	<?php
		$index = 1;
	?>
	<?= $this->Form->create('Progress'); ?>
		<?php foreach ($progresses as $progress): ?>
			<div class="panel panel-info">
				<div class="panel-heading">No.<?= $index;?></div>
				<div class="panel-body">
					<h4><?= h($progress['Progress']['title']); ?></h4>
					
					<div class="progress-text bg-warning">
						<?= $progress['Progress']['body']; ?>
					</div>
					
					<div class="radio-group">
						<?php
						//debug($progress['Progress']['progress_type']);
						$id = $progress['Progress']['id'];
						$answer = @$record['RecordsQuestion'][$index-1]['answer'];
						$is_disabled = ($is_record) ? " disabled" : "";
						
						if($progress['Progress']['progress_type']=='text')
						{
							echo '<textarea name="answer_'.$id.'" '.$is_disabled.' class="form-control" rows="6">'.$answer.'</textarea><br>';
						}
						else
						{
							$list = explode('|', $progress['Progress']['options']);
							
							$val = 1;
							
							foreach($list as $option) {
								$options[$val] = $option;
								$is_checked = ($answer==$val) ? " checked" : "";
								
								echo '<input type="radio" value="'.$val.'" name="data[answer_'.$id.']" '.
									$is_checked.$is_disabled.'> '.$option.'<br>';
								$val++;
							}
						}
						?>
					</div>
					<?php
					$index++;
					?>
				</div>
			</div>
		<?php endforeach; ?>

		<?php
			echo '<div class="form-inline"><!--start-->';
			if (!$is_record)
			{
				echo $this->Form->hidden('study_sec');
				echo '<input type="button" value="送信" class="btn btn-primary btn-lg btn-score" onclick="$(\'#confirmModal\').modal()">';
				echo '&nbsp;';
			}
			
			if($this->action != 'admin_record_enq')
			{
				echo '<input type="button" value="戻る" class="btn btn-default btn-lg" onclick="location.href=\''.Router::url($theme_url).'\'">';
			}
			
			echo '</div><!--end-->';
			echo $this->Form->end();
		?>
	<br>
</div>

<div class="modal fade" id="confirmModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">送信確認</h4>
			</div>
			<div class="modal-body">
				<p>送信してよろしいですか？</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">キャンセル</button>
				<button type="button" class="btn btn-primary btn-score" onclick="$('form').submit();">送信</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
