<?php
	require_once 'exexception.php';

	class MySQLDb {
		private $user;
		private $passwd;
		private $db;
		private $host;

		private $dbh;
		private $sth;

        private $isTransaction;

		function getUser() {
			return $this->user;
		}

		function __construct() {
			require_once 'mysqlsettings.php';
			$this->user = $user;
			$this->passwd = $passwd;
			$this->db = $db;
			$this->host = $host;
            $this->isTransaction = false;
		}

		function connect() {
			try {
				//echo $this->user;
				$this->dbh = new PDO("mysql:host=$this->host;dbname=$this->db", $this->user, $this->passwd);
				$this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
				$this->dbh->exec("SET CHARACTER SET utf8");
			}
			catch (PDOException $e) {
				//throw new exException("Внутренняя ошибка SQL", 3999);
				throw new exException("Внутренняя ошибка SQL: ".$e->getMessage(), 3999);
			}
		}
		function select($table, $fields, $conditions = '') {
			try {
				$strFields = implode(',', $fields);
				$query = "SELECT $strFields FROM $table ";
				if ($conditions != '') {
                    $query.="WHERE $conditions";
                }
				$this->sth = $this->dbh->query($query);
				$this->sth->setFetchMode(PDO::FETCH_ASSOC);
			}
			catch (PDOException $e) {
				throw new exException("Внутренняя ошибка SQL: ".$e->getMessage(), 3999);
				//throw new exException($e->getMessage(), 3999);
			}
		}

		function fetchRow() {
			try {
				return $this->sth->fetch(PDO::FETCH_ASSOC);
			}
			catch (PDOException $e) {
				throw new exException("Внутренняя ошибка SQL: ".$e->getMessage(), 3999);
			}
		}

		function rowCount() {
			try {
				return $this->sth->rowCount();
			}
			catch (PDOException $e) {
				throw new exException("Внутренняя ошибка SQL: ".$e->getMessage(), 3999);
			}
		}

		function insert($table, $data) {
			if (count($data) > 0) {
				try {
					$args = array_keys($data);
					$query = "INSERT INTO $table (".implode(', ', $args).") VALUES (:".implode(', :', $args).")";
					$this->sth = $this->dbh->prepare($query);
					$this->sth->execute($data);
					return $this->dbh->lastInsertId();
				}
				catch (PDOException $e) {
					throw new exException("Внутренняя ошибка SQL: ".$e->getMessage(), 3999);
				}
			}
			else throw new exException("Данные для вставки в таблицу не обнаружены", 3012);	
		}

        function update($table, $data, $conditions) {
            if(count($data) > 0) {
                try {
                    function updateSetSection($k) {
                        return "$k = :$k";
                    }
                    $keys = array_keys($data);
                    $set = array_map("updateSetSection", $keys);
                    $query = "UPDATE $table SET ".implode(', ', $set);
                    if ($conditions != '') {
                        $query .= " WHERE $conditions";
                    }
                    $this->sth = $this->dbh->prepare($query);
                    $this->sth->execute($data);
                    return $this->sth->rowCount();
                } catch (PDOException $e) {
                    throw new exException("Внутренняя ошибка SQL: ".$e->getMessage(), 3999);
                }
            } else {
                throw new exException("Данные для обновления не обнаружены", 3012);
            }
        }

        function delete($table, $conditions) {
            try {
                $query = "DELETE FROM $table";
                if ($conditions != "") {
                    $query .= " WHERE $conditions";
                }
                $this->sth = $this->dbh->prepare($query);
                $this->sth->execute();
                return $this->sth->rowCount();
            }
            catch (PDOException $e) {
                throw new exException("Внутренняя ошибка SQL: ".$e->getMessage(), 3999);
            }
		}

        function beginTransaction() {
            try {
                $this->dbh->beginTransaction();
                $this->isTransaction = true;
            } catch (PDOException $e){
                throw new exException("Невозможно начать транзакцию SQL", 3998);
            };
        }

        function commit() {
            try {
                if ($this->isTransaction) {
                    $this->dbh->commit();
                    $this->isTransaction = false;
                }
            } catch (PDOException $e){
                throw new exException("Невозможно завершить транзакцию SQL", 3997);
            };
        }

        function rollBack() {
            try {
                if ($this->isTransaction) {
                    $this->dbh->rollBack();
                    $this->isTransaction = false;
                }
            } catch (PDOException $e){
                throw new exException("Невозможно отменить транзакцию SQL", 3997);
            };
        }

		function executeQuery($query, $queryArgs) {
			try {
				$this->sth = $this->dbh->prepare($query);
                $this->sth->execute($queryArgs);
			}
			catch (PDOException $e) {
				throw new exException("Внутренняя ошибка SQL: ".$e->getMessage(), 3999);
			}
		}

		function close() {
			$sth = null;
			$dbh = null;
		}
	}
?>