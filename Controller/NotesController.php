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
	public $components = [
		'Paginator',
		'Security' => [
			'csrfUseOnce' => false,
			'unlockedActions' => ['leaf_control', 'link_control', 'webpage']
		],
		'Session',
		'RequestHandler',
	];

	/**
	 * index method
	 *
	 * @return void
	 */
	public function index()
	{
		$this->layout = '';
		$this->autoRender = FALSE;
		
		$notes = $this->Note->find()
			->where(['Note.user_id' => 'admin'])
			->all();
		
		$xmlArray = ['root' => ['note' => []]];
		
		$list = [];
		
		foreach ($notes as $note)
		{
			$list[count($list)] = $note['Note'];
		}
		
		$xmlArray['root']['note'] = $list;
		
		$xmlObject = Xml::fromArray($xmlArray, ['format' => 'tags']); // Xml::build() を使うこともできます
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
}
