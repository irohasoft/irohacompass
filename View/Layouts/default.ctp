<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2018 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohacompass.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

//$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	
	<title><?php echo h($this->Session->read('Setting.title')); ?></title>
	<meta name="application-name" content="iroha Compass">
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<?php
		// 管理画面フラグ
		$is_admin_page = (($this->params['admin']==1)&&($this->params['action']!='admin_login'));
		
		// 受講者向け画面及び、管理システムのログイン画面のみ viewport を設定（スマートフォン対応）
		if(!$is_admin_page)
			echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
		
		echo $this->Html->meta('icon');

		echo $this->Html->css('cake.generic');
		echo $this->Html->css('jquery-ui');
		echo $this->Html->css('bootstrap.min');
		echo $this->Html->css('common.css');
		
		if($is_admin_page)
			echo $this->Html->css('admin.css');

		echo $this->Html->script('jquery-1.9.1.min.js');
		echo $this->Html->script('jquery-ui-1.9.2.min.js');
		echo $this->Html->script('bootstrap.min.js');
		echo $this->Html->script('moment.js');
		echo $this->Html->script('marked.min.js');
		echo $this->Html->script('common.js?20181004');
		
		// デモモード用JavaScript
		if(Configure::read('demo_mode'))
			echo $this->Html->script('demo.js');
		
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
		echo $this->fetch('css-embedded');
		echo $this->fetch('script-embedded');
	?>
	<script>
	var _controller	= '<?php echo $this->params['controller'] ?>';
	var _action		= '<?php echo $this->params['action'] ?>';
	var _params		= '<?php echo join(',', $this->params['pass']) ?>';
	var _sec		= 0;
	
	$(document).ready(function()
	{
		// 一定時間経過後、メッセージを閉じる
		setTimeout(function() {
			$('#flashMessage').fadeOut("slow");
		}, 1500);
		
		
		setInterval('_addSec()', 1000);
		
		$.ajax({
			url: "<?php echo Router::url(array('controller' => 'logs', 'action' => 'add')) ?>",
			type: "POST",
			data: {
				log_type	: 'view', 
				log_content	: '', 
				controller	: _controller,
				action		: _action,
				params		: _params
			},
			dataType: "text",
			success : function(response){
				//通信成功時の処理
				//alert(response);
			},
			error: function(){
				//通信失敗時の処理
				//alert('通信失敗');
			}
		});
	});

	$(window).on('beforeunload', function(event)
	{
		$.ajax({
			url: "<?php echo Router::url(array('controller' => 'logs', 'action' => 'add')) ?>",
			type: "POST",
			data: {
				log_type	: 'move', 
				log_content	: '', 
				controller	: _controller,
				action		: _action,
				params		: _params,
				sec			: _sec
			},
			dataType: "text",
			success : function(response){
				//通信成功時の処理
				//alert(response);
			},
			error: function(){
				//通信失敗時の処理
				//alert('通信失敗');
			}
		});
		
		return;
	});
	
	function _addSec()
	{
		_sec++;
		
		var $target = $('input[name="study_sec"]');
		
		if($target);
		{
			var sec = parseInt($target.val());
			sec++;
			$target.val(sec);
		}
	}

	</script>
	<style>
		.ib-theme-color
		{
			background-color	: <?php echo h($this->Session->read('Setting.color')); ?>;
			color				: white;
		}
		
		.ib-logo a
		{
			color				: white;
			text-decoration		: none;
		}
	</style>
</head>
<body>
	<div class="header ib-theme-color">
		<div class="ib-logo ib-left">
			<a href="<?php echo $this->Html->url('/')?>"><?php echo h($this->Session->read('Setting.title')); ?></a>
		</div>
<?php
		if(@$loginedUser)
		{
			echo '<div class="ib-navi-item ib-right">'.$this->Html->link(__('ログアウト'), $logoutURL).'</div>';
			echo '<div class="ib-navi-sepa ib-right"></div>';
			echo '<div class="ib-navi-item ib-right">'.$this->Html->link(__('設定'), array('controller' => 'users', 'action' => 'setting')).'</div>';
			echo '<div class="ib-navi-sepa ib-right"></div>';
			echo '<div class="ib-navi-item ib-right">'.__('ようこそ ').h($loginedUser["name"]).' さん </div>';
		}
?>
	</div>
	
	<div id="container">
		<div id="header" class="row">
		</div>
		<div id="content" class="row">
			<?php echo $this->Session->flash(); ?>

			<?php echo $this->fetch('content'); ?>
		</div>
		<div id="footer" class="row">
		</div>
	</div>
	
	<div class="footer ib-theme-color text-center">
		<?php echo h($this->Session->read('Setting.copyright')); ?>
	</div>
	
	<div class="irohasoft">
		Powered by <a href="https://irohacompass.irohasoft.jp/">iroha Compass</a>
	</div>
	
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
