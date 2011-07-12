<?php
// BOOTSTRAP ########################
require_once(str_replace('\\', '/', dirname(__FILE__)).'/template/template.class.php');
$template = new template();
set_error_handler('template::handleError', E_ALL);
require_once(SERVERFOLDER.'application/database/database.class.php');
include(SERVERFOLDER.'config/config.inc.php');
database::setConnection($config['database']['host'], $config['database']['user'], $config['database']['pass'], $config['database']['name']);
// CONTENT ##########################
$content['FOLDER'] = FOLDER;
$content['SCRIPTNAME'] = SCRIPTNAME;
$content['MSG'] = '';
$content['PAGETITLE'] = '';
$content['KEYWORDS'] = '';
$content['DESCRIPTION'] = '';
$content['SITENAME'] = 'Digital Peninsula South West Skills Map';
$content['YEAR'] = date('Y');
$content['JAVASCRIPT'] = '';
// APPLICATION #####################
$empty = false;
$nav = $config['navigation'];
foreach ($nav AS $key => $item) {
	if (strpos(SCRIPTNAME, FOLDER.$item['href']) === 0) {
		$match[$key] = strlen(FOLDER.$item['href']);
	}
}
if (empty($match)) {
	header('HTTP/1.1 404 Not Found');
	exit('Page could not be found');
} else {
	arsort($match, SORT_NUMERIC);
	$key = key($match);
	if (SCRIPTNAME != FOLDER.$nav[$key]['href'] && !$nav[$key]['friendlyurls']) {
		header('HTTP/1.1 404 Not Found');
		exit('Page could not be found');
	} else {
		$content['TITLE'] = $nav[$key]['title'];
		$content['KEYWORDS'] = $nav[$key]['keywords'];
		$content['DESCRIPTION'] = $nav[$key]['description'];
		if ($nav[$key]['app'] !== false) {
			$page = substr(SCRIPTNAME, strlen(FOLDER.$nav[$key]['href']), -1);
			switch ($nav[$key]['app']) {
				case 'skillsmap':
					require_once(SERVERFOLDER.'application/skillsmap/skillsmap.controller.php');
					$obj = new skillsmap($config['skillsmap'], $page);
					$content['CONTENT'] = $obj->drawPage();
					break;
			}
		}
		$content['NAV'] = $template->drawNav($nav, -1, false, false, 'nav');
		$content['MSG'] = $template->drawMsg();
		$template->compileTemplate($content, 'template.html');
	}
}
?>