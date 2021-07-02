<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

// Custom Config
$config['dummy']		= [];

// 感情アイコン
//$config['use_emotion_icon']	= true;

// 詳細なログ記録機能を使用する
//$config['use_detailed_logging']	= true;

// 感情アイコンを使用する
//$config['use_emotion_icon']	= true;

// スマイル機能を使用する
//$config['use_smile']		= true;

// iroha Note を使用する
//$config['use_irohanote']	= true;

/*
$config['content_type']	= array(
	'text'		=> 'テキスト',
	'markdown'	=> 'Markdown',
	'irohanote'	=> 'iroha Note',
);
*/

/*
class I18n {
	public static function translate($singular) {
		return $singular;
	}
	
	public static function insertArgs($translated, array $args) {
		$translated = str_replace('学習',	'研究',		$translated);

		$len = count($args);
		if ($len === 0 || ($len === 1 && $args[0] === null)) {
			return $translated;
		}

		if (is_array($args[0])) {
			$args = $args[0];
		}

		$translated = preg_replace('/(?<!%)%(?![%\'\-+bcdeEfFgGosuxX\d\.])/', '%%', $translated);
		return vsprintf($translated, $args);
	}
}
*/
