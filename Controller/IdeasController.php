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
 * Ideas Controller
 *
 * @property Idea $Idea
 * @property PaginatorComponent $Paginator
 */
class IdeasController extends AppController
{

	/**
	 * Components
	 *
	 * @var array
	 */
	public $components = [
		'Paginator'
	];

	public function admin_index()
	{
		$this->index();
		$this->render('index');
	}

	/**
	 * index method
	 *
	 * @return void
	 */
	public function index($idea_id = null)
	{
		$is_add  = ($idea_id==null);
		
		$this->Idea->recursive = 0;

		$this->Paginator->settings = [
			'limit' => 10,
			'order' => 'Idea.created desc',
			'conditions' => [
				'user_id' => $this->readAuthUser('id')
			],
		];
		
		$ideas = $this->paginate();
		//debug($ideas);
		
		if($this->request->is(['post','put']))
		{
			if(Configure::read('demo_mode'))
				return;

			if($is_add)
				$this->request->data['Idea']['user_id'] = $this->readAuthUser('id');
			
			if(!$this->Idea->validates())
				return;
			
			if($this->Idea->save($this->request->data))
			{
				$this->Flash->success(__('アイデア・メモを追加しました'));
				$this->redirect(['action' => 'index']);
			}
			else
			{
				$this->Flash->error(__('The tasks idea could not be saved. Please, try again.'));
			}
		}
		else
		{
			$this->request->data = $this->Idea->findById($idea_id);
		}
		
		$this->set(compact('ideas', 'is_add'));
	}

	/**
	 * delete method
	 *
	 * @throws NotFoundException
	 * @param string $id        	
	 * @return void
	 */
	public function admin_delete($id)
	{
		$this->delete($id);
	}
	
	public function delete($id)
	{
		$this->Idea->id = $id;
		
		if (!$this->Idea->exists())
		{
			throw new NotFoundException(__('Invalid tasks idea'));
		}
		
		$this->request->allowMethod('post', 'delete');
		
		if ($this->Idea->delete())
		{
			$this->Flash->success(__('アイデアが削除されました'));
			return $this->redirect(['action' => 'index']);
		}
		else
		{
			$this->Flash->error(__('The tasks idea could not be deleted. Please, try again.'));
		}
	}
}
