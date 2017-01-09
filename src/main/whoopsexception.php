<?php
class Main_Whoopsexception {

	public function exception() {
		$run     = new Whoops\Run;
		$handler = new Whoops\Handler\PrettyPageHandler;

		// Set the title of the error page:
		$handler->setPageTitle("errorWhoops! There was a problem.");
		$response = _make('response');
		$handler->addDataTable('template vars', $response->sectionList);

		$run->pushHandler($handler);
		$run->handleException( _get('last_exception') );
	}
}
