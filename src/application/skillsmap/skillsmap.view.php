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
		// SUBMIT #############################
		$html .= '<div class="floatFix submit"><input type="submit" value="Search" />';
		// ADVANCED CONTROL ###################
		$html .= '<a href="'.template::getQueryString(Array('advanced' => (int) !$this->model->advanced), true, true, SCRIPTNAME).'">'.(!$this->model->advanced ? 'Advanced Options' : 'Hide Advanced Options').'</a></div>';
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
		$debug = $this->model->config['debug'];
		$html = $this->drawSearchControls();
		if (($data = $this->model->getSearch($debug)) !== false && !empty($data)) {
			$html .= $this->drawTabs('list');
			$html .= '<ol id="list">';
			foreach ($data AS $item) {
				$html .= '<li>';
				$html .= '<h3><a href="'.htmlspecialchars($item['href']).'" title="View this profile">'.htmlspecialchars($item['name']).'</a></h3>';
				$html .= '<div class="town">'.htmlspecialchars($item['town']).'</div>';
				if (!empty($item['image']) && file_exists($item['image'])) {
					$size = getimagesize($item['image']);
					$html .= '<img src="'.htmlspecialchars($item['image']).'" alt="'.htmlspecialchars($item['name']).' picture" width="'.$size[0].'" height="'.$size[1].'" />';
				}
				$html .= '<p>'.htmlspecialchars($item['desc']).'</p>';
				if ($debug) {
					$html .= '<h3>Relevancy Debug Information</h3>';
					$html .= '<table><thead><tr>';
					$html .= '<th>Parameter</th>';
					$html .= '<th>Value</th>';
					$html .= '<th>Max</th>';
					$html .= '<th>Calculated</th>';
					$html .= '<th>Weighting</th>';
					$html .= '<th>Total</th>';
					$html .= '</tr></thead><tbody>';
					foreach ($this->model->config['weightings'] AS $key => $value) {
						if (isset($item[$key.'total'])) {
							$html .= '<tr><td><strong>'.htmlspecialchars(ucfirst($key)).'</strong></td>';
							$html .= '<td>'.$item[$key].'</td>';
							$html .= '<td class="centre">'.(empty($item[$key.'max']) ? 'n/a' : $item[$key.'max']).'</td>';
							$html .= '<td class="centre">'.(empty($item[$key.'calc']) ? 'n/a' : number_format($item[$key.'calc'], 4)).'</td>';
							$html .= '<td class="centre">'.$value.'</td>';
							$html .= '<td class="right">'.number_format($item[$key.'total'], 4).'</td></tr>';
						}
					}
					$html .= '<tr><td colspan="5" class="right">Total:</td><td>'.number_format($item['relevance'], 4).'</td></tr>';
					$html .= '</tbody></table>';
				}
				$html .= '</li>';
			}
			$html .= '</ol>';
		} elseif ($data === false && !template::msgSet()) {
			trigger_error('No profiles match the selected parameters', E_USER_WARNING);
		}
		return $html;
	}
	
	public function drawMap() {
		$html = $this->drawSearchControls();
		if (($data = $this->model->getSearch()) !== false) {
			$html .= $this->drawTabs('list');
			$html .= '<ol id="list">';
			foreach ($data AS $item) {
			}
		} elseif (!template::msgSet()) {
			trigger_error('No profiles match the selected parameters', E_USER_WARNING);
		}
		return $html;
	}
	
	public function drawProfile($profile) {
		if (($data = $this->model->getProfile($profile)) !== false) {
			return '<pre>'.print_r($data, true).'</pre>';
		} else {
			trigger_error('The profile requested could not be found', E_USER_WARNING);
			return $this->drawList();
		}
	}
}
?>