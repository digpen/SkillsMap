<?php
// BOOTSTRAP ########################
require_once(str_replace('\\', '/', dirname(__FILE__)).'/template/template.class.php');
$template = new template();
set_error_handler('template::handleError', E_ALL);
require_once(SERVERFOLDER.'application/database/database.class.php');
include(SERVERFOLDER.'config/config.inc.php');
database::setConnection($config['database']['host'], $config['database']['user'], $config['database']['pass'], $config['database']['name']);
// APPLICATION #####################
$db = new database();
if (($page = $db->getData('SELECT `navID` AS `id`, `navTitle` AS `title`, `navHref` AS `href`, `navText` AS `text`, `appCode` AS `app` FROM `nav` LEFT JOIN `applications` ON `appID`=`navApplication` WHERE `nav`.`navActive`=1 AND "'.addslashes($_SERVER['REQUEST_URI']).'" LIKE CONCAT("'.FOLDER.'", `nav`.`navHref`, "%") ORDER BY CHARACTER_LENGTH(CONCAT("'.FOLDER.'", `nav`.`navHref`)) DESC LIMIT 1;')) === false) {
	header('HTTP/1.1 404 Not Found');
	echo 'Page could not be found';
	exit();
} else {
	// CONTENT ##########################
	$content['FOLDER'] = FOLDER;
	$content['SCRIPTNAME'] = SCRIPTNAME;
	$content['KEYWORDS'] = '';
	$content['DESCRIPTION'] = '';
	$content['SITENAME'] = 'DigPen';
	$content['TITLE'] = $page['title'];
	$content['CONTENT'] = template::fixRelativeUrls($page['text'], FOLDER);
	if (!empty($page['app'])) {
		$href = substr(SCRIPTNAME, strlen(FOLDER.$page['href']), -1);
		switch ($page['app']) {
			case 'skillsmap':
				require_once(SERVERFOLDER.'application/skillsmap/skillsmap.controller.php');
				$obj = new skillsmap($config['skillsmap'], $href);
				$content['CONTENT'] = $obj->drawPage();
				break;
				
		}
	}
	if (($nav = $db->getData('SELECT `navID` AS `id`, `navParent` AS `parent`, `navName` AS `name`, `navRollover` AS `rollover`, `navHref` AS `href`, IF(`navActive`=0, 0, `navShow`) AS `show` FROM `nav` ORDER BY `navOrder` ASC;', Array('index' => true))) !== false) {
		$content['NAV'] = $template->drawNav($nav, false, 1, $page['id'], 'nav');
	}
	$content['JAVASCRIPT'] = '';
	$content['YEAR'] = date('Y');
	$content['MSG'] = template::drawMsg();
	$template->compileTemplate($content, 'template.html');
}
?>