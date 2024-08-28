<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses('AppController', 'Controller');

/**
 * UsersThemes Controller
 *
 * @property UsersTheme $UsersTheme
 * @property PaginatorComponent $Paginator
 */
class UsersThemesController extends AppController
{
	/**
	 * 学習テーマ一覧（ホーム画面）を表示
	 */
	public function index()
	{
		$user_id = $this->readAuthUser('id');
		
		// 全体のお知らせの取得
		$data = $this->Setting->find()
			->where(['Setting.setting_key' => 'information'])
			->first();
		
		$info = $data['Setting']['setting_value'];
		
		// お知らせ一覧を取得
		$infos = $this->fetchTable('Info')->getInfos($user_id, 2);
		
		$no_info = '';
		
		// 全体のお知らせもお知らせも存在しない場合
		if(($info == '') && (count($infos) == 0))
			$no_info = __('お知らせはありません');
		
		// 受講学習テーマ情報の取得
		$themes = $this->UsersTheme->getThemeRecord($user_id);
		
		$no_record = '';
		
		if(count($themes) == 0)
			$no_record = "選択可能な学習テーマはありません";
		

		// 最近の学習履歴一覧を取得
		$theme_ids = [];
		
		foreach ($themes as $theme)
		{
			array_push($theme_ids, $theme['Theme']['id']);
		}
		
		$where = ['Theme.id' => $theme_ids];
		
		// 自分が所有しているテーマの履歴のみを表示
		if(Configure::read('show_my_record'))
			$where = ['Theme.id' => $theme_ids, 'Theme.user_id' => $user_id];
		
		$records = $this->fetchTable('Record')->find()
			->where($where)
			->order(['Record.created desc'])
			->limit(5)
			->all();
		
		// 進捗チャート用の情報を取得
		$labels			= $this->fetchTable('Record')->getDateLabels();
		$login_data		= $this->fetchTable('Record')->getLoginData($user_id, $labels);
		$progress_data	= $this->fetchTable('Record')->getProgressData($user_id, $labels);
		
		// アップロードファイル参照用
		$this->writeCookie('LoginStatus', 'logined');
		
		$this->set(compact('themes', 'no_record', 'info', 'infos', 'no_info', 'records', 'labels', 'login_data', 'progress_data'));
	}
}
