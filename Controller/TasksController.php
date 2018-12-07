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
App::uses('Theme', 'Theme');
App::uses('Record', 'Record');

/**
 * Tasks Controller
 *
 * @property Task $Task
 * @property PaginatorComponent $Paginator
 */
class TasksController extends AppController
{

	public $components = array(
		'Paginator',
		'Search.Prg',
		'Security' => array(
			'validatePost' => false,
			'csrfUseOnce' => false,
			'unlockedActions' => array('admin_order', 'admin_preview', 'admin_upload_image'),
		),
	);

	public $paginate = array(
		'limit' => 100,
		'order' => array(
			'Post.title' => 'asc'
		)
	);

	public function index($theme_id, $user_id = null)
	{
		$theme_id = intval($theme_id);
		$is_user = ($this->action == 'index');
		
		// 学習テーマの情報を取得
		$this->loadModel('Theme');
		$theme = $this->Theme->find('first', array(
			'conditions' => array(
				'Theme.id' => $theme_id
			)
		));
		
		// ユーザの場合、
		if($is_user)
		{
			// 学習テーマの閲覧権限の確認
			if(! $this->Theme->hasRight($this->Session->read('Auth.User.id'), $theme_id))
			{
				throw new NotFoundException(__('Invalid access'));
			}
		}
		
		// 検索条件設定
		$this->Prg->commonProcess();
		
		$conditions = $this->Task->parseCriteria($this->Prg->parsedParams());
		
		$key = 'Iroha.search.task.status';
		
		if(isset($this->request->query['status']))
		{
			// ステータスが新しく指定された場合、セッションに保存
			$status = $this->request->query['status'];
			$this->Session->write($key, intval($this->request->query['status']));
		}
		else
		{
			// 既にセッションにステータスが存在する場合、選択中のステータスに設定
			$status = $this->Session->check($key) ? $this->Session->read($key) : 99;
		}
		
		$conditions['theme_id'] = $theme_id;
		
		if($status != '')
			$conditions['status'] = $status;
		
		// 完了以外の場合
		if($status == "99")
		{
			$conditions['Task.status != '] = 3;
			unset($conditions['Task.status']);
			unset($conditions['status']);
			//debug($conditions);
		}
		

		$this->Paginator->settings = array(
			'limit' => 20,
			'order' => 'Task.modified desc',
			'conditions' => $conditions,
		);
		
		$tasks = $this->paginate();
		
		$this->set(compact('theme', 'tasks', 'is_user', 'status'));
	}

	public function view($id = null)
	{
		$id = intval($id);
		
		if (! $this->Task->exists($id))
		{
			throw new NotFoundException(__('Invalid task'));
		}

		$this->layout = "";

		$options = array(
			'conditions' => array(
				'Task.' . $this->Task->primaryKey => $id
			)
		);
		
		$task = $this->Task->find('first', $options);
		
		// コンテンツの閲覧権限の確認
		$this->loadModel('Theme');
		
		if(! $this->Theme->hasRight($this->Session->read('Auth.User.id'), $task['Task']['theme_id']))
		{
			throw new NotFoundException(__('Invalid access'));
		}
		
		$this->set(compact('task'));
	}

	public function admin_preview()
	{
		$this->autoRender = FALSE;
		if($this->request->is('ajax'))
		{
			$data = array(
				'Task' => array(
					'id'     => 0,
					'title'  => $this->data['task_title'],
					'kind'   => $this->data['task_kind'],
					'url'    => $this->data['task_url'],
					'body'  => $this->data['task_body']
				)
			);
			
			$this->Session->write("Iroha.preview_task", $data);
		}
	}

	public function preview()
	{
		$this->layout = "";
		$this->set('task', $this->Session->read('Iroha.preview_task'));
		$this->render('view');
	}

	public function delete($id)
	{
		if(Configure::read('demo_mode'))
			return;
		
		$this->Task->id = $id;
		
		if (! $this->Task->exists())
		{
			throw new NotFoundException(__('Invalid task'));
		}
		
		// コンテンツ情報を取得
		$this->loadModel('Task');
		$task = $this->Task->find('first', array(
			'conditions' => array(
				'Task.id' => $id
			)
		));
		
		$this->request->allowMethod('post', 'delete');
		
		if ($this->Task->delete())
		{
			$this->Flash->success(__('コンテンツが削除されました'));
		}
		else
		{
			$this->Flash->error(__('The task could not be deleted. Please, try again.'));
		}
		
		return $this->redirect(array(
			'action' => 'index/' . $task['Theme']['id']
		));
	}

	public function admin_delete_enq($id)
	{
		if(Configure::read('demo_mode'))
			return;
		
		$this->Task->id = $id;
		
		if (! $this->Task->exists())
		{
			throw new NotFoundException(__('Invalid task'));
		}
		
		// コンテンツ情報を取得
		$this->loadModel('Task');
		$task = $this->Task->find('first', array(
			'conditions' => array(
				'Task.id' => $id
			)
		));
		
		$this->request->allowMethod('post', 'delete');
		
		if ($this->Task->delete())
		{
			$this->Flash->success(__('コンテンツが削除されました'));
			return $this->redirect(array(
				'action' => 'index_enq'
			));
		}
		else
		{
			$this->Flash->error(__('The task could not be deleted. Please, try again.'));
		}
	}

	public function admin_index($id)
	{
		//$id = intval($id);
		$this->index($id);
		$this->render('index');
	}

	public function admin_index_enq()
	{
		$this->Paginator->settings = array(
			'limit' => 20,
			'order' => 'Task.modified desc',
			'conditions' => array('theme_id' => 0),
		);
		
		$tasks = $this->paginate();
		
		$this->set(compact('tasks'));
	}

	public function admin_add($theme_id)
	{
		$this->edit($theme_id);
		$this->render('edit');
	}

	public function admin_delete($id)
	{
		$this->delete($id);
	}
	
	public function add($theme_id)
	{
		$this->edit($theme_id);
		$this->render('edit');
	}

	public function admin_edit($theme_id, $task_id = null)
	{
		$this->edit($theme_id, $task_id);
		$this->render('edit');
	}

	public function edit($theme_id, $task_id = null)
	{
		$theme_id = intval($theme_id);
		$is_add  = (($this->action == 'admin_add')||($this->action == 'add'));
		$is_user = (($this->action == 'add')||($this->action == 'edit'));
		
		if ($this->action == 'edit' && ! $this->Task->exists($task_id))
		{
			throw new NotFoundException(__('Invalid task'));
		}
		if ($this->request->is(array(
				'post',
				'put'
		)))
		{
			if(Configure::read('demo_mode'))
				return;
			
			if($is_add)
			{
				$this->request->data['Task']['user_id'] = $this->Session->read('Auth.User.id');
				$this->request->data['Task']['theme_id'] = $theme_id;
			}
			
			if ($this->Task->save($this->request->data))
			{
				// 学習履歴を追加
				$record_type = $is_add ? 'task_add' : 'task_update';
				$id = ($task_id == null) ? $this->Task->getLastInsertID() : $task_id;
				$this->loadModel('Record');
				$this->Record->addRecord(
					$this->Session->read('Auth.User.id'),
					$theme_id,
					$id, // task_id
					$record_type, 
					$this->request->data['study_sec'] //study_sec
				);

				// 学習テーマの更新日時を更新
				$this->Task->Theme->id = $theme_id;
				$this->Task->Theme->saveField('modified', date(DATE_ATOM));
				
				$this->Flash->success(__('課題内容が保存されました'));
				
				return $this->redirect( array(
					'action' => 'index/' . $theme_id
				));
			}
			else
			{
				$this->Flash->error(__('The task could not be saved. Please, try again.'));
			}
		}
		else
		{
			$options = array(
				'conditions' => array(
					'Task.' . $this->Task->primaryKey => $task_id
				)
			);
			$this->request->data = $this->Task->find('first', $options);
		}
		
		// コース情報を取得
		$theme = $this->Task->Theme->find('first', array(
			'conditions' => array(
				'Theme.id' => $theme_id
			)
		));
		
		$themes = $this->Task->Theme->find('list');
		$users = $this->Task->User->find('list');
		
		$status = $is_add ? '1' : $this->request->data['Task']['status'];
		$priority = $is_add ? '2' : $this->request->data['Task']['priority'];
		$deadline = $is_add ? date("Y-m-d",strtotime("+1 week")) : $this->request->data['Task']['deadline'];
		
		$this->set(compact('groups', 'themes', 'users', 'theme', 'priority', 'status', 'is_user', 'deadline'));
	}

	public function admin_upload($file_type)
	{
		$this->upload($file_type);
		$this->render('upload');
	}

	public function upload($file_type)
	{
		//$this->layout = "";
		App::import ( "Vendor", "FileUpload" );

		$fileUpload = new FileUpload();

		$mode = '';
		$file_url = '';
		
		switch ($file_type)
		{
			case 'file' :
				$upload_extensions = (array)Configure::read('upload_extensions');
				$upload_maxsize = Configure::read('upload_maxsize');
				break;
			case 'image' :
				$upload_extensions = (array)Configure::read('upload_image_extensions');
				$upload_maxsize = Configure::read('upload_image_maxsize');
				break;
			case 'movie' :
				$upload_extensions = (array)Configure::read('upload_movie_extensions');
				$upload_maxsize = Configure::read('upload_movie_maxsize');
				break;
		}
		
		$fileUpload->setExtension($upload_extensions);
		$fileUpload->setMaxSize($upload_maxsize);
		
		$original_file_name = '';
		
		if ($this->request->is('post') || $this->request->is('put'))
		{
			if(Configure::read('demo_mode'))
				return;
			
			$fileUpload->readFile( $this->request->data['Task']['file'] );						//	ファイルの読み込み
			
			$original_file_name = $this->request->data['Task']['file']['name'];
			
			$new_name = date("YmdHis").$fileUpload->getExtension( $fileUpload->get_file_name() );	//	ファイル名：YYYYMMDDHHNNSS形式＋"既存の拡張子"
			
			$user_id    = intval($this->Session->read('Auth.User.id'));
			$upload_dir = WWW_ROOT.DS."uploads".DS.$user_id;
			
			if(!file_exists($upload_dir))
				mkdir($upload_dir, 0755);

			$file_path = $upload_dir.DS.$new_name;													//	ファイル格納フォルダ
			$file_url = $this->webroot.'uploads/'.$user_id.'/'.$new_name;

			$result = $fileUpload->saveFile( $file_path );											//	ファイルの保存

			if($result)																				//	結果によってメッセージを設定
			{
				$this->Flash->success('ファイルのアップロードが完了いたしました');
				$mode = 'complete';

				//$url = G_ROOT_URL."/../uploads/".$new_name;										//	アップロードしたファイルのURL
			}
			else
			{
				$this->Flash->error('ファイルのアップロードに失敗しました');
				$mode = 'error';
			}
		}

		$this->set('mode',					$mode);
		$this->set('file_url',				$file_url);
		$this->set('file_name',				$original_file_name);
		$this->set('upload_extensions',		join(', ', $upload_extensions));
		$this->set('upload_maxsize',		$upload_maxsize);
	}
	
	public function admin_upload_image()
	{
		$this->autoRender = FALSE;
		
		if ($this->request->is('post') || $this->request->is('put'))
		{
			App::import ( "Vendor", "FileUpload" );
			$fileUpload = new FileUpload();
			
			$upload_extensions = (array)Configure::read('upload_image_extensions');
			$upload_maxsize = Configure::read('upload_image_maxsize');
			
			$fileUpload->setExtension($upload_extensions);
			$fileUpload->setMaxSize($upload_maxsize);
			//debug($this->request->params['form']['file']);
			
			$fileUpload->readFile( $this->request->params['form']['file'] );						//	ファイルの読み込み
			
			$new_name = date("YmdHis").$fileUpload->getExtension( $fileUpload->get_file_name() );	//	ファイル名：YYYYMMDDHHNNSS形式＋"既存の拡張子"

			$user_id    = intval($this->Session->read('Auth.User.id'));
			$upload_dir = WWW_ROOT.DS."uploads".DS.$user_id;
			
			if(!file_exists($upload_dir))
				mkdir($upload_dir, 0755);
			
			$file_path = $upload_dir.DS.$new_name;													//	ファイルのパス
			$file_url = $this->webroot.'uploads/'.$user_id.'/'.$new_name;

			$result = $fileUpload->saveFile( $file_path );											//	ファイルの保存
			
			//debug($result);
			
			$response = ($result) ? array($file_url) : array(false);
			echo json_encode($response);
		}
	}
	
	public function admin_order()
	{
		$this->autoRender = FALSE;
		if($this->request->is('ajax'))
		{
			$this->Task->setOrder($this->data['id_list']);
			return "OK";
		}
	}

	public function admin_record($theme_id, $user_id)
	{
		$this->index($theme_id, $user_id);
		$this->render('index');
	}

	public function admin_add_enq()
	{
		$this->admin_edit_enq();
		$this->render('admin_edit_enq');
	}

	public function admin_edit_enq($task_id = null)
	{
		if ($this->action == 'admin_edit' && ! $this->Task->exists($id))
		{
			throw new NotFoundException(__('Invalid user'));
		}
		
		if ($this->request->is(array(
				'post',
				'put'
		)))
		{
			if ($this->Task->save($this->request->data))
			{
				$this->Flash->success(__('アンケートが保存されました'));

				return $this->redirect(array(
						'action' => 'index_enq'
				));
			}
			else
			{
				$this->Flash->error(__('The user could not be saved. Please, try again.'));
			}
		}
		else
		{
			$options = array(
				'conditions' => array(
					'Task.' . $this->Task->primaryKey => $task_id
				)
			);
			$this->request->data = $this->Task->find('first', $options);
		}
	}
}
