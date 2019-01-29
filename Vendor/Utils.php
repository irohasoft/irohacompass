<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2018 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohacompass.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

// ユーティリティクラス
class Utils
{
	//------------------------------//
	//	コンストラクタ				//
	//------------------------------//
	public function Utils()
	{
	}
	
	public static function getYMD($str)
	{
		return substr($str, 0, 10);
	}

	public static function getYMDHN($str)
	{
		return substr($str, 0, 16);
	}
	
	public static function getHNSBySec($sec)
	{
		$hour	= floor($sec / 3600);
		$min	= floor(($sec / 60) % 60);
		$sec	= $sec % 60;
		
		$hms = sprintf("%02d:%02d:%02d", $hour, $min, $sec);
		
		return $hms;
	}

	public static function getBrUrlText($text, $helper)
	{
		$text  = $helper->autoLink($text);
		$text  = nl2br($text);
		return $text;
	}

	public static function getDownloadLink($url, $name, $helper)
	{
		$link = '';
		
		if($url != null)
		{
			// 相対URLの場合、絶対URLに変更する
			if(mb_substr($url, 0, 1)=='/')
				$url = FULL_BASE_URL.$url;
			
			if($name=='')
				$name = '添付ファイル';
			
			$link = '<br>'.$helper->link($name, $url, array('target'=>'_blank', 'download' => $name));
		}
		
		return $link;
	}

	public static function getNoteLink($page_id, $helper)
	{
		$link = '';
		
		if(!$page_id)
			return '';
		
		$url = FULL_BASE_URL.'/notes/page/'.$page_id;
		
		$tag = 
			"<iframe id='irohanote-frame-%s' width='100%%' height='400' src='%s/%s'></iframe>";
		
		$tag = sprintf(
			$tag,
			$page_id,
			Router::url(array('controller' => 'notes', 'action' => 'page', 'admin' => false)),
			$page_id
		);
		
		return $tag;
	}

	public static function writeFormGroup($label, $value, $is_bold = false, $block_class = '')
	{
		$value = $is_bold ? '<h5>'.$value.'</h5>' : $value;
		echo '<div class="form-group '.$block_class.'">';
		echo '  <label for="UserRegistNo" class="col col-md-3 col-sm-4 control-label">'.$label.'</label>';
		echo '  <div class="col col-md-9 col-sm-8">'.$value.'</div>';
		echo '</div>';
	}
}

