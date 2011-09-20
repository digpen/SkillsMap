<?php
require_once(SERVERFOLDER.'application/skillsmap/skillsmap.model.php');
require_once(SERVERFOLDER.'application/skillsmap/skillsmap.view.php');
class skillsmap {
	
	private $model;
	private $view;
	
	public function __construct($config = Array(), $profile) {
		$this->model = new skillsmapModel($config, $profile);
		$this->view = new skillsmapView($this->model);
		if ($profile === false) {
			if (!empty($_GET['view']) && in_array($_GET['view'], $this->model->config['views'])) $this->model->view = $_GET['view'];
			if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) {
				$this->model->page = $_GET['page'];
			}
			if (!empty($_GET['q'])) $this->model->search = $_GET['q'];
			if (!empty($_GET['advanced'])) $this->model->advanced = true;
			if (!empty($_GET['distance']) && in_array($_GET['distance'], $this->model->config['distances'])) {
				$this->model->distance = $_GET['distance'];
				if (!empty($_GET['postcode'])) {
					$this->model->advanced = true;
					if (!preg_match('#^(GIR 0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]|[A-HK-Y][0-9]([0-9]|[ABEHMNPRV-Y]))|[0-9][A-HJKS-UW]) [0-9][ABD-HJLNP-UW-Z]{2})\z#i', $_GET['postcode'])) {
						trigger_error('The postcode entered is not in the correct format', E_USER_WARNING);
					} elseif (($coords = $this->model->geocodeQuery($_GET['postcode'])) === false) {
						trigger_error('The postcode entered could not be found', E_USER_WARNING);
					} else {
						$this->model->postcode = $_GET['postcode'];
						$this->model->coords = $coords;
					}
				}
			}
			if (isset($_GET['group']) && is_array($_GET['group'])) {
				$groups = array_filter($_GET['group'], 'is_numeric');
				if (!empty($groups)) $this->model->groups = $groups;
				$this->model->advanced = true;
			}
			if (isset($_GET['skill']) && is_array($_GET['skill'])) {
				$skills = array_filter($_GET['skill'], 'is_numeric');
				if (!empty($skills)) $this->model->skills = $skills;
				$this->model->advanced = true;
			}
			template::registerQueryVar(Array('q', 'distance', 'postcode', 'group', 'skill', 'view'));
		}
	}
	
	public function drawPage() {
		if ($this->model->profile !== false) return $this->view->drawProfile($this->model->profile);
		elseif ($this->model->view == 'map') return $this->view->drawMap();
		else return $this->view->drawList();
	}
}
?>