<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2018 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohacompass.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

class DATABASE_CONFIG {

	public $default = array(
		'datasource' => 'Database/Mysql',
		'persistent' => true,
		//'host' => 'localhost',
		//'host' => 'localhost:3307',
		'login' => 'root',
		'password' => '',
		//'database' => 'ic_test',
		'database' => 'irohacompass',
		//'password' => 'irohairoha1',
		'prefix' => 'ib_',
		'encoding' => 'utf8'
	);
}
