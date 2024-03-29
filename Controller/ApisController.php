<?php
/**
 * iroha Note Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses('AppController', 'Controller');
App::uses('Xml', 'Utility');

/**
 * Apis Controller
 *
 * @property Api $Api
 * @property PaginatorComponent $Paginator
 */
class ApisController extends AppController
{
	public $uses = null;

	/**
	 * Components
	 *
	 * @var array
	 */
	public $components = [
		'Paginator',
		'Security' => [
			'csrfUseOnce' => false,
			'unlockedActions' => ['note_control', 'page_control', 'leaf_control', 'link_control', 'webpage', 'note_export']
		],
	];

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow();
	}

	/**
	 * ノート一覧
	 */
	public function note_list()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$user_id   = $this->getUserID();
		
		// ログインチェック
		if($user_id == '')
		{
			$this->flashError(101);
			return;
		}
		
		// ログインユーザがアクセス可能なノートIDリスト
		$note_id_list  = $this->getNoteIDList($user_id);
		
		$notes = $this->fetchTable('Note')->find()
			->where(['Note.id' => $note_id_list])
			->order(['Note.note_order'])
			->all();
		
		$xmlArray = ['root' => ['result' => []]];
		$xmlArray['root']['result'] = ['error_code' => '0'];
		
		$list = $this->getList($notes, 'Note');
		
		if($list)
			$xmlArray['root']['note'] = $list;
		// XML出力
		$this->flashXML($xmlArray);
	}

	/**
	 * ノート操作
	 */
	public function note_control()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$user_id = $this->getUserID();
		$note_id = $this->_getParamData('note_id');
		$note_id = $this->_getParamData('note_id');
		
		// ログインチェック
		if($user_id == '')
		{
			$this->flashError(101);
			return;
		}
		
		//debug($this->data);
		$cmd = $this->data['cmd'];
		
		$conditions = [
			'Note.note_id' => $note_id,
			'Note.user_id' => $this->getUserIDList($note_id) // ノートにアクセス可能なユーザIDリスト
		];
		
		$data = $this->fetchTable('Note')->find()
			->where($conditions)
			->first();
		
		if(
			($cmd == 'add')||
			($cmd == 'update')
		)
		{
			if(mb_strlen($this->data['note_title']) > 20)
			{
				$this->flashError(102, 'ノートのタイトルは20文字以下で入力してください。');
				return;
			}
		}
		
		switch($cmd)
		{
			case 'add':
				//$this->flashError(102, '上限に達しているノートを作成できません');
				//return;
				$data['Note']['note_id']		= $this->data['note_id'];
				$data['Note']['note_title']		= $this->data['note_title'];
				$data['Note']['note_order']		= time();
				$data['Note']['user_id']		= $user_id;
				$this->fetchTable('Note')->save($data);
				
				$data['Page']['note_id']		= $this->data['note_id'];
				$data['Page']['page_id']		= time();
				$data['Page']['page_title']		= '(non title)';
				$data['Page']['page_fold']		= 0;
				$data['Page']['page_order']		= time();
				$data['Page']['user_id']		= $user_id;
				$this->fetchTable('Page')->save($data);
				break;
			case 'update':
				$data['Note']['note_title']		= $this->data['note_title'];
				$this->fetchTable('Note')->save($data);
				break;
			case 'color':
				$data['Note']['note_color']		= $this->data['note_color'];
				$this->fetchTable('Note')->save($data);
				break;
			case 'order':
				$this->fetchTable('Note')->updateOrder($user_id, $this->data);
				break;
			case 'delete':
				$this->fetchTable('Note')->deleteNote($user_id, $this->data['note_id']);
				break;
			case 'import':
				$note		= json_decode(str_replace ('\"','"', $this->data["note"]), true);
				$page_list	= json_decode(str_replace ('\"','"', $this->data["page_list"]), true);
				$leaf_list	= json_decode(str_replace ('\"','"', stripslashes($this->data["leaf_list"])), true);
				$link_list	= json_decode(str_replace ('\"','"', $this->data["link_list"]), true);
				
				$base_id = rand(10000, 99999);
				$note_id = $this->getRenewedID($base_id, $note['note_id']);
				
				// ノートの保存
				$data['Note']['note_id']		= $note_id;
				$data['Note']['note_title']		= $note['note_title'];
				$data['Note']['note_order']		= time();
				$data['Note']['user_id']		= $user_id;
				//debug($data);
				$this->fetchTable('Note')->save($data);
				
				// ページの保存
				foreach($page_list as $page)
				{
					$page_id	= $this->getRenewedID($base_id, $page['page_id']);
					$parent_id	= null;
					
					if(@$page['parent_id'])
						$parent_id = $this->getRenewedID($base_id, $page['parent_id']);
					
					$data = [];
					$data['Page'] = $page;
					
					$data['Page']['id']				= null;
					$data['Page']['note_id']		= $note_id;
					$data['Page']['page_id']		= $page_id;
					$data['Page']['parent_id']		= $parent_id;
					$data['Page']['leaf_zorder']	= time();
					$data['Page']['user_id']		= $user_id;
		
					//debug($data);
					$this->fetchTable('Page')->save($data);
				}
		
				// リーフの保存
				foreach($leaf_list as $leaf)
				{
					$page_id	= $this->getRenewedID($base_id, $leaf['page_id']);
					$leaf_id	= $this->getRenewedID($base_id, $leaf['leaf_id']);
					
					$data = [];
					$data['Leaf'] = $leaf;
					
					$data['Leaf']['id']				= null;
					$data['Leaf']['note_id']		= $note_id;
					$data['Leaf']['page_id']		= $page_id;
					$data['Leaf']['leaf_id']		= $leaf_id;
					$data['Leaf']['leaf_zorder']	= time();
					$data['Leaf']['user_id']		= $user_id;
					//debug($data);
					
					$this->fetchTable('Leaf')->save($data);
				}
		
				// リンクの保存
				foreach($link_list as $link)
				{
					$page_id	= $this->getRenewedID($base_id, $link['page_id']);
					$link_id	= $this->getRenewedID($base_id, $link['link_id']);
					$leaf_id	= $this->getRenewedID($base_id, $link['leaf_id']);
					$leaf_id2	= $this->getRenewedID($base_id, $link['leaf_id2']);
					
					$data = [];
					$data['Link'] = $link;
					
					$data['Link']['id']				= null;
					$data['Link']['note_id']		= $note_id;
					$data['Link']['page_id']		= $page_id;
					$data['Link']['link_id']		= $link_id;
					$data['Link']['leaf_id']		= $leaf_id;
					$data['Link']['leaf_id2']		= $leaf_id2;
					$data['Link']['user_id']		= $user_id;
					//debug($data);
		
					$this->fetchTable('Link')->save($data);
				}
		
				break;
		}

		$xmlArray = ['root' => ['result' => []]];
		$xmlArray['root']['result'] = ['error_code' => '0'];

		// XML出力
		$this->flashXML($xmlArray);
	}

	/**
	 * ページ一覧
	 */
	public function page_list()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$user_id = $this->getUserID();
		
		// ログインチェック
		if($user_id == '')
		{
			$this->flashError(101);
			return;
		}
		
		$note_id = $this->_getParamData('note_id');
		
		$conditions = [
			'Note.note_id' => $note_id,
			'Note.user_id' => $this->getUserIDList($note_id) // ノートにアクセス可能なユーザIDリスト
		];
		
		// 管理権限の場合、ユーザIDは任意とする
		if($this->isAdminRole())
			unset($conditions['Note.user_id']);
		
		//debug($options);
		
		$note = $this->fetchTable('Note')->find()
			->where($conditions)
			->first();
		
		$conditions = [
			'Page.note_id' => $note_id,
			'Page.user_id' => $this->getUserIDList($note_id) // ノートにアクセス可能なユーザIDリスト
		];
		
		// 管理権限の場合、ユーザIDは任意とする
		if($this->isAdminRole())
			unset($conditions['Page.user_id']);
		
		$pages = $this->fetchTable('Page')->find()
			->where($conditions)
			->order(['Page.page_order'])
			->all();
		
		$xmlArray = ['root' => ['note' => []]];
		
		$xmlArray['root']['note'] = $note['Note'];
		
		$list = $this->getList($pages, 'Page');
		
		if($list)
		$xmlArray['root']['page'] = $list;
		
		// XML出力
		$this->flashXML($xmlArray);
	}
	
	/**
	 * リーフ操作
	 */
	public function page_control()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$user_id = $this->getUserID();
		$note_id = $this->_getParamData('note_id');
		$page_id = $this->_getParamData('page_id');
		$cmd = $this->_getParamData('cmd');

		// ログインチェック
		if($user_id == '')
		{
			$this->flashError(101);
			return;
		}
		
		$conditions = [
			'Page.page_id' => $page_id,
			'Page.user_id' => $this->getUserIDList($note_id) // ノートにアクセス可能なユーザIDリスト
		];
		
		$data = $this->fetchTable('Page')->find()
			->where($conditions)
			->first();
		
		switch($cmd)
		{
			case 'add':
				$data['Page']['note_id']		= $this->data['note_id'];
				$data['Page']['page_id']		= $this->data['page_id'];
				$data['Page']['page_title']		= $this->data['page_title'];
				$data['Page']['page_fold']		= 0;
				$data['Page']['page_order']		= time();
				$data['Page']['user_id']		= $user_id;
				$this->fetchTable('Page')->save($data);
				break;
			case 'update':
				$data['Page']['page_title']		= $this->data['page_title'];
				$this->fetchTable('Page')->save($data);
				break;
			case 'order':
				$this->fetchTable('Page')->updateOrder($user_id, $this->data);
				break;
			case 'delete':
				$this->fetchTable('Page')->deletePage($user_id, $this->data['page_id']);
				break;
		}
		
		$page_id = $this->fetchTable('Page')->getLastInsertID();
		
		$xmlArray = ['root' => ['result' => [], 'page_id' => $page_id, 'page_title' => @$this->data['page_title']]];
		$xmlArray['root']['result'] = ['error_code' => '0'];

		// XML出力
		$this->flashXML($xmlArray);
	}
	
	/**
	 * リーフ一覧
	 */
	public function leaf_list()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$user_id = $this->getUserID();
		
		// ログインチェック
		if($user_id == '')
		{
			$this->flashError(101);
			return;
		}
		
		$page_id = $this->_getParamData('page_id');
		$note_id = $this->_getParamData('note_id');
		
		//debug($note_id);
		//debug($this->getUserIDList($note_id));
		
		$conditions = [
			'Leaf.page_id' => $page_id,
			'Leaf.user_id' => $this->getUserIDList($note_id) // ノートにアクセス可能なユーザIDリスト
		];
		
		// 管理権限の場合、ユーザIDは任意とする
		if($this->isAdminRole())
			unset($conditions['Leaf.user_id']);
		
		$leafs = $this->fetchTable('Leaf')->find()
			->where($conditions)
			->order(['Leaf.leaf_zorder asc'])
			->all();
		
		$xmlArray = ['root' => []];
		
		$list = $this->getList($leafs, 'Leaf');
		
		if($list)
			$xmlArray['root']['leaf'] = $list;
		
		// XML出力
		$this->flashXML($xmlArray);
	}

	/**
	 * リーフ操作
	 */
	public function leaf_control()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$user_id = $this->getUserID();
		$leaf_id = $this->_getParamData('note_id');
		
		// ログインチェック
		if($user_id == '')
		{
			$this->flashError(101);
			return;
		}
		
		//debug($this->data);
		$cmd = $this->data['cmd'];
		
		$leaf_id = $this->data['leaf_id'];
		
		$data = $this->fetchTable('Leaf')->find()
			->where(['Leaf.leaf_id' => $leaf_id])
			->first();
		
		switch($cmd)
		{
			case 'add':
				$data['Leaf']['note_id']		= $this->data['note_id'];
				$data['Leaf']['page_id']		= $this->data['page_id'];
				$data['Leaf']['leaf_id']		= $leaf_id;
				$data['Leaf']['leaf_title']		= $this->data['leaf_title'];
				$data['Leaf']['leaf_content']	= $this->data['leaf_content'];
				$data['Leaf']['leaf_top']		= $this->data['leaf_top'];
				$data['Leaf']['leaf_width']		= $this->data['leaf_width'];
				$data['Leaf']['leaf_height']	= $this->data['leaf_height'];
				$data['Leaf']['leaf_color']		= $this->data['leaf_color'];
				$data['Leaf']['leaf_left']		= $this->data['leaf_left'];
				$data['Leaf']['leaf_kind']		= $this->data['leaf_kind'];
				$data['Leaf']['leaf_fold']		= 0;
				$data['Leaf']['leaf_zorder']	= time();
				$data['Leaf']['user_id']		= $user_id;
				$this->fetchTable('Leaf')->save($data);
				break;
			case 'update':
				$data['Leaf']['leaf_title']		= $this->data['leaf_title'];
				$data['Leaf']['leaf_content']	= $this->data['leaf_content'];
				$this->fetchTable('Leaf')->save($data);
				break;
			case 'move':
				$data['Leaf']['leaf_top']		= $this->data['leaf_top'];
				$data['Leaf']['leaf_left']		= $this->data['leaf_left'];
				$data['Leaf']['leaf_zorder']	= time();
				$this->fetchTable('Leaf')->save($data);
				break;
			case 'size':
				$data['Leaf']['leaf_width']		= $this->data['leaf_width'];
				$data['Leaf']['leaf_height']	= $this->data['leaf_height'];
				$data['Leaf']['leaf_zorder']	= time();
				$this->fetchTable('Leaf')->save($data);
			case 'zorder':
				$data['Leaf']['leaf_zorder']	= time();
				$this->fetchTable('Leaf')->save($data);
				break;
			case 'color':
				$data['Leaf']['leaf_color']		= $this->data['leaf_color'];
				$this->fetchTable('Leaf')->save($data);
			case 'fold':
				$data['Leaf']['leaf_fold']		= $this->data['fold'];
				$this->fetchTable('Leaf')->save($data);
				break;
			case 'change_page':
				$data['Leaf']['page_id']		= $this->data['page_id'];
			$this->fetchTable('Leaf')->save($data);
				break;
			case 'delete':
				$this->fetchTable('Leaf')->deleteLeaf($user_id, $leaf_id);
				break;
		}
		
		$xmlArray = ['root' => ['result' => []]];
		$xmlArray['root']['result'] = ['error_code' => '0'];
		
		// XML出力
		$this->flashXML($xmlArray);
	}

	/**
	 * リンク一覧
	 */
	public function link_list()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$page_id = $this->_getParamData('page_id');
		
		$links = $this->fetchTable('Link')->find()
			->where(['Link.page_id' => $page_id])
			->all();
		
		$xmlArray = ['root' => ['link' => []]];
		
		$list = $this->getList($links, 'Link');
		
		if($list)
		$xmlArray['root']['link'] = $list;
		
		// XML出力
		$this->flashXML($xmlArray);
	}

	/**
	 * リンク操作
	 */
	public function link_control()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$user_id = $this->getUserID();
		
		// ログインチェック
		if($user_id == '')
		{
			$this->flashError(101);
			return;
		}
		//debug($this->data);
		$cmd = $this->data['cmd'];
		
		switch($cmd)
		{
			case 'add':
				$data['Link']['link_id']		= $this->data['link_id'];
				$data['Link']['leaf_id']		= $this->data['leaf_id'];
				$data['Link']['leaf_id2']		= $this->data['leaf_id2'];
				$data['Link']['page_id']		= $this->data['page_id'];
				$data['Link']['note_id']		= $this->data['note_id'];
				$data['Link']['user_id']		= $user_id;
				$this->fetchTable('Link')->save($data);
				break;
			case 'delete':
				$this->fetchTable('Link')->deleteAll(['Link.link_id' => $this->data['link_id']]);
				$this->fetchTable('Link')->delete();
				break;
			case 'delete_by_leaf_id':
				$this->fetchTable('Link')->deleteAll(['Link.leaf_id' => $this->data['leaf_id']]);
				$this->fetchTable('Link')->deleteAll(['Link.leaf_id2' => $this->data['leaf_id']]);
				break;
		}
		
		$xmlArray = ['root' => ['result' => []]];
		$xmlArray['root']['result'] = ['error_code' => '0'];
		
		// XML出力
		$this->flashXML($xmlArray);
	}
	
	/**
	 * 検索結果一覧
	 */
	public function search_list()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$user_id = $this->getUserID();
		
		//$user_id = 'admin';
		
		// ログインチェック
		if($user_id == '')
		{
			$this->flashError(101);
			return;
		}
		$keyword = $this->_getParamData('keyword');
		$note_id = $this->_getParamData('note_id');
		
		// ログインユーザがアクセス可能なノートIDリスト
		$note_id_list  = $this->getNoteIDList($user_id);
		
		$conditions = [
			'OR' => [
				['Leaf.leaf_title like' 	=> '%'.$keyword.'%'],
				['Leaf.leaf_content like'=> '%'.$keyword.'%'],
			],
			'Note.id' => $note_id_list
		];
		
		// ノートが指定されている場合、ノート内を検索
		if($note_id!='')
			$conditions['Leaf.note_id'] = $note_id;
		
		$leafs = $this->fetchTable('Leaf')->find()
			->where($conditions)
			->all();
		
		$xmlArray = ['root' => []];
		
		$xmlArray['root']['result'] = ['error_code' => '0'];
		
		
		// リストに変換
		$list = [];
		
		foreach($leafs as $item)
		{
			$new_item = $item['Leaf'];
			
			
			$new_item['note_id']	= $item['Note']['note_id'];
			$new_item['note_title']	= $item['Note']['note_title'];
			$new_item['page_id']	= $item['Page']['page_id'];
			$new_item['page_title']	= $item['Page']['page_title'];

			$list[count($list)] = $new_item;
		}
		
		if($list)
		$xmlArray['root']['leaf'] = $list;
		
		// XML出力
		$this->flashXML($xmlArray);
	}
	
	/**
	 * 検索結果一覧
	 */
	public function note_export()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$user_id = $this->getUserID();
		//$user_id = 'admin';
		
		// ログインチェック
		if($user_id == '')
		{
			$this->flashError(101);
			return;
		}

		$note_id = $this->_getParamData('note_id');

		$conditions = [
			'Note.note_id' => $note_id,
			'Note.user_id' => $user_id
		];
		
		$note = $this->fetchTable('Note')->find()
			->where($conditions)
			->first();
		
		$conditions = [
			'Page.note_id' => $note_id,
			'Page.user_id' => $user_id
		];
		
		$pages = $this->fetchTable('Page')->find()
			->where($conditions)
			->all();
		
		$conditions = [
			'Leaf.note_id' => $note_id,
			'Leaf.user_id' => $user_id
		];
		
		//debug($options);
		$leafs = $this->fetchTable('Leaf')->find()
			->where($conditions)
			->all();
		
		$conditions = [
			'Link.note_id' => $note_id,
			'Link.user_id' => $user_id
		];
		
		$links = $this->fetchTable('Link')->find()
			->where($conditions)
			->all();
		
		// リストに変換
		$page_list = $this->getList($pages, 'Page');
		$leaf_list = $this->getList($leafs, 'Leaf');
		$link_list = $this->getList($links, 'Link');
		
		$xmlArray = ['root' => []];
		
		$xmlArray['root']['result'] = ['error_code' => '0'];
		$xmlArray['root']['note'] = $note['Note'];
		
		if($page_list)
			$xmlArray['root']['page'] = $page_list;
		
		if($leaf_list)
			$xmlArray['root']['leaf'] = $leaf_list;

		if($link_list)
			$xmlArray['root']['link'] = $link_list;

		// XML出力
		$this->flashXML($xmlArray);
	}

	/**
	 * ノート一覧
	 */
	public function plan()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$user_id = $this->getUserID();
		
		// ログインチェック
		if($user_id == '')
		{
			$this->flashError(101);
			return;
		}
		
		$customer_id = $this->fetchTable('UsersPlan')->getCustomerID($user_id);
		$sub = $this->fetchTable('UsersPlan')->getActiveSub($customer_id);
		
		$xmlArray = ['root' => ['result' => []]];
		$xmlArray['root']['result'] = ['error_code' => '0'];
		
		$xmlArray['root']['sub'] = $sub;
		$xmlArray['root']['plan'] = $sub['plan'];
		
		/*
		debug($sub->plan);
		exit;
		*/
		// XML出力
		$this->flashXML($xmlArray);
	}
	
	/**
	 * Webページのタイトルの取得
	 */
	public function webpage()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		$page_title = '';
		
		$url = $this->data['url'];
		//$url = 'https://www.washingtontimes.com/';
		$page_title = $this->getPageTitle($url);

		$xmlArray = ['root' => ['result' => [], 'title' => $page_title]];
		$xmlArray['root']['result'] = ['error_code' => '0'];
		
		// XML出力
		$this->flashXML($xmlArray);
	}
	
	/**
	 * Webページのタイトルの取得
	 */
	private function getPageTitle( $url )
	{
		$agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; NP06; rv:11.0) like Gecko';
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_HEADER, false );
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLINFO_REDIRECT_COUNT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_POSTREDIR, 3);

		$html =  curl_exec($ch);
		curl_close($ch);
		
		if(preg_match( "/<title>(.*?)<\/title>/i", $html, $matches))
		{
			return $matches[1];
		}
		else
		{
			return 'none';
		}
	}
	
	/**
	 * パラメータを取得
	 */
	private function _getParamData($key)
	{
		$val = '';
		
		if($this->hasQuery($key))
			$val = $this->params->query[$key];
		
		if($this->getData($key))
			$val = $this->data[$key];
		
		return $val;
	}
	
	private function getList($arr, $name)
	{
		$list = [];
		
		foreach($arr as $item)
		{
			$list[count($list)] = $item[$name];
		}
		
		return $list;
	}
	
	/**
	 * エラー出力
	 */
	private function flashError($error_code, $error_message = null)
	{
		$xmlArray = ['root' => []];
		$xmlArray['root']['result'] = ['error_code' => $error_code, 'error_message' => $error_message];
		$this->flashXML($xmlArray);
	}
	
	/**
	 * XML出力
	 */
	private function flashXML($xmlArray)
	{
		$xmlObject = Xml::fromArray($xmlArray, ['format' => 'tags']);
		$xmlString = $xmlObject->asXML();
		
		$this->response->type('xml');

		header('Task-Type: text/xml');

		echo $xmlString;
	}
	
	/**
	 * セッションもしくはBearerからユーザIDを取得
	 */
	private function getUserID()
	{
		$user_id = '';
		
		if((Configure::read('api_auth_type')) == 'wordpress')
		{
			$user = wp_get_current_user();
			$user_id = $user->get('user_login');
			
			// Bearerからアクセストークンを取得
			$access_token = $this->getAccessTokenFromBearer();
			
			// アクセストークンからユーザIDを取得
			if($access_token!='')
			{
				$user_id = $this->fetchTable('User')->getUserIdByAccessToken($access_token);
			}
		}
		else
		{
			$user_id = $this->readAuthUser('id');
		}
		
		return $user_id;
	}

	/**
	 * ノートにアクセス可能なユーザIDリスト
	 */
	private function getUserIDList($note_id)
	{
		$user_id_list  = $this->fetchTable('Note')->getUserIDList($note_id);
		
		return $user_id_list;
	}

	/**
	 * ログインユーザがアクセス可能なノートIDリスト
	 */
	private function getNoteIDList($user_id)
	{
		$note_id_list  = $this->fetchTable('Note')->getNoteIDList($user_id);
		
		return $note_id_list;
	}

	//--------------------------------------//
	//	Bearerからアクセストークンを取得	//
	//	iroha Note 2 用						//
	//--------------------------------------//
	private function getAccessTokenFromBearer()
	{
		$access_token = '';
		
		foreach(getallheaders() as $name => $value)
		{
			if($name == "Authorization")
				$access_token = str_replace("Bearer ", '', $value);
		}
		
		return $access_token;
	}

	//--------------------------------------//
	// ベースIDと既存IDを元に新しいIDを生成	//
	// 前半5桁・・・ベースとなるID			//
	// 後半5桁・・・既存のID				//
	//--------------------------------------//
	private function getRenewedID($base_id, $original_id)
	{
		$element_id = substr('00000'.$original_id, -5);
		
		$new_id = $base_id.$element_id;
		
		return $new_id;
	}
}
