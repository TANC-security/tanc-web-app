<?php

class Main_AccessDenied {

	public function accessDenied($event, $args) {
		//if there are no users at all, present
		//main admin account creation page
		$finder   = \_makeNew('dataitem', 'user_login');
		$count = $finder->getUnlimitedCount();
		$response = \_make('response');
		if ($count < 1) {
			$response->redir = m_appurl('firsttime');
			return;
		}
		$response->statusCode = 403;
		_clearHandlers('process');
		$user = \_make('user');
		if ( $user->isAnonymous()) {
			$response->statusCode = 401;
			_iCanHandle('process', 'metrou/login.php::mainAction');
		}
		return TRUE;
	}
}
