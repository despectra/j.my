<?php
	class MySQLDb {
		private $user;
		private $passwd;
		private $db;
		private $host;

		private $dbh;
		private $sth;

		function __construct() {
			//----------------------------
			$user = 'educationbd';
			$passwd = '123456';
			$db = 'educationbd';
			$host = 'localhost';
			//----------------------------
			$this->user = $user;
			$this->passwd = $passwd;
			$this->db = $db;
			$this->host = $host;
		}

		function connect() {
			try {
				$this->dbh = new PDO("mysql:host=$this->host;dbname=$this->db", $this->user, $this->passwd);
				$this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			}
			catch (PDOException $e) {
				echo $e->getMessage();
			}
		}
		function select($table, $fields, $conditions = '') {
			try {
				$strFields = implode(',', $fields);
				$query = "SELECT $strFields FROM $table ";
				if ($conditions != '') $query.="WHERE $conditions";
				$this->sth = $this->dbh->query($query);
				$this->sth->setFetchMode(PDO::FETCH_ASSOC);
			}
			catch (PDOException $e) {
				echo $e->getMessage();
			}
		}

		function fetchRow() {
			try {
				return $this->sth->fetch();
			}
			catch (PDOException $e) {
				echo $e->getMessage();
			}
		}

		function rowCount() {
			try {
				return $this->sth->rowCount();
			}
			catch (PDOException $e) {
				echo $e->getMessage();
			}
		}

		function insert($table, $data) {
			if (count($data) > 0) {
				try {
					$args = array_keys($data);
					$query = "INSERT INTO $table (".implode(',', $args).") VALUES (:".implode(', :', $args).")";
					$this->sth = $this->dbh->prepare($query);
					$this->sth->execute($data);
					return $this->dbh->lastInsertId();
				}
				catch (PDOException $e) {
					echo $e->getMessage();
				}
			}
			else throw new Exception("Данные для вставки в таблицу $table не обнаружены", 3012);
			
		}

	}
	$my = new MySQLDb();
	$my->connect();
	$my->select('users', array('login', 'passwd'));
	/*echo $my->rowCount()."\n";
	while ($row = $my->fetchRow()) {
		echo $row['login']." --- ".$row['passwd'];
	}*/
	echo $my->insert('tokens', array('uid'=> 1, 'token4' => 'dfsdfsdf'));
?>