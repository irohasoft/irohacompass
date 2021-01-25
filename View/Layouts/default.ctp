<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */
?>
<!DOCTYPE html>
<html>
<head>
	<?= $this->Html->charset(); ?>
	
	<title><?= h($this->Session->read('Setting.title')); ?></title>
	<meta name="application-name" content="iroha Compass">
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<?php
		// 管理画面フラグ（ログイン画面は例外とする）
		$is_admin_page = (($this->params['admin']==1)&&($this->params['action']!='admin_login'));
		
		// 受講者向け画面及び、管理システムのログイン画面のみ viewport を設定（スマートフォン対応）
		if(!$is_admin_page)
			echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
		
		echo $this->Html->meta('icon');

		echo $this->Html->css('cake.generic');
		echo $this->Html->css('jquery-ui');
		echo $this->Html->css('bootstrap.min');
		echo $this->Html->css('common.css?20200308');
		
		// 管理画面用CSS
		if($is_admin_page)
			echo $this->Html->css('admin.css?20190401');

		// カスタマイズ用CSS
		echo $this->Html->css('custom.css?20190401');
		
		echo $this->Html->script('jquery-1.9.1.min.js');
		echo $this->Html->script('jquery-ui-1.9.2.min.js');
		echo $this->Html->script('jquery.localize.min.js');
		echo $this->Html->script('bootstrap.min.js');
		echo $this->Html->script('moment.js');
		echo $this->Html->script('marked.min.js');
		echo $this->Html->script('common.js?20200622');
		
		// 管理画面用スクリプト
		if($is_admin_page)
			echo $this->Html->script('admin.js?20190401');
		
		// デモモード用スクリプト
		if(Configure::read('demo_mode'))
			echo $this->Html->script('demo.js');
		
		// カスタマイズ用スクリプト
		echo $this->Html->script('custom.js?20190401');
		
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
		echo $this->fetch('css-embedded');
		echo $this->fetch('script-embedded');
	?>
	<script>
	var _controller		= '<?= $this->params['controller'] ?>';
	var _action			= '<?= $this->params['action'] ?>';
	var _params			= '<?= join(',', $this->params['pass']) ?>';
	var _sec			= 0;
	var G_WEBROOT		= '<?= $this->Html->webroot;?>';
	var G_LANG			= '<?= @$loginedUser['lang'];?>';
	var URL_LOGS_ADD	= '<?= Router::url(['controller' => 'logs', 'action' => 'add']) ?>';
	
	</script>
	<style>
		.ib-theme-color
		{
			background-color	: <?= h($this->Session->read('Setting.color')); ?>;
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
			<a href="<?= $this->Html->url('/')?>"><?= h($this->Session->read('Setting.title')); ?></a>
		</div>
		<?php if(@$loginedUser) {?>
		<div class="ib-navi-item ib-right"><?= $this->Html->link(__('ログアウト'), ['controller' => 'users', 'action' => 'logout']); ?></div>
		<div class="ib-navi-sepa ib-right"></div>
		<div class="ib-navi-item ib-right"><?= $this->Html->link('<span data-localize="setting">設定</span>', ['controller' => 'users', 'action' => 'setting'], ['escape' => false]); ?></div>
		<div class="ib-navi-sepa ib-right"></div>
		<div class="ib-navi-item ib-right navi-item-idea"><?= $this->Html->link('<span data-localize="idea">アイデア</span>', ['controller' => 'ideas'], ['escape' => false]); ?></div>
		<div class="ib-navi-sepa ib-right"></div>
		<div class="ib-navi-item ib-right"><?= __('ようこそ ').h($loginedUser["name"]); ?> さん </div>
		<?php }?>
	</div>
	
	<div id="container">
		<div id="header" class="row">
		</div>
		<div id="content" class="row">
			<?= $this->Session->flash(); ?>

			<?= $this->fetch('content'); ?>
		</div>
		<div id="footer" class="row">
		</div>
	</div>
	
	<div class="ib-theme-color text-center">
		<?= h($this->Session->read('Setting.copyright')); ?>
	</div>
	
	<div class="irohasoft">
		Powered by <a href="https://irohacompass.irohasoft.jp/">iroha Compass</a>
	</div>
	
	<?= $this->element('sql_dump'); ?>
</body>
</html>
