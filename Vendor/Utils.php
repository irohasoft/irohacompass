<?php
/**
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 */

// ユーティリティクラス
class Utils
{
	/**
	 * 対象日時から時間を切り取って返す
	 * 
	 * @param string $str 対象日時（YYYY-MM-DD HH:MM:SS）
	 * @return string 日付 YYYY-MM-DD
	 */
	public static function getYMD($str)
	{
		return substr($str, 0, 10);
	}

	/**
	 * 対象日時から秒を切り取って返す
	 * 
	 * @param string $str 対象日時（YYYY-MM-DD HH:MM:SS）
	 * @return string 日付 YYYY-MM-DD HH:MM
	 */
	public static function getYMDHN($str)
	{
		return substr($str, 0, 16);
	}
	
	/**
	 * 秒を時間に変換
	 * 
	 * @param int $sec 秒
	 * @return int 時間 HH:MM:SS
	 */
	public static function getHNSBySec($sec)
	{
		$hour	= floor($sec / 3600);
		$min	= floor(($sec / 60) % 60);
		$sec	= $sec % 60;
		
		$hms = sprintf("%02d:%02d:%02d", $hour, $min, $sec);
		
		return $hms;
	}
	
	/**
	 * CSVデータを取得
	 * 
	 * @param string $file_path CSVファイルのパス
	 * @return array CSVデータ（2次元配列）
	 */
	public static function getCsvData($file_path)
	{
		setlocale(LC_ALL, 'ja_JP.UTF-8');
		
		$data = file_get_contents($file_path);
		$data = mb_convert_encoding($data, 'UTF-8', 'SJIS-Win');
		$temp = tmpfile();
		$meta = stream_get_meta_data($temp);
		
		fwrite($temp, $data);
		rewind($temp);
		
		$file = new SplFileObject($meta['uri']);
		$file->setFlags(SplFileObject::READ_CSV);
		
		$csv  = [];
		 
		foreach($file as $line)
		{
			$csv[] = $line;
		}
		
		fclose($temp);
		$file = null;
		
		return $csv;
	}
	/*
	public static function isAllowed($user, $roles)
	{
		return (array_search($user['role'], $roles) > -1);
	}
	*/
	
	/**
	 * 設定項目の値に対応するキーを取得
	 * 
	 * @param string $configure 設定の項目名
	 * @return string 値
	 */
	public static function getKeyByValue($configure, $value)
	{
		$list = Configure::read($configure);
		
		foreach ($list as $key => $val)
		{
			if($value==$val)
				return $key;
		}
		
		return null;
	}
	
	/**
	 * 連想配列内の値に対応するキーを取得
	 * 
	 * @param array $list 対象配列
	 * @param array $title 値
	 * @return string キー
	 */
	public static function getIdByTitle($list, $title)
	{
		foreach ($list as $key => $value)
		{
			if($value==$title)
				return $key;
		}
		
		return null;
	}
	
	/**
	 * 指定した桁数のパスワードを生成
	 * 
	 * @param int $digit 桁数
	 * @return string 生成したパスワード
	 */
	public static function getNewPassword($digit)
	{
		return substr(str_shuffle('23456789abcdefghijkmnopqrstuvwxyz'), 0, $digit);
	}
	
	/**
	 * 未定義エラー回避用
	 * 
	 * @param string $check 対象の文字列
	 * @param string $alternate 未定義の場合に返す文字列
	 * @return string 生成したパスワード
	 */
	function issetOr(&$check, $alternate = null) 
	{
		return (isset($check)) ? $check : $alternate;
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
			
			$link = '<br>'.$helper->link($name, $url, ['target'=>'_blank', 'download' => $name]);
		}
		
		return $link;
	}

	public static function getNoteLink($page_id, $helper)
	{
		$link = '';
		
		if(!$page_id)
			return '';
		
		if(!Configure::read('use_irohanote'))
			return '';
		
		$url = FULL_BASE_URL.'/notes/page/'.$page_id;
		
		$tag = 
			"<iframe id='irohanote-frame-%s' width='100%%' height='400' src='%s/%s'></iframe>";
		
		$tag = sprintf(
			$tag,
			$page_id,
			Router::url(['controller' => 'notes', 'action' => 'page', 'admin' => false]),
			$page_id
		);
		
		return $tag;
	}

	public static function writeFormGroup($label, $value, $is_bold = false, $block_class = '')
	{
		$value = $is_bold ? '<h5>'.$value.'</h5>' : $value;
		echo '<div class="form-group '.$block_class.'">';
		echo '  <label for="UserRegistNo" class="col col-sm-3 control-label">'.$label.'</label>';
		echo '  <div class="col col-sm-9">'.$value.'</div>';
		echo '</div>';
	}
}

