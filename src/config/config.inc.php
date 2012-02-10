<?php
$config = Array(
	'database' => Array(
		'host' => 'localhost',
		'user' => 'digpen_skillsmap',
		'pass' => '3Tequyap',
		'name' => 'digpen_skillsmap'
	),
	'navigation' => Array(
		Array(
			'name' => 'Skills Map',
			'rollover' => 'Find people with the skills you need',
			'href' => '',
			'title' => 'Skillsmap - Find the people you need',
			'keywords' => '',
			'description' => '',
			'parent' => -1,
			'show' => true,
			'app' => 'skillsmap',
			'friendlyurls' => true
		)
	),
	'skillsmap' => Array(
		'debug' => true,
		'imagefolder' => 'images/profiles/',
		'distances' => Array(5, 10, 20, 30, 50, 100, 200),
		'geocodeHref' => 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=',
		'weightings' => Array(
			'title' => 100,
			'desc' => 50,
			'distance' => 50,
			'skills' => 50,
			'level' => 50
		),
		'searchFields' => Array(
			'title' => Array('profileName', 'profileSeoName', 'profileTown'),
			'desc' => Array('profileDescription')
		),
		'maxlevel' => 3
	)
);
?>