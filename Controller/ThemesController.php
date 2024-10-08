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

class ThemesController extends AppController
{
	/**
	 * Components
	 *
	 * @var array
	 */
	public $components = [
		'Paginator',
		'Security' => [
			'validatePost' => false,
			'unlockedActions' => ['admin_order']
		],
	];

	public $paginate = [
		'order' => [
			'Theme.modified' => 'desc'
		]
	];
	
	public function index()
	{
		$this->Theme->recursive = 0;
		$this->set('themes', $this->Paginator->paginate());
	}

	public function admin_index()
	{
		$this->Paginator->settings['order'] = 'Theme.modified desc';
		$this->set('themes', $this->Paginator->paginate());
	}

	public function admin_add()
	{
		$this->edit();
		$this->render('edit');
	}

	public function admin_edit($theme_id = null)
	{
		$this->edit($theme_id);
		$this->render('edit');
	}

	public function add()
	{
		$this->edit();
		$this->render('edit');
	}

	public function edit($theme_id = null)
	{
		if($this->isEditPage() && !$this->Theme->exists($theme_id))
		{
			throw new NotFoundException(__('Invalid theme'));
		}
		
		// 追加フラグ
		$is_add  = !$this->isEditPage();
		// ユーザフラグ
		$is_user = !$this->isAdminPage();
		
		if($this->request->is(['post', 'put']))
		{
			if(Configure::read('demo_mode'))
				return;
			
			// 所有者が指定されていない場合のみ、ログインユーザを所有者に設定
			if(!$this->request->data['Theme']['user_id'])
				$this->request->data['Theme']['user_id'] = $this->readAuthUser('id');
			
			if($this->Theme->save($this->request->data))
			{
				// 学習履歴を追加
				$record_type = $is_add ? 'theme_add' : 'theme_update';
				$theme_id = ($theme_id == null) ? $this->Theme->getLastInsertID() : $theme_id;
				
				$this->fetchTable('Record')->addRecord([
					'user_id'		=> $this->readAuthUser('id'),
					'theme_id'		=> $theme_id,
					'task_id'		=> 0,
					'study_sec'		=> $this->request->data['study_sec'],
					'record_type'	=> $record_type,
				]);
				
				// 新規追加の場合、学習テーマとユーザの紐づけを追加
				if($is_add)
				{
					$this->Theme->addUserTheme($this->readAuthUser('id'), $this->Theme->getLastInsertID());
				}
				
				$this->Flash->success(__('学習テーマが保存されました'));
				
				// ユーザの場合、課題一覧へ遷移
				if($is_user)
				{
					return $this->redirect(['controller' => 'tasks', 'action' => 'index', $theme_id]);
				}
				else
				{
					return $this->redirect(['action' => 'index']);
				}
			}
			else
			{
				$this->Flash->error(__('The theme could not be saved. Please, try again.'));
			}
		}
		else
		{
			$this->request->data = $this->Theme->get($theme_id);
		}
		
		$users = $this->Theme->User->find('list');
		$this->set(compact('is_user', 'users', 'theme_id'));
	}

	public function admin_delete($theme_id = null)
	{
		if(Configure::read('demo_mode'))
			return;
		
		$this->Theme->id = $theme_id;
		
		if(!$this->Theme->exists())
		{
			throw new NotFoundException(__('Invalid theme'));
		}
		
		$this->request->allowMethod('post', 'delete');
		
		if($this->Theme->delete())
		{
			$this->Flash->success(__('学習テーマが削除されました'));
		}
		else
		{
			$this->Flash->error(__('The theme could not be deleted. Please, try again.'));
		}
		
		return $this->redirect(['action' => 'index']);
	}

	public function admin_order()
	{
		$this->autoRender = FALSE;
		
		if($this->request->is('ajax'))
		{
			$this->Theme->setOrder($this->data['id_list']);
			return "OK";
		}
	}
}
