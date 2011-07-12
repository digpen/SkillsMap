<?php
class database {
	
	static $link;
	public $result;
	public $data = Array();
	public $totalRows = 0;
	
	public static function setConnection($host, $user, $pass, $db) {
		if ((self::$link = mysqli_connect($host, $user, $pass, $db)) !== false) {
			mysqli_query(self::$link, 'SET NAMES "utf8"');
			return true;
		} else {
			trigger_error('Could not connect to MySQL server "'.$config['dbhost'].'": '.$this->getDbError(), E_USER_ERROR);
			return false;
		}
	}
	
	public function connect() {
		if (!self::$link) {
			trigger_error('Could not connect to MySQL server: Noconnection has been defined', E_USER_ERROR);
			return false;
		} else {
			return true;
		}
	}
	
	public function slashData($data) {
		if (is_string($data) && $this->connect()) {
			$data = mysqli_real_escape_string(self::$link, $data);
		} elseif (is_array($data) && $this->connect()) {
			foreach ($data AS &$item) {
				if (is_string($item)) $item = mysqli_real_escape_string(self::$link, $item);
			}
		}
		return $data;
	}
	
	public function query($query) {
		if ($this->connect()) {
			if (is_object($this->result)) mysqli_free_result($this->result);
			if (($this->result = mysqli_query(self::$link, $query)) !== false) {
				return true;
			} else {
				trigger_error('MySQL query failed: '.htmlspecialchars($query).', MySQL said: '.mysqli_error(self::$link), E_USER_WARNING);
				return false;
			}
		}
	}
	
	public function fetchArray() {
		if (($arr = mysqli_fetch_assoc($this->result)) !== NULL) return $arr;
		else return false;
	}
	
	public function numRows() {
		return mysqli_num_rows($this->result);
	}
	
	public function affectedRows() {
		if (($affectedRows = mysqli_affected_rows(self::$link)) !== -1) {
			return $affectedRows;
		} else {
			trigger_error('MySQL affectedRows() failed, MySQL said: '.mysqli_error(self::$link), E_USER_WARNING);
			return -1;
		}
	}
	
	public function getTotalRows() {
		if ($this->query('SELECT FOUND_ROWS() AS `count` LIMIT 1;') && $this->numRows() == 1) {
			$Row = $this->fetchArray();
			return $Row['count'];
		}
		return 0;
	}
	
	public function getLastInsertId() {
		return mysqli_insert_id(self::$link);
	}
	
	public function getDbError() {
		if (self::$link) return mysqli_error(self::$link);
		else return mysqli_connect_error();
	}
	
	public function getData($query, $config = Array()) {
		$config = array_merge(Array('failempty' => true, 'index' => false, 'id' => 'id', 'parent' => 'parent'), $config);
		if ($this->query($query)) {
			$data = Array();
			while (($arr = $this->fetchArray()) !== false) {
				if (!$config['index']) $data[] = $arr;
				else $data[$arr[$config['id']]] = $arr;
			}
			if ($config['index']) {
				if (empty($config['parentids'])) $config['parentids'] = array_keys($data);
				foreach ($data AS &$item) {
					if (!in_array($item[$config['parent']], $config['parentids'])) $item[$config['parent']] = false;
				}
			}
			if (!empty($data)) {
				if (preg_match('/LIMIT 1(;?)\z/', $query)) return $data[0];
				elseif (strpos($query, ' SQL_CALC_FOUND_ROWS ') !== false) $this->totalRows = $this->getTotalRows();
				return $data;
			} elseif (!$config['failempty']) {
				return Array();
			}
		}
		return false;
	}
	
	public function setData($query, $arr, $config = Array()) { // , $cache = false
		if (isset($this->data[$arr]) && is_array($this->data[$arr])) {
			return (!$failEmpty || !empty($this->data[$arr]));
		} elseif (($data = $this->getData($query, $config)) !== false) {
			$this->data[$arr] = $data;
			return true;
		}
		$this->data[$arr] = Array();
		return false;
	}
	
	public function getDistanceSql($latA, $longA, $latB, $longB, $metric = true) {
		$scale = $metric ? 6371 : 3959; // kilometres | miles
		return '('.$scale.'*ACOS(COS(RADIANS('.$latA.'))*COS(RADIANS('.$latB.'))*COS(RADIANS('.$longB.')-RADIANS('.$longA.'))+SIN(RADIANS('.$latA.'))*SIN(RADIANS('.$latB.'))))';
	}
	
	public function getSortLimitSql($sort = Array(), $order = 'asc', $page = 1, $show = 0) {
		$sql = '';
		if (!is_array($sort)) $sort = $sort != '' ? Array($sort) : Array();
		if (count($sort) > 0) {
			foreach ($sort AS $key => $value) $sort[$key] = $value;
			$sql = ' ORDER BY '.implode(' '.strToUpper($order).', ', $sort).' '.strToUpper($order);
		}
		if ($show > 0) $sql .= ' LIMIT '.(($page-1) * $show).', '.$show.';';
		return $sql;
	}
}
?>