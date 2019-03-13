<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2018 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohacompass.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

$config['group_status']		= array('1' => '公開', '0' => '非公開');
$config['theme_status']		= array('1' => '有効', '0' => '無効');

$config['progress_type']			= array(
	'progress'	=> '進捗',
	'comment'	=> 'コメント',
	'idea'		=> 'アイデア・メモ',
	'question'	=> '質問',
	'answer'	=> '回答',
);

$config['content_type']	= array(
	'text'		=> 'テキスト',
	'markdown'	=> 'Markdown',
	'irohanote'	=> 'iroha Note',
);

$config['progress_type_enq']	= array(
	'single'	=> '選択形式',
	'text'		=> '記述式'
);

$config['record_type'] = array(
	'0'					=> '',
	'theme_add'			=> '学習テーマ追加',
	'theme_update'		=> '学習テーマ更新',
	'task_add'			=> '課題追加',
	'task_update'		=> '課題更新',
	'progress'			=> '進捗',
	'comment'			=> 'コメント',
	'idea'				=> 'アイデア・メモ',
	'question'			=> '質問',
	'answer'			=> '回答',
	'progress_update'	=> '進捗更新',
	'comment_update'	=> 'コメント更新',
	'idea_update'		=> 'アイデア・メモ更新',
	'question_update'	=> '質問更新',
	'answer_update'		=> '回答更新',
);

$config['user_role'] = array('admin' => '管理者', 'user' => '学習者');

$config['task_priority'] = array('1' => '高', '2' => '中', '3' => '低');
$config['task_status'] = array('1' => '未対応', '2' => '実施中', '3' => '完了');

$config['upload_extensions'] = array(
	'.png',
	'.gif',
	'.jpg',
	'.jpeg',
	'.pdf',
	'.zip',
	'.ppt',
	'.pptx',
	'.doc',
	'.docx',
	'.xls',
	'.xlsx',
	'.txt',
	'.mov',
	'.mp4',
	'.wmv',
	'.asx',
	'.mp3',
	'.wma',
	'.m4a',
);

$config['upload_image_extensions'] = array(
	'.png',
	'.gif',
	'.jpg',
	'.jpeg',
);

$config['upload_movie_extensions'] = array(
	'.mov',
	'.mp4',
	'.wmv',
	'.asx',
);

// アップロードサイズの上限（別途 php.ini で upload_max_filesize を設定する必要があります）
$config['upload_maxsize']		= 1024 * 1024 * 10;
$config['upload_image_maxsize'] = 1024 * 1024 *  2;
$config['upload_movie_maxsize'] = 1024 * 1024 * 10;

// select2 項目選択時の自動クローズの設定 (true ; 自動的にメニューを閉じる, false : 閉じない)
$config['close_on_select'] = true;

// リッチテキストエディタの画像アップロード機能の設定 (true ; 使用する, false : 使用しない)
$config['use_upload_image'] = true;

// iroha Note を使用する
$config['use_irohanote']	= true;

// 感情アイコンを使用する
$config['use_emotion_icon']	= true;

// デモモード (true ; 設定する, false : 設定しない)
//$config['demo_mode'] = true;
$config['demo_mode'] = false;

// デモユーザのログインIDとパスワード
$config['demo_login_id'] = "demo001";
$config['demo_password'] = "pass";
$config['demo_target_date'] = "2018/12/17";

// フォームのスタイル(BoostCake)の基本設定
$config['form_defaults'] = array(
	'inputDefaults' => array(
		'div' => 'form-group',
		'label' => array(
			'class' => 'col col-sm-3 control-label'
		),
		'wrapInput' => 'col col-sm-9',
		'class' => 'form-control'
	),
	'class' => 'form-horizontal'
);

$config['form_submit_defaults'] = array(
	'div' => false,
	'class' => 'btn btn-primary'
);


$config['theme_colors']   = array(
	'#337ab7' => 'default',
	'#006888' => 'marine blue',
	'#003f8e' => 'ink blue',
	'#00a960' => 'green',
	'#288c66' => 'forest green',
	'#006948' => 'holly green',
	'#ea5550' => 'red',
	'#ea5550' => 'poppy red',
	'#ee7800' => 'orange',
	'#fcc800' => 'chrome yellow',
	'black' => 'black',
	'#7d7d7d' => 'gray'
);

$config['emotion_icons']   = array(
	'positive-2' => 'positive-2.png',
	'positive-1' => 'positive-1.png',
	'normal'     => 'normal.png',
	'negative'   => 'negative.png',
);

// メール関連の設定
$config['mail_title']	= '[iroha Compass] 進捗の更新';
$config['admin_from']	= array('sendmail@irohasoft.com' => 'iroha Compass');


