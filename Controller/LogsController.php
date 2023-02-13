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
 * Logs Controller
 *
 * @property Log $Log
 * @property PaginatorComponent $Paginator
 */
class LogsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = ['Paginator',
		'Auth' => [
			'allowedActions' => [
				'admin_add',
			]
		]
	];

	public function add()
	{
		$this->admin_add();
	}

	public function admin_add()
	{
		$this->autoRender = FALSE;
		if($this->request->is('ajax'))
		{
			$this->writeLog(
				$this->data['log_type'],
				$this->data['log_content'],
				$this->data['controller'],
				$this->data['action'],
				$this->data['params'],
				isset($this->data['sec']) ? $this->data['sec'] : null
			);
			
			// 最終アクセス日時を保存
			if($this->readAuthUser('id'))
			{
				$this->fetchTable('User')->id = $this->readAuthUser('id');
				$this->fetchTable('User')->saveField('last_accessed', date(date('Y-m-d H:i:s')));
			}
			
			return "OK";
		}
	}
}
