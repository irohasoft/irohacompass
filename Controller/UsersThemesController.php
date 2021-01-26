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
	public $components = [
		'Paginator'
	];

	public function index()
	{
		// 全体のお知らせの取得
		$this->loadModel('Setting');
		$data = $this->Setting->findAllBySettingKey('information');
		
		$info = $data[0]['Setting']['setting_value'];
		
		// お知らせ一覧を取得
		$this->loadModel('Info');
		$infos = $this->Info->getInfos($this->Auth->user('id'), 2);
		
		$no_info = '';
		
		// 全体のお知らせもお知らせも存在しない場合
		if(($info=='') && count($infos)==0)
			$no_info = "お知らせはありません";
		
		// 受講学習テーマ情報の取得
		$themes = $this->UsersTheme->getThemeRecord( $this->Auth->user('id') );
		
		$no_record = '';
		
		if(count($themes)==0)
			$no_record = "選択可能な学習テーマはありません";
		

		// 最近の学習履歴一覧を取得
		$this->loadModel('Record');
		
		$theme_ids = [];
		
		foreach ($themes as $theme)
		{
			array_push($theme_ids, $theme['Theme']['id']);
		}
		
		$conditions['Theme.id'] = $theme_ids;
		
		$options = [
			'conditions' => $conditions,
			'order' => 'Record.created desc',
			'limit' => 5,
		];
		
		$records = $this->Record->find('all', $options);
		
		//debug($records);
		
		// 進捗チャート用の情報を取得
		$user_id		= $this->Auth->user('id');
		$labels			= $this->Record->getDateLabels();
		$login_data		= $this->Record->getLoginData($user_id, $labels);
		$progress_data	= $this->Record->getProgressData($user_id, $labels);
		
		$this->set(compact('themes', 'no_record', 'info', 'infos', 'no_info', 'records', 'labels', 'login_data', 'progress_data'));
	}
}
