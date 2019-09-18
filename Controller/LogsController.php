<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2018 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohacompass.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
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
	public $components = array('Paginator',
		'Auth' => array(
			'allowedActions' => array(
				'admin_add',
			)
		)
	);

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
				@$this->data['sec']
			);
			
			// 最終アクセス日時を保存
			if($this->Auth->user('id'))
			{
				$this->loadModel('User');
				$this->User->id = $this->Auth->user('id');
				$this->User->saveField('last_accessed', date(date('Y-m-d H:i:s')));
			}
			
			return "OK";
		}
	}
}
