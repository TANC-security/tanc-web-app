<?php

class Main_Models {


	public function resources() {
		_didef('settings', function() {
			$_di = \_makeNew('dataitem', 'settings', 'key');
			$_di->_typeMap['key'] = 'string';
			$_di->_uniqs[] = 'key';
			return $_di;
		});

		_didef('plugin', function() {
			$_di = \_makeNew('dataitem', 'plugin');
			$_di->_typeMap['plug_name'] = 'string';
			$_di->_typeMap['plug_type'] = 'string';
			$_di->_typeMap['data']      = 'blob';
			$_di->_uniqs[] = 'plug_name';
			return $_di;
		});

	}
}
