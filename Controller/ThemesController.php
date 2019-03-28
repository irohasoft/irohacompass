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

class ThemesController extends AppController
{

	/**
	 * Components
	 *
	 * @var array
	 */
	public $components = array(
		'Paginator',
		'Security' => array(
			'validatePost' => false,
			'unlockedActions' => array('admin_order')
		),
	);

	public $paginate = array(
		'order' => array(
			'Theme.modified' => 'desc'
		)
	);
	
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

	public function admin_edit($id = null)
	{
		$this->edit($id);
		$this->render('edit');
	}

	public function add()
	{
		$this->edit();
		$this->render('edit');
	}

	public function edit($id = null)
	{
		if ($this->action == 'edit' && ! $this->Theme->exists($id))
		{
			throw new NotFoundException(__('Invalid theme'));
		}
		
		// 追加フラグ
		$is_add  = (($this->action == 'admin_add')||($this->action == 'add'));
		// ユーザフラグ
		$is_user = (($this->action == 'add')||($this->action == 'edit'));
		
		if ($this->request->is(array(
				'post',
				'put'
		)))
		{
			if(Configure::read('demo_mode'))
				return;
			
			// 作成者を設定
			$this->request->data['Theme']['user_id'] = $this->Session->read('Auth.User.id');
			
			if ($this->Theme->save($this->request->data))
			{
				// 学習履歴を追加
				$record_type = $is_add ? 'theme_add' : 'theme_update';
				$id = ($id == null) ? $this->Theme->getLastInsertID() : $id;
				
				$this->loadModel('Record');
				$this->Record->addRecord(array(
					'user_id'		=> $this->Session->read('Auth.User.id'),
					'theme_id'		=> $id,
					'task_id'		=> 0,
					'study_sec'		=> $this->request->data['study_sec'],
					'record_type'	=> $record_type,
				));
				
				// 新規追加の場合、学習テーマとユーザの紐づけを追加
				if($is_add)
				{
					$this->Theme->addUserTheme($this->Session->read('Auth.User.id'), $this->Theme->getLastInsertID());
				}
				
				$this->Flash->success(__('学習テーマが保存されました'));
				
				// ユーザの場合、課題一覧へ遷移
				if($is_user)
				{
					return $this->redirect(array(
						'controller' => 'tasks',
						'action' => 'index',
						$id
					));
				}
				else
				{
					return $this->redirect(array(
						'action' => 'index'
					));
				}
			}
			else
			{
				$this->Flash->error(__('The theme could not be saved. Please, try again.'));
			}
		}
		else
		{
			$options = array(
				'conditions' => array(
					'Theme.' . $this->Theme->primaryKey => $id
				)
			);
			$this->request->data = $this->Theme->find('first', $options);
		}
		
		$this->set(compact('is_user'));
	}

	public function admin_delete($id = null)
	{
		if(Configure::read('demo_mode'))
			return;
		
		$this->Theme->id = $id;
		if (! $this->Theme->exists())
		{
			throw new NotFoundException(__('Invalid theme'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Theme->delete())
		{
			$this->Flash->success(__('学習テーマが削除されました'));
		}
		else
		{
			$this->Flash->error(__('The theme could not be deleted. Please, try again.'));
		}
		return $this->redirect(array(
				'action' => 'index'
		));
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
