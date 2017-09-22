<?php

class Template_Pagetitle {


	public function template($request, $template_section) {
		echo _get('site_title');
		echo '|';
		$title = _get('page_title'); 
		if ($title) {
			echo $title;
		} else {
			$title = ucfirst($request->appName);
			echo $title;
		}
	}
}
