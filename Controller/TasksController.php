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
 * Tasks Controller
 *
 * @property Task $Task
 * @property PaginatorComponent $Paginator
 */
class TasksController extends AppController
{

	public $components = [
		'Paginator',
		'Search.Prg',
		'Security' => [
			'validatePost' => false,
			'csrfUseOnce' => false,
			'unlockedActions' => ['admin_order', 'admin_preview', 'admin_upload_image'],
		],
	];

	public $paginate = [
		'limit' => 100,
		'order' => [
			'Post.title' => 'asc'
		]
	];

	public function index($theme_id, $user_id = null)
	{
		$theme_id	= intval($theme_id);
		$is_user	= $this->isIndexPage() && !$this->isAdminPage();
		$keyword	= $this->getQuery('keyword');
		$status		= $this->getQuery('status');
		
		// 学習テーマの情報を取得
		$theme = $this->fetchTable('Theme')->get($theme_id);
		
		// ユーザの場合、
		if($is_user)
		{
			// 学習テーマの閲覧権限の確認
			if(!$this->fetchTable('Theme')->hasRight($this->readAuthUser('id'), $theme_id))
			{
				throw new NotFoundException(__('Invalid access'));
			}
		}
		
		// 検索条件設定
		$this->Prg->commonProcess();
		
		$conditions = $this->Task->parseCriteria($this->Prg->parsedParams());
		
		$key = 'Iroha.search.task.status';
		
		if($this->hasQuery('status'))
		{
			// ステータスが新しく指定された場合、セッションに保存
			$this->writeSession($key, intval($status));
		}
		else
		{
			// 既にセッションにステータスが存在する場合、選択中のステータスに設定
			$status = $this->Session->check($key) ? $this->readSession($key) : 99;
		}
		
		$conditions['theme_id'] = $theme_id;
		
		if($status != '')
			$conditions['status'] = $status;
		
		if($keyword != '')
		{
			$conditions_progress = $conditions;
			
			$conditions_progress['OR'] = [
				'Progress.title like' => '%'.$keyword.'%',
				'Progress.body like' => '%'.$keyword.'%'
			];
			
			// キーワードを含む進捗を検索
			$progress_list = $this->fetchTable('Progress')->find()
				->select(['Task.id'])
				->where($conditions_progress)
				->all();
			
			// ヒットした課題のIDリストを作成
			$task_id_list = [];
			
			foreach ($progress_list as $item)
			{
				$task_id_list[] = $item['Task']['id'];
			}
			
			// キーワードを含むカードを検索
			$list = $this->fetchTable('Leaf')->getTaskIdByKeyword($keyword, $theme_id);
			
			$task_id_list = array_merge($task_id_list, $list);
			
			//debug($task_id_list);
			
			// キーワードを含む課題を検索
			$conditions['OR'] = [
				['Task.title like' => '%'.$keyword.'%'],
				['Task.body like' => '%'.$keyword.'%'],
				['Task.id' => $task_id_list],
			];
		}
		
		// 完了以外の場合
		if($status == "99")
		{
			$conditions['Task.status != '] = 3;
			unset($conditions['Task.status']);
			unset($conditions['status']);
			//debug($conditions);
		}
		
		$page_id = $theme['Theme']['page_id'];
		
		if($page_id)
		{
			$leaf_count = $this->fetchTable('Leaf')->find()->where(['page_id' => $page_id])->count();
			
			if($leaf_count == 0)
				$page_id = null;
		}

		$this->Paginator->settings = [
			'limit' => 20,
			'order' => 'Task.modified desc',
			'conditions' => $conditions,
		];
		
		$tasks = $this->paginate();
		
		$this->set(compact('theme', 'tasks', 'is_user', 'status', 'keyword', 'page_id'));
	}

	public function admin_index($id)
	{
		//$id = intval($id);
		$this->index($id);
		$this->render('index');
	}

	public function admin_add($theme_id)
	{
		$this->edit($theme_id);
		$this->render('edit');
	}

	/**
	 * 課題の削除
	 *
	 * @param int $task_id 削除する課題のID
	 */
	public function admin_delete($task_id)
	{
		$this->delete($task_id);
	}

	public function delete($task_id)
	{
		if(Configure::read('demo_mode'))
			return;
		
		$this->Task->id = $task_id;
		
		if(!$this->Task->exists())
		{
			throw new NotFoundException(__('Invalid task'));
		}
		
		$task = $this->Task->get($task_id);
		
		$this->request->allowMethod('post', 'delete');
		
		if($this->Task->delete())
		{
			$this->Flash->success(__('課題が削除されました'));
		}
		else
		{
			$this->Flash->error(__('The task could not be deleted. Please, try again.'));
		}
		
		return $this->redirect(['action' => 'index', $task['Theme']['id']]);
	}

	public function add($theme_id)
	{
		$this->edit($theme_id);
		$this->render('edit');
	}

	public function admin_edit($theme_id, $task_id = null, $from = null)
	{
		$this->edit($theme_id, $task_id, $from);
		$this->render('edit');
	}

	public function edit($theme_id, $task_id = null, $from = null)
	{
		$theme_id = intval($theme_id);
		$is_user  = !$this->isAdminPage();
		
		if($this->isEditPage() && !$this->Task->exists($task_id))
		{
			throw new NotFoundException(__('Invalid task'));
		}
		
		if($this->request->is(['post', 'put']))
		{
			if(Configure::read('demo_mode'))
				return;
			
			if(!$this->isEditPage())
			{
				$this->request->data['Task']['user_id'] = $this->readAuthUser('id');
				$this->request->data['Task']['theme_id'] = $theme_id;
			}
			
			if($this->Task->save($this->request->data))
			{
				// 学習履歴を追加
				$record_type = $this->isEditPage() ? 'task_update' : 'task_add';
				$id = ($task_id == null) ? $this->Task->getLastInsertID() : $task_id;
				/*
				$this->fetchTable('Record')->addRecord(
					$this->readAuthUser('id'),
					$theme_id,
					$id, // task_id
					$record_type, 
					$this->request->data['study_sec'] //study_sec
				);
				*/
				$this->fetchTable('Record')->addRecord([
					'user_id'		=> $this->readAuthUser('id'),
					'theme_id'		=> $theme_id,
					'task_id'		=> $id,
					'study_sec'		=> $this->request->data['study_sec'],
					'record_type'	=> $record_type,
				]);
				// 学習テーマの更新日時を更新
				$this->fetchTable('Theme')->id = $theme_id;
				$this->fetchTable('Theme')->saveField('modified', date(date('Y-m-d H:i:s')));
				
				$this->Flash->success(__('課題内容が保存されました'));
				
				if($from == 'progresses')
				{
					// 編集の場合、進捗一覧画面に遷移
					return $this->redirect(['controller' => 'progresses', 'action' => 'index', $task_id]);
				}
				else
				{
					// 追加の場合、課題一覧画面に遷移
					return $this->redirect(['action' => 'index', $theme_id]);
				}
			}
			else
			{
				$this->Flash->error(__('The task could not be saved. Please, try again.'));
			}
		}
		else
		{
			$this->request->data = $this->Task->get($task_id);
		}
		
		// コース情報を取得
		$theme = $this->fetchTable('Theme')->get($theme_id);
		
		$themes = $this->fetchTable('Theme')->find('list');
		$users = $this->fetchTable('User')->find('list');
		
		$status   = $this->isEditPage() ? $this->request->data['Task']['status']   : '1';
		$priority = $this->isEditPage() ? $this->request->data['Task']['priority'] : '2';
		$deadline = $this->isEditPage() ? $this->request->data['Task']['deadline'] : date("Y-m-d",strtotime("+1 week"));
		
		$this->set(compact('themes', 'users', 'theme', 'priority', 'status', 'is_user', 'deadline'));
	}

	/**
	 * ファイル（配布資料、動画）のアップロード
	 *
	 * @param int $file_type ファイルの種類
	 */
	public function admin_upload($file_type)
	{
		$this->upload($file_type);
		$this->render('upload');
	}

	public function upload($file_type)
	{
		//$this->layout = '';
		App::import ( "Vendor", "FileUpload" );

		$fileUpload = new FileUpload();

		$mode = '';
		$file_url = '';
		
		// ファイルの種類によって、アップロード可能な拡張子とファイルサイズを指定
		switch($file_type)
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
			default :
				throw new NotFoundException(__('Invalid access'));
		}
		
		// php.ini の upload_max_filesize, post_max_size の値を確認（互換性維持のためメソッドが存在する場合のみ）
		if(method_exists($fileUpload, 'getBytes'))
		{
		$upload_max_filesize = $fileUpload->getBytes(ini_get('upload_max_filesize'));
		$post_max_size		 = $fileUpload->getBytes(ini_get('post_max_size'));
		
		// upload_max_filesize が設定サイズより小さい場合、upload_max_filesize を優先する
		if($upload_max_filesize < $upload_maxsize)
			$upload_maxsize	= $upload_max_filesize;
		
		// post_max_size が設定サイズより小さい場合、post_max_size を優先する
		if($post_max_size < $upload_maxsize)
			$upload_maxsize	= $post_max_size;
		}
		
		$fileUpload->setExtension($upload_extensions);
		$fileUpload->setMaxSize($upload_maxsize);
		
		$original_file_name = '';
		
		if($this->request->is(['post', 'put']))
		{
			if(Configure::read('demo_mode'))
				return;
			
			// ファイルの読み込み
			$fileUpload->readFile( $this->getData('Task')['file'] );

			$error_code = 0;
			
			// エラーチェック（互換性維持のためメソッドが存在する場合のみ）
			if(method_exists($fileUpload, 'checkFile'))
				$error_code = $fileUpload->checkFile();
			
			if($error_code > 0)
			{
				$mode = 'error';
				
				switch($error_code)
				{
					case 1001 : // 拡張子エラー
					$this->Flash->error('アップロードされたファイルの形式は許可されていません');
						break;
					case 1002 : // ファイルサイズが0
					case 1003 : // ファイルサイズオバー
						$size = $this->getData('Task')['file']['size'];
						$this->Flash->error('アップロードされたファイルのサイズ（'.$size.'）は許可されていません');
						break;
					default :
						$this->Flash->error('アップロード中にエラーが発生しました ('.$error_code.')');
				}
			}
			else
			{
				$original_file_name = $this->getData('Task')['file']['name'];
				$str = substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 4);
				
				$user_id    = intval($this->readAuthUser('id'));
				$upload_dir = WWW_ROOT.DS."uploads".DS.$user_id;
				
				if(!file_exists($upload_dir))
					mkdir($upload_dir, 0755);
				
				// ファイル名：YYYYMMDDHHNNSS形式＋ランダムな4桁の文字列＋"既存の拡張子"
				$new_name = date('YmdHis').$str.$fileUpload->getExtension( $fileUpload->getFileName() );
				
				$file_name = $upload_dir.DS.$new_name;
				$file_url = $this->webroot.'uploads/'.$user_id.'/'.$new_name;

				$result = $fileUpload->saveFile( $file_name );										//	ファイルの保存

				if($result)																				//	結果によってメッセージを設定
				{
					//$this->Flash->success('ファイルのアップロードが完了いたしました');
					$mode = 'complete';
				}
				else
				{
					$this->Flash->error('ファイルのアップロードに失敗しました');
					$mode = 'error';
				}
			}
		}

		$file_name = $original_file_name;
		$upload_extensions = join(', ', $upload_extensions);
		
		$this->set(compact('mode', 'file_url', 'file_name', 'upload_extensions', 'upload_maxsize'));
	}
	
	/**
	 * リッチテキストエディタ(Summernote) からPOSTされた画像を保存
	 *
	 * @return string アップロードした画像のURL(JSON形式)
	 */
	public function admin_upload_image()
	{
		$this->autoRender = FALSE;
		
		if($this->request->is(['post', 'put']))
		{
			App::import ( "Vendor", "FileUpload" );
			$fileUpload = new FileUpload();
			
			$upload_extensions = (array)Configure::read('upload_image_extensions');
			$upload_maxsize = Configure::read('upload_image_maxsize');
			
			$fileUpload->setExtension($upload_extensions);
			$fileUpload->setMaxSize($upload_maxsize);
			$fileUpload->readFile( $this->getParam('form')['file'] );								//	ファイルの読み込み
			
			$str = substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 4);
			
			// ファイル名：YYYYMMDDHHNNSS形式＋ランダムな4桁の文字列＋"既存の拡張子"
			$new_name = date('YmdHis').$str.$fileUpload->getExtension( $fileUpload->getFileName() );
			
			$file_name = WWW_ROOT."uploads".DS.$new_name;											//	ファイルのパス
			$file_url = $this->webroot.'uploads/'.$new_name;										//	ファイルのURL

			$result = $fileUpload->saveFile( $file_name );											//	ファイルの保存
			
			//debug($result);
			$response = $result ? [$file_url] : [false];
			echo json_encode($response);
		}
	}
	
	public function admin_record($theme_id, $user_id)
	{
		$this->index($theme_id, $user_id);
		$this->render('index');
	}
}
