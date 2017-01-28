<?php

class Template_Extrajs {

	public function template($response, $template_section) {
		$list = $response->extraJs;
		if (!is_array($list)) {
			$list = array($list);
		}

		foreach ($list as $_idx => $_url) {
			if (strstr($_url, '<script')) {
				echo $_url;
				continue;
			}
			if (substr($_url, 0, 1) == '/') {
				$url = $_url;
			} else if (substr($_url, 0, 4) == 'http') {
				$url = $_url;
			} else {
				$url = m_turl().$_url;
			}
			echo'
<script type="text/javascript" src="'.$url.'"></script>
';
		}
	}
}
