<?php

class Main_Models {


	public function resources() {
		_didef('settings', function() {
			$_di = \_makeNew('dataitem', 'settings', 'key');
			$_di->_typeMap['key'] = 'string';
			$_di->_uniqs[] = 'key';
			return $_di;
		});
	}
}
