<?php
/**
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 */

App::uses('AppController', 'Controller');

class UpdateController extends AppController
{
	var $name = 'Update';
	var $uses = [];
	var $helpers = ['Html'];
	var $err_msg = '';
	var $db   = null;
	var $path = '';
	
	public $components = [
		'Session',
		'Auth' => [
			'allowedActions' => [
				'index',
				'error',
			]
		]
	];
	
	/**
	 * アップデート
	 */
	public function index($mode = null)
	{
		try
		{
			App::import('Model','ConnectionManager');

			// テストモードの場合、テスト用のデータベースを参照
			if($mode == 'test')
			{
				$this->db   = ConnectionManager::getDataSource('test');
			}
			else
			{
			$this->db   = ConnectionManager::getDataSource('default');
			}

			// パッケージアップデート用クエリ
			$this->path = APP.'Config'.DS.'Schema'.DS.'update.sql';
			$err_update = $this->__executeSQLScript();
			
			// カスタマイズ用クエリ
			$this->path = APP.'Custom'.DS.'Config'.DS.'custom.sql';
			
			// カスタマイズ用クエリが存在する場合
			if(file_exists($this->path))
			{
			$err_custom = $this->__executeSQLScript();
			$err_statements = array_merge($err_update, $err_custom);
			}
			else
			{
				$err_statements = $err_update;
			}
			
			if(count($err_statements) > 0)
			{
				$this->err_msg = 'クエリの実行中にエラーが発生しました。詳細はエラーログ(tmp/logs/error.log)をご確認ください。';
				
				foreach($err_statements as $err)
				{
					$err .= $err."\n";
				}
				
				//debug($err);
				// デバッグログ
				$this->log($err);
				$this->error();
				$this->render('error');
				return;
			}
		}
		catch(Exception $e)
		{
			$this->err_msg = 'データベースへの接続に失敗しました。<br>Config / database.php ファイル内のデータベースの設定を確認して下さい。';
			$this->error();
			$this->render('error');
		}
	}
	
	/**
	 * アップデートエラーメッセージを表示
	 */
	public function error()
	{
		$this->set('loginedUser', $this->readAuthUser());
		$this->set('body', $this->err_msg);
	}
	
	/**
	 * update.sql のクエリを実行
	 */
	private function __executeSQLScript()
	{
		$statements = file_get_contents($this->path);
		$statements = explode(';', $statements);
		$err_statements = [];
		
		foreach($statements as $statement)
		{
			if(trim($statement) != '')
			{
				try
				{
					$this->db->query($statement);
				}
				catch(Exception $e)
				{
					// レコード重複追加エラー
					if($e->errorInfo[0] == '23000')
						continue;
					
					// カラム重複追加エラー
					if($e->errorInfo[0] == '42S21')
						continue;
					
					// ビュー重複追加エラー
					if($e->errorInfo[0] == '42S01')
						continue;
					
					// インデックス重複追加エラー
					if($e->errorInfo[0] == '42000')
						continue;
					
					$error_msg = sprintf("%s\n[Error Code]%s\n[Error Code2]%s\n[SQL]%s", $e->errorInfo[2], $e->errorInfo[0], $e->errorInfo[1], $statement);
					$err_statements[] = $error_msg;
				}
			}
		}
		
		return $err_statements;
	}
}
