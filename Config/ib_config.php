<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

$config['group_status']		= ['1' => '公開', '0' => '非公開'];
$config['theme_status']		= ['1' => '有効', '0' => '無効'];

$config['progress_type']			= [
	'progress'	=> '進捗',
	'comment'	=> 'コメント',
	'idea'		=> 'アイデア・メモ',
	'question'	=> '質問',
	'answer'	=> '回答',
];

$config['content_type']	= [
	'text'		=> 'テキスト',
	'markdown'	=> 'Markdown',
	'irohanote'	=> 'アイデアマップ',
];

$config['progress_type_enq']	= [
	'single'	=> '選択形式',
	'text'		=> '記述式'
];

$config['lang']	= [
	'jp'		=> '日本語',
	'en'		=> '英語'
];

$config['record_type'] = [
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
];

$config['user_role'] = ['admin' => '管理者', 'user' => '学習者'];

$config['task_priority'] = ['1' => '高', '2' => '中', '3' => '低'];
$config['task_status'] = ['1' => '未対応', '2' => '実施中', '3' => '完了'];

$config['upload_extensions'] = [
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
];

$config['upload_image_extensions'] = [
	'.png',
	'.gif',
	'.jpg',
	'.jpeg',
];

$config['upload_movie_extensions'] = [
	'.mov',
	'.mp4',
	'.wmv',
	'.asx',
];

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

// iroha Note を使用しない場合、選択肢からも削除
if(!$config['use_irohanote'])
{
	unset($config['content_type']['irohanote']);
}

// 感情アイコンを使用する
$config['use_emotion_icon']	= false;

// スマイル機能を使用する
$config['use_smile']		= false;

// デモモード (true ; 設定する, false : 設定しない)
//$config['demo_mode'] = true;
$config['demo_mode'] = false;

// デモユーザのログインIDとパスワード
$config['demo_login_id']	= "demo001";
$config['demo_password']	= "pass";
$config['demo_target_date']	= "2018/12/17";

// フォームのスタイル(BoostCake)の基本設定
$config['form_defaults'] = [
	'inputDefaults' => [
		'div' => 'form-group',
		'label' => [
			'class' => 'col col-sm-3 control-label'
		],
		'wrapInput' => 'col col-sm-9',
		'class' => 'form-control'
	],
	'class' => 'form-horizontal'
];

$config['form_submit_defaults'] = [
	'div' => false,
	'class' => 'btn btn-primary',
	'data-localize' => 'save'
];

$config['form_submit_before'] = 
	 '<div class="form-group">'
	.'  <div class="col col-sm-9 col-sm-offset-3">';

$config['form_submit_after'] = 
	 '  </div>'
	.'</div>';

$config['theme_colors'] = [
	'#337ab7' => 'default',
	'#003f8e' => 'ink blue',
	'#4169e1' => 'royal blue',
	'#006888' => 'marine blue',
	'#00bfff' => 'deep sky blue',
	'#483d8b' => 'dark slate blue',
	'#00a960' => 'green',
	'#006948' => 'holly green',
	'#288c66' => 'forest green',
	'#556b2f' => 'dark olive green',
	'#8b0000' => 'dark red',
	'#d84450' => 'poppy red',
	'#c71585' => 'medium violet red',
	'#a52a2a' => 'brown',
	'#ee7800' => 'orange',
	'#fcc800' => 'chrome yellow',
	'#7d7d7d' => 'gray',
	'#696969' => 'dim gray',
	'#2f4f4f' => 'dark slate gray',
	'#000000' => 'black'
];

$config['emotion_icons']   = [
	'positive-2' => 'positive-2.png',
	'positive-1' => 'positive-1.png',
	'normal'     => 'normal.png',
	'negative'   => 'negative.png',
];

// メール関連の設定
$config['mail_title']	= '[iroha Compass] 進捗の更新';
$config['admin_from']	= ['sendmail@irohasoft.com' => 'iroha Compass'];


