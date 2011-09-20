<?php
class skillsmapModel extends database {
	
	public $profile = false;
	public $config = Array();
	public $page = 1;
	public $show = 15;
	public $search = '';
	public $advanced = false;
	public $distance = false;
	public $postcode = false;
	public $coords = false;
	public $groups = Array();
	public $skills = Array();
	public $view = 'list';
	
	public function __construct($config = Array(), $profile = false) {
		$config['views'] = Array('list', 'map');
		$this->config = $config;
		if (!empty($profile)) $this->profile = $profile;
	}
	
	public function getSkills() {
		return $this->getData('SELECT `sgID` AS `group`, `sgName` AS `groupname`, `sgDescription` AS `groupdesc`, `skillID` AS `skill`, `skillName` AS `skillname`, `skillDescription` AS `skilldesc` FROM `skillgroups`, `skills` WHERE `sgID`=`skillGroup` ORDER BY `sgName`, `skillName`;');
	}
	
	public function getSearch($debug = false) {
		$fields = Array(
			'`profileName` AS `name`',
			'CONCAT("'.SCRIPTNAME.'", `profileSeoName`, "/") AS `href`',
			'`profileTown` AS `town`',
			'IF(`profileImage`="", "", CONCAT("'.FOLDER.$this->config['imagefolder'].'", `profileImage`, "-m.jpg")) AS `image`',
			'`profileDescription` AS `desc`'
		);
		$tables = '`profiles`';
		$sql = Array();
		$relevance = Array();
		// KEYWORD SEARCHING #####################
		if (!empty($this->search) && preg_match_all('#(NOT )?([a-zA-Z@.0-9\']++|"[^"]++")( OR)?#', str_replace(' OR NOT ', ' OR ', $this->search), $matches)) {
			$terms = $matches[2];
			$matches = array_map(null, $matches[1], $matches[2], $matches[3]);
			$query = Array();
			$or = false;
			$short = '';
			foreach ($matches AS $match) {
				$isOr = !empty($match[2]);
				if (strlen($match[1]) < 4) { //  min word length, mysql will ignore
					$short .= (empty($query) ? '' : ($or ? ' OR ' : ' AND ')).'CONCAT_WS(",", `'.implode('`, `', $fields).'`) LIKE ("%'.$this->slashData($match[1]).'%")';
				} else {
					$exact = strpos($match[1], '"') !== false || strpos($match[1], '.') !== false || strpos($match[1], '@') !== false;
					$query[] = ($or ? '' : (empty($match[0]) ? '+' : '-')).($isOr ? '(' : '').$match[1].($exact ? '' : '*').($or && !$isOr ? ')' : '');
				}
				$or = $isOr;
			}
			if ($isOr) $query .= ')';
			$search = Array();
			foreach ($this->config['searchFields'] AS $key => $item) {
				$search = array_merge($search, $item);
				$relevance[$key] = 'MATCH(`'.implode('`, `', $item).'`) AGAINST ("'.implode('", "', $terms).'")';
				if ($debug && $key != 'desc') $fields[] = 'CONCAT_WS(", ", `'.implode('`, `', $item).'`) AS `'.$key.'`';
			}
			if (empty($query)) $sql[] = $short;
			else $sql[] = 'MATCH(`'.implode('`, `', $search).'`) AGAINST (\''.implode(' ', $query).'\' IN BOOLEAN MODE)'.$short;
		}
		// DISTANCE MATCHING #####################
		if ($this->postcode !== false) {
			$distance = $this->getDistanceSql('`profileLat`', '`profileLng`', $this->coords['lat'], $this->coords['lng']);
			$sql[] = $distance.'<'.$this->distance;
			$relevance['distance'] = '('.$this->distance.'-'.$distance.')/'.$this->distance;
			if ($debug) {
				$fields[] = $distance.' AS `distance`';
				$fields[] = $this->distance.' AS `distancemax`';
			}
		}
		// SKILLS ################################
		$skill = !empty($this->skills);
		if (!empty($this->groups)) {
			$tables .= ', `profileskills`, `skills`, `skillgroups`';
			$sql[] = '`psProfile`=`profileID` AND `skillID`=`psSkill`';
			$skillSql = '`skillGroup` IN ('.implode(',', $this->groups).')';
			if ($skill) $skillSql = '('.$skillSql.' OR `psSkill` IN ('.implode(',', $this->skills).'))';
			$sql[] = $skillSql;
		} elseif (!empty($this->skills)) {
			$tables .= ', `profileskills`';
			$sql[] = '`psProfile`=`profileID` AND `psSkill` IN ('.implode(',', $this->skills).')';
			$relevance['skills'] = 'COUNT(`psID`)/'.count($this->skills);
			if ($debug) {
				$fields[] = 'COUNT(`psID`) AS `skills`';
				$fields[] = count($this->skills).' AS `skillsmax`';
			}
			$relevance['level'] = '(SUM(psLevel)/COUNT(`psID`))/'.$this->config['maxlevel'];
			if ($debug) {
				$fields[] = 'SUM(psLevel) AS `level`';
				$fields[] = 'COUNT(`psID`) AS `levelmax`';
			}
		}
		// RELEVANCY ############################
		if (empty($relevance)) {
			return Array();
		} else {
			foreach ($relevance AS $key => &$item) {
				if ($debug) $fields[] = $item.' AS `'.$key.'calc`';
				$item = '('.$item.')*'.$this->config['weightings'][$key];
				if ($debug) $fields[] = $item.' AS `'.$key.'total`';
			}
			$fields[] = '('.implode(')+(', $relevance).') AS `relevance`';
			$query = 'SELECT '.implode(', ', $fields).' FROM '.$tables.(empty($sql) ? '' : ' WHERE '.implode(' AND ', $sql)).' GROUP BY `profileID`'.$this->getSortLimitSql('`relevance`', 'desc', $this->page, $this->show);
			return $this->getData($query);
		}
	}
	
	public function getProfile($profile) {
		return $this->getData('SELECT `profileName` AS `name`, `profileDescription` AS `desc`, `profileTown` AS `town` FROM `profiles` WHERE `profileSeoName`="'.$this->slashData($profile).'" LIMIT 1;');
	}
	
	public function geocodeQuery($query) {
		if (!empty($this->config['geocodeHref']) && ($geo = file_get_contents($this->config['geocodeHref'].urlencode($query))) !== false) {
			$geo = json_decode($geo);
			if (!empty($geo->status) && $geo->status == 'OK') return (array) $geo->results[0]->geometry->location;
		}
		return false;
	}
}
?>