<?php
class skillsmapView {
	
	private $model;
	
	public function __construct($model) {
		$this->model = $model;
	}
	
	protected function drawSearchControls() {
		$html = '<form action="'.SCRIPTNAME.'" accept-charset="utf8"><fieldset>';
		// KEYWORDS ############################
		$html .= '<div class="floatFix"><label for="q">Keywords:</label>';
		$html .= '<input type="text" name="q" id="q" value="'.htmlspecialchars($this->model->search).'" /></div>';
		// ADVANCED ############################
		$html .= '<div id="advanced"'.($this->model->advanced ? '' : ' style="display:none;"').'>';
		// LOCATION ############################
		$html .= '<div><label for="distance">Location:</label>';
		$html .= '<select id="distance" name="distance">';
		foreach ($this->model->config['distances'] AS $item) {
			$html .= '<option value="'.$item.'"'.($this->model->distance == $item ? ' selected="selected"' : '').'>Within '.$item.' Miles</option>';
		}
		$html .= '</select>';
		$html .= '<label for="postcode">of</label>';
		$html .= '<input type="text" name="postcode" id="postcode" value="'.htmlspecialchars($this->model->postcode).'" /></div>';
		// SKILLS ##############################
		if (($data = $this->model->getSkills()) !== false) {
			$html .= '<div class="floatFix"><label for="skills">With the following skills:</label>';
			$html .= '<ul id="skills">';
			$group = false;
			foreach ($data AS $item) {
				if ($group !== $item['group']) {
					if ($group !== false) $html .= '</ul></li>';
					$html .= '<li><label><input type="checkbox" name="group[]" value="'.$item['group'].'"'.(in_array($item['group'], $this->model->groups) ? ' checked="checked"' : '').' />'.htmlspecialchars($item['groupname']).'</label><ul class="floatFix">';
					$group = $item['group'];
				}
				$html .= '<li><label><input type="checkbox" name="skill[]" value="'.$item['skill'].'"'.(in_array($item['skill'], $this->model->skills) ? ' checked="checked"' : '').' />'.htmlspecialchars($item['skillname']).'</label></li>';
			}
			$html .= '</ul></li></ul></div>';
		}
		$html .= '</div>';
		// ADVANCED CONTROL ###################
		$html .= '<a href="'.template::getQueryString(Array('advanced' => (int) !$this->model->advanced), true, true, SCRIPTNAME).'">'.(!$this->model->advanced ? 'Advanced Options' : 'Hide Advanced Options').'</a>';
		// SUBMIT #############################
		$html .= '<input type="submit" value="Search" />';
		return $html.'</fieldset></form>';
	}
	
	protected function drawTabs($view = 'list') {
		$html = '<ul id="tabs" class="floatFix">';
		foreach ($this->model->config['views'] AS $item) {
			$html .= '<li'.($item == $view ? ' class="on"' : '').'><a href="'.template::getQueryString(Array('view' => $item), true, true, SCRIPTNAME).'" title="View this data in '.$item.' view">'.ucfirst($item).' View</a></li>';
		}
		$html .= '</ul>';
		return $html;
	}
	
	public function drawList() {
		$html = $this->drawSearchControls();
		if (($data = $this->model->getSearch()) !== false) {
			$html .= $this->drawTabs('list');
			$html .= '<ol id="list">';
			foreach ($data AS $item) {
				$html .= '<li>';
				$html .= '<h3>'.htmlspecialchars($item['name']).'</h3>';
				$html .= '<div class="town">'.htmlspecialchars($item['town']).'</div>';
				if (!empty($item['image']) && file_exists($item['image'])) {
					$size = getimagesize($item['image']);
					$html .= '<img src="'.htmlspecialchars($item['image']).'" alt="'.htmlspecialchars($item['name']).' picture" width="'.$size[0].'" height="'.$size[1].'" />';
				}
				$html .= '<p>'.htmlspecialchars($item['desc']).'</p>';
				$html .= '</li>';
			}
			$html .= '</ol>';
		} elseif (!template::msgSet()) {
			trigger_error('No profiles match the selected parameters', E_USER_WARNING);
		}
		return $html;
	}
	
	public function drawMap() {
		
	}
	
	public function drawProfile($profile) {
		if (($data = $this->model->getProfile($profile)) !== false) {
			
		} else {
			trigger_error('The profile requested could not be found', E_USER_WARNING);
			return $this->drawList();
		}
	}
}
?>