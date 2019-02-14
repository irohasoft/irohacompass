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
 * Notes Controller
 *
 * @property Note $Note
 * @property PaginatorComponent $Paginator
 */
class NotesController extends AppController
{

	/**
	 * Components
	 *
	 * @var array
	 */
	public $components = array(
		'Paginator',
		'Security' => array(
			'csrfUseOnce' => false,
			'unlockedActions' => array('leaf_control', 'link_control', 'webpage')
		),
		'Session',
		'RequestHandler',
	);

	/**
	 * index method
	 *
	 * @return void
	 */
	public function index()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$options = array(
			'conditions' => array(
				'Note.user_id' => 'admin'
			)
		);
		
		$notes = $this->Note->find('all', $options);
		
		$xmlArray = array('root' => array('note' => array()));
		
		$list = array();
		
		foreach ($notes as $note)
		{
			$list[count($list)] = $note['Note'];
		}
		
		$xmlArray['root']['note'] = $list;
		
		$xmlObject = Xml::fromArray($xmlArray, array('format' => 'tags')); // Xml::build() を使うこともできます
		$xmlString = $xmlObject->asXML();
		
		$this->response->type('xml');

		header('Task-Type: text/xml');

		echo $xmlString;
	}

	/**
	 * index method
	 *
	 * @return void
	 */
	public function page($page_id, $mode = 'readonly')
	{
		$this->layout = '';
		$this->set(compact('page_id', 'mode'));
	}

	public function webpage()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		$page_title = '';
		
		$url = $this->data['url'];
		//$url = 'https://www.washingtontimes.com/';
		$str = @file_get_contents($url);
		
		// Webページからタイトルを取得
		if(strlen($str)>0){
			preg_match("/\<title\>(.*)\<\/title\>/", $str, $title);
			$page_title = $title[1];
		}

		$xmlArray = array('root' => array('result' => array(), 'title' => $page_title));
		$xmlArray['root']['result'] = array('error_code' => '0');
		
		$xmlObject = Xml::fromArray($xmlArray, array('format' => 'tags'));
		$xmlString = $xmlObject->asXML();
		$this->response->type('xml');

		header('Task-Type: text/xml');

		echo $xmlString;
	}

	public function leaf_list($page_id)
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$options = array(
			'conditions' => array(
				'Leaf.page_id' => $page_id
			)
		);
		
		$this->loadModel('Leaf');
		$leafs = $this->Leaf->find('all', $options);
		
		$xmlArray = array('root' => array());
		
		$list = array();
		
		foreach ($leafs as $leaf)
		{
			$list[count($list)] = $leaf['Leaf'];
		}
		
		if(count($list) > 0)
			$xmlArray['root']['leaf'] = $list;
		
		$xmlObject = Xml::fromArray($xmlArray, array('format' => 'tags'));
		$xmlString = $xmlObject->asXML();
		
		$this->response->type('xml');

		header('Task-Type: text/xml');

		echo $xmlString;
	}

	public function leaf_control()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		//debug($this->data);
		$cmd = $this->data['cmd'];
		
		$this->loadModel('Leaf');
		$data = $this->Leaf->find('first', array(
			'conditions' => array(
				'Leaf.leaf_id' => $this->data['leaf_id']
			)
		));
		
		switch($cmd)
		{
			case 'add':
				$data['Leaf']['page_id']		= $this->data['page_id'];
				$data['Leaf']['leaf_id']		= $this->data['leaf_id'];
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
				$data['Leaf']['user_id']		= $this->Session->read('Auth.User.id');
				break;
			case 'update':																	//	削除の場合
				$data['Leaf']['leaf_title']		= $this->data['leaf_title'];
				$data['Leaf']['leaf_content']	= $this->data['leaf_content'];
				break;
			case 'move':																	//	移動の場合
				$data['Leaf']['leaf_top']		= $this->data['leaf_top'];
				$data['Leaf']['leaf_left']		= $this->data['leaf_left'];
				$data['Leaf']['leaf_zorder']	= time();
				break;
			case 'size':																	//	サイズ変更の場合
				$data['Leaf']['leaf_width']		= $this->data['leaf_width'];
				$data['Leaf']['leaf_height']	= $this->data['leaf_height'];
				$data['Leaf']['leaf_zorder']	= time();
			case 'zorder':																	//	サイズ変更の場合
				$data['Leaf']['leaf_zorder']	= time();
				break;
			case 'color':																	//	折り畳みの場合
				$data['Leaf']['leaf_color']		= $this->data['leaf_color'];
			case 'fold':																	//	折り畳みの場合
				$data['Leaf']['leaf_fold']		= $this->data['fold'];
				break;
		}
		
		if($cmd=='delete')
		{
			$this->Leaf->id = $data['Leaf']['id'];
			$this->Leaf->delete();
		}
		else
		{
			/*
			debug($data);
			debug($this->Leaf->save($data));
			*/
			$this->Leaf->save($data);
		}
		
		$xmlArray = array('root' => array('result' => array()));
		$xmlArray['root']['result'] = array('error_code' => '0');
		
		$xmlObject = Xml::fromArray($xmlArray, array('format' => 'tags'));
		$xmlString = $xmlObject->asXML();
		
		$this->response->type('xml');

		header('Task-Type: text/xml');
		echo $xmlString;
	}

	public function link_list($page_id)
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$options = array(
			'conditions' => array(
				'Link.page_id' => $page_id
			)
		);
		
		$this->loadModel('Link');
		$links = $this->Link->find('all', $options);
		
		$xmlArray = array('root' => array('link' => array()));
		
		$list = array();
		
		foreach ($links as $link)
		{
			$list[count($list)] = $link['Link'];
		}
		
		$xmlArray['root']['link'] = $list;
		
		$xmlObject = Xml::fromArray($xmlArray, array('format' => 'tags'));
		$xmlString = $xmlObject->asXML();
		
		$this->response->type('xml');

		header('Task-Type: text/xml');

		echo $xmlString;
	}

	public function link_control()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		//debug($this->data);
		$cmd = $this->data['cmd'];
		
		$this->loadModel('Link');
		switch($cmd)
		{
			case 'add':
				$data['Link']['link_id']		= $this->data['link_id'];
				$data['Link']['leaf_id']		= $this->data['leaf_id'];
				$data['Link']['leaf_id2']		= $this->data['leaf_id2'];
				$data['Link']['page_id']		= $this->data['page_id'];
				$data['Link']['user_id']		= $this->Session->read('Auth.User.id');
				$this->Link->save($data);
				/*
				debug($data);
				debug($this->Link->save($data));
				*/
				break;
			case 'delete':
				$this->Link->deleteAll(array('Link.link_id' => $this->data['link_id']));
				$this->Link->delete();
				break;
			case 'delete_by_leaf_id':
				$this->Link->deleteAll(array('Link.leaf_id' => $this->data['leaf_id']));
				$this->Link->deleteAll(array('Link.leaf_id2' => $this->data['leaf_id']));
				break;
		}
		
		$xmlArray = array('root' => array('result' => array()));
		$xmlArray['root']['result'] = array('error_code' => '0');
		
		$xmlObject = Xml::fromArray($xmlArray, array('format' => 'tags'));
		$xmlString = $xmlObject->asXML();
		
		$this->response->type('xml');

		header('Task-Type: text/xml');
		echo $xmlString;
	}

}
