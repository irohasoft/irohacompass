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
<title><?= $pageTitle; ?></title>

<?php if (!Configure::read('debug')): ?>
<meta http-equiv="Refresh" content="<?= $pause; ?>;url=<?= $url; ?>"/>
<?php endif ?>
<style><!--
P { text-align:center; font:bold 1.1em sans-serif }
A { color:#444; text-decoration:none }
A:HOVER { text-decoration: underline; color:#44E }
--></style>
</head>
<body>
<p><a href="<?= $url; ?>"><?= $message; ?></a></p>
</body>
</html>
