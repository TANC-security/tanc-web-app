<?php

class Template_Extrajs {

	public function template($response, $template_section) {
		$list = $response->extrajs;
		if (!is_array($list)) {
			echo'
<script type="text/javascript" href="'.$list.'"></script>
';
			return;
		}

		foreach ($list as $_idx => $_url) {
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
