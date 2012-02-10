<?php
class template {
	
	static $msg = '';
	static $msgError = true;
	static $queryVars = Array();
	
	public function __construct() {
		ob_start();
		if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start('ob_gzhandler');
		define('SERVERFOLDER', $this->getServerRoot(2));
		define('SCRIPTNAME', $this->getScriptName());
		define('SCRIPTFOLDER', substr(SCRIPTNAME, 0, strrpos(SCRIPTNAME, '/')+1));
		define('FOLDER', $this->getBaseFolder(SERVERFOLDER, SCRIPTFOLDER));
		if (version_compare(PHP_VERSION, '6.0', '<') == -1) {
		    set_magic_quotes_runtime(false);
			if (get_magic_quotes_gpc()) {
		        $_GET = $this->unSlashData($_GET);
		        $_POST = $this->unSlashData($_POST);
		        $_REQUEST = $this->unSlashData($_REQUEST);
		        $_COOKIE = $this->unSlashData($_COOKIE);
			}
		}
	}
	/**
	 * Defines root server (SERVERFOLDER) constant
	 */
	private function getServerRoot($distance = 0) {
		$serverHome = array_slice(explode('/', str_replace('\\', '/', dirname(__FILE__))), 0, $distance*-1);
		return implode('/', $serverHome).(count($serverHome) > 0 ? '/' : '');
	}
	
	private function getScriptName() {
		if (($path = @parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) === false) {
			trigger_error('Could not process script: An invalid URL was requested', E_USER_ERROR);
		} else {
			$scriptname = '/'.substr($path, 1);
			$pos = strrpos($scriptname, '/')+1;
			$filename = substr($scriptname, $pos);
			if ($pos < mb_strlen($scriptname) && strpos($filename, '.') == false) $scriptname .= '/';
			if ($filename != 'index.php') return $scriptname;
			else return substr($scriptname, 0, $pos);
		}
	}
	
	private function getBaseFolder($serverfolder, $scriptfolder) {
		$web = $scriptfolder == '/' ? Array() : explode('/', substr($scriptfolder, 1, -1)); // chop trailing slashes
		$webRoot = Array();
		if (count($web) > 0) {
			$total = $pos = count($web)-1;
			foreach (array_reverse(explode('/', $serverfolder)) AS $item) {
				for ($i = $pos; $i >= 0; $i--) {
					if ($item == $web[$i]) {
						$webRoot[] = $item;
						$pos = $i-1;
						break;
					} elseif ($pos < $total) {
						break;
					}
				}
			}
		}
		return '/'.implode('/', array_reverse($webRoot)).(count($webRoot) > 0 ? '/' : '');
	}
	
	public function unSlashData($data) {
		if (is_array($data)) {
			foreach ($data AS $key => $value) $data[$key] = $this->unSlashData($value);
			return $data;
		} else {
			return stripslashes($data);
		}
	}
	// CACHE CONTROL ###################################
	public function compileTemplate($content, $html) {
		$html = self::compileHtml($content, $html);
		$etag = md5($html);
		if (isset($headers['If-None-Match']) && (strpos($headers['If-None-Match'], $etag)) !== false) {
			header('HTTP/1.1 304 Not Modified');
			exit();
		} else {
			header('Etag: '.$etag);
			echo $html;
			ob_end_flush();
			if (!headers_sent() && ob_get_length() > 0) {
				header('Accept-ranges: none');
				header('Content-length: '.ob_get_length());
			}
			exit();
		}
	}
	// MESSAGING #######################################
	public static function handleError($type, $msg) {
		switch ($type) {
			case E_USER_ERROR:
				exit($msg);
			case E_USER_WARNING:
				template::setMsg($msg, true);
				break;
			case E_USER_NOTICE:
				template::setMsg($msg, false);
				break;
			default:
				return false;
		}
		return true;
	}
	public function setMsg($msg, $error = true) {
		self::$msg = $msg;
		self::$msgError = $error;
	}
	
	public function msgSet() {
		return !empty(self::$msg);
	}
	
	public function drawMsg() {
		if (self::$msg != '') {
			return '<div id="msg"'.(self::$msgError ? ' class="error"' : '').'>'.self::$msg.'</div>';
		}
		return '';
	}
	// BASIC FUNCTIONS #################################
	public function readHtml($template, $folder = 'templates/') { // reads a HTML file to a variable
		return file_get_contents(SERVERFOLDER.$folder.$template);
	}
	
	public function compileHtml($items, $template, $folder = 'templates/', $start = '{', $end = '}', $upper = true) { // Compiles an Array with a HTML file
		$html = self::readHtml($template, $folder); // Read the template into an array, then create a string.
		return self::recompileHtml($items, $html, $start, $end, $upper);
	}
	
	public function recompileHtml($items, $html, $start = '{', $end = '}', $upper = true) {
		foreach ($items as $key => $value) { // Loop through all the parameters and set the variables to values.
			if (!is_array($value)) $html = str_replace($start.($upper ? strtoupper($key) : $key).$end, $value, $html);
		}
		return $html;
	}
	
	public function drawNav($nav, $parent = false, $draw = false, $on = false, $id = false, $attr = array()) {
		static $i = 0;
		$draw === true ? true : $draw--;
		if ($id === false) $wrap = '';
		elseif ($i == 0) $wrap = $id;
		else $wrap = substr($id, strrpos($id, '-')).'-'.$i;
		$html = '';
		foreach ($nav AS $key => $item) {
			if ($parent == $item['parent'] && $item['show']) {
				if (substr($item['href'], 0, 7) != 'http://') $item['href'] = FOLDER.$item['href'];
				$html .= '<li';
				if (self::hasParent($nav, $key, $on)) $html .= ' class="on"';
				$html .= '><a href="'.$item['href'].'" title="'.$item['rollover'].'">'.$item['name'].'</a>';
				if ($draw) $html .= self::drawNav($nav, $key, $subNav, $id, $attr);
				$html .= '</li>';
			}
			if ($id !== false) $i++;
		}
		if ($html == '') {
			return '';
		} else {
			$temp = '';
			foreach ($attr AS $key => $value) $temp .= ' '.$key.'="'.str_replace('"', '&quot;', $value).'"';
			return '<ul'.($wrap == '' ? '' : ' id="'.$wrap.'"').$temp.'>'.$html.'</ul>';
		}
	}
	
	private function hasParent($data, $key, $id) {
		if ($key == $id) return true;
		elseif (!isset($data[$key])) return false;
		else return self::hasParent($data, $data[$key]['parent'], $id);
	}
	
	public function drawImg($filename, $alt, $attr = Array()) {
		if (!isset($attr['width'])) {
			$size = getimagesize(SERVERFOLDER.$filename);
			$attr['width'] = $size[0];
			$attr['height'] = $size[1];
		}
		if (substr($filename, 0, 7) != 'http://') $filename = FOLDER.$filename;
		$extra = '';
		foreach ($attr AS $key => $value) $extra .= ' '.$key.'="'.str_replace('"', '&quot;', $value).'"';
		return '<img src="'.$filename.'" alt="'.$alt.'"'.$extra.' />';
	}
	
	public function replaceImages($html, $folder, $find, $replace) {
		if (preg_match_all('#<img(.*) \/>#', $html, $matches, PREG_OFFSET_CAPTURE)) {
			$offset = 0;
			foreach ($matches[1] AS $i => $match) {
				if (preg_match_all('# ([^=]+)="([^"]*)"#', $match[0], $params, PREG_SET_ORDER)) {
					$attr = Array();
					foreach ($params AS $param) $attr[$param[1]] = $param[2];
					$attr['src'] = preg_replace('#^('.preg_quote(FOLDER.$folder).'[^.]+)(?:'.implode('|', array_map('preg_quote', $find)).')$#', '$1'.$replace, $attr['src'], 1, $count);
					if ($count == 1) {
						$size = getimagesize(SERVERFOLDER.substr($attr['src'], strlen(FOLDER)));
						$attr['width'] = $size[0];
						$attr['height'] = $size[1];
						$img = '<img';
						foreach ($attr AS $key => $value) $img .= ' '.$key.'="'.$value.'"';
						$img .= ' />';
						$len = strlen($matches[0][$i][0]);
						$offset += strlen($img)-$len;
						$html = substr_replace($html, $img, $matches[0][$i][1], $len);
					}
				}
			}
		}
		return $html;
	}
	
	function fixRelativeUrls($html, $prefix = '{FOLDER}') {
		return preg_replace('#(<(?:a|img|link|script|object|embed)[^>]*)(href|src)\="((?!\#|mailto:|{[A-Z0-9]+}|[a-z]{2,5}://)[^"]+)"#', '$1$2="'.$prefix.'$3"', $html);
	}
	
	public function registerQueryVar() {
		$vars = func_get_args();
		if (is_array(current($vars))) $vars = current($vars);
		foreach ($vars AS $var) if (!in_array($var, self::$queryVars)) self::$queryVars[] = $var;
	}
	/**
	 * Create an array containing the specified variable values, merged with the requested GET or POST values
	 * @param array $vars An array of variable index and values to include in the result
	 * @param bool $includeVars A boolean specifying whether to include registered variables in the result
	 * @return array Returns an array containing the specified key/values
	 */
	public function getQueryVars($vars = Array(), $includeVars = true) {
		if ($includeVars && !empty(self::$queryVars)) {
			$keys = array_fill_keys(self::$queryVars, true);
			$post = array_intersect_key($_POST, $keys); // filter empty string values
			$post = array_filter($post, create_function('$value', 'return $value !== "";'));
			$get = array_intersect_key($_GET, $keys); // don't filter get
			$vars = array_merge($post, $get, $vars);
		}
		return array_filter($vars, create_function('$value', 'return $value !== false;'));
	}
	/**
	 * Create a query string of GET and POST variables
	 * @param array $vars An array of variable index and values to include in the query string
	 * @param array|bool $includeVars Either an array specifying the indexes of the vars to include, or a boolean specifying whether to include vars from the GET array. If an empty array is sent, the vars included will be those specified in template::queryVars
	 * @param string $separator A string specifying the query string seperator, defaults to "&amp;"
	 * @return string Returns a query string encoded with th specified variables
	 */
	public function getQueryString($vars = Array(), $includeVars = true, $encode = true, $href = '') {
		// ANCHOR ################
		if (($anchor = strstr($href, '#')) === false) $anchor = '';
		else $href = substr($href, 0, strpos($href, '#'));
		if (!empty($vars['#'])) {
			$anchor = '#'.$vars['#'];
			unset($vars['#']);
		}
		// VARS ##################
		$query = self::getQueryVars($vars, $includeVars);
		// HREF QUERY ############
		if (($pos = strpos($href, '?')) !== false) {
			parse_str(substr($href, $pos+1), $hard);
			$query = array_merge($hard, $query);
			$href = substr($href, 0, $pos);
		}
		$sep = $encode ? '&amp;' : '&';
		return $href.(!empty($query) ? (strpos($href, '?') === false ? '?' : $sep).http_build_query($query, '', $sep) : '').$anchor;
	}
}
?>