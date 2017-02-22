<?php

class Template_Zendform {


	public static function formOpenTag($form) {
		static $formHelper;
		if ($formHelper == NULL) {
			$formHelper     = new Zend\Form\View\Helper\Form();
		}
		echo $formHelper->openTag($form);
	}

	public static function rowHelper($row, $opts) {
		static $rowHelper, $renderer, $configProvider;
		if ($rowHelper == NULL) {
			$rowHelper = new Zend\Form\View\Helper\FormRow();
			$renderer = new Zend\View\Renderer\PhpRenderer();
			$configProvider = new \Zend\Form\ConfigProvider();
			$renderer->setHelperPluginManager(new \Zend\View\HelperPluginManager(new \Zend\ServiceManager\ServiceManager(), $configProvider()['view_helpers']));
			$rowHelper->setView($renderer);
		}

		if (array_key_exists('class', $opts['hash'])) {
			$row->setAttribute('class', $opts['hash']['class']);
		}
		return $rowHelper($row);
	}
}
