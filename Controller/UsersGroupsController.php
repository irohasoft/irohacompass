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
 * UsersGroups Controller
 *
 * @property UsersGroup $UsersGroup
 * @property PaginatorComponent $Paginator
 */
class UsersGroupsController extends AppController
{

	/**
	 * Components
	 *
	 * @var array
	 */
	public $components = [
			'Paginator'
	];

	/**
	 * index method
	 *
	 * @return void
	 */
	public function index()
	{
		$this->UsersGroup->recursive = 0;
		$this->set('usersGroups', $this->Paginator->paginate());
	}
}
