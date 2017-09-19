<?php

include_once ('src/Beanstalk/Client.php');

use Beanstalk\Client;
use Zend\Form\Element;
use Zend\Form\Element\Hidden;
use Zend\Form\Form;

class Rules_Main {

	public function saveSmtpAction($request, $response) {
		$_di = \_makeNew('plugin');
		$_di->set('plug_name', 'smtp1');
		$_di->set('plug_type', 'smtp');
		$_di->loadExisting();

		$value = json_decode($_di->value, TRUE);
		$value = $value ? $value:array();
		$value = array_merge($value, [
			'host'          => $request->cleanString('host'),
			'port'          => $request->cleanString('port'),
			'smtp_username' => $request->cleanString('smtp_username'),
			'from'          => $request->cleanString('from')
		]);
		//only update password if it was passed in
		if ($request->cleanString('smtp_password')) {
			$value['smtp_password'] = $request->cleanString('smtp_password');
		}

		$_di->set('data', json_encode($value));

		$x = $_di->save();
		$response->redir = m_appurl('rules');
	}

	public function saveEmailsAction($request, $response) {
		$_di = \_makeNew('settings');
		$_di->set('key', 'notifylist1');
		$_di->loadExisting();

		$value = json_decode($_di->value, TRUE);
		$value = $value ? $value:array();
		$value = array_merge($value, [
			'email_1' => $request->cleanString('email_1'),
			'email_2' => $request->cleanString('email_2'),
			'email_3' => $request->cleanString('email_3'),
		]);

		$_di->set('value', json_encode($value));

		$x = $_di->save();
		$response->redir = m_appurl('rules');
	}

	public function testAction($request, $response) {

		$k = $request->cleanString('msg');
		$response->addTo('key', trim($k));

		$beanstalk = \_make('beanstalkclient');

		$beanstalk->connect();
		$beanstalk->useTube('status');

		$x = $beanstalk->put(
			23, // Give the job a priority of 23.
			0,  // Do not wait to put job into the ready queue.
			20, // Give the job 10 sec to run.
			'{"type":"status","armed": "yes", "mode": "away", "ignore_faults": "no", "faulted": "no", "panic": "no"}'
		);

		$response->redir = m_appurl('rules');
	}

	public function mainAction($request, $response) {
		//$settings = \_makeNew('dataitem', 'settings', 'key');
		$settings = \_makeNew('plugin');
		$settings->set('plug_name', 'smtp1');
		$settings->set('plug_type', 'smtp');
		$x = $settings->loadExisting();
		$smtpVals = json_decode($settings->get('data'), TRUE);

		$response->smtpForm = $this->loadSmtpForm($smtpVals);

		$notifyList = \_makeNew('settings');
		$notifyList->set('key', 'notifylist1');
		$notifyList->loadExisting();
		$notifyListVals = json_decode($notifyList->get('value'), TRUE);

		$response->emailForm = $this->loadEmailForm($notifyListVals);
	}

	public function loadSmtpForm($params=[]) {
		$form = new Form('smtp_settings');
		$host = new Element('host');
		$host->setLabel('SMTP Host');
		$host->setValue(@$params['host']);

		$port = new Element('port');
		$port->setLabel('SMTP Port');
		$port->setValue(@$params['port']);

		$username = new Element('smtp_username');
		$username->setLabel('Username');
		$username->setValue(@$params['smtp_username']);

		$password = new Element('smtp_password');
		$password->setLabel('Password');
		$password->setAttributes([
			'type' => 'password',
		]);
		$password->setValue(@$params['smtp_password']);


		$from = new Element('from');
		$from->setLabel('From Email');
		$from->setValue(@$params['from']);

		$send = new Element('submit');
		$send->setValue('Save SMTP Settings');
		$send->setAttributes([
				'type' => 'submit',
				'class' => 'form-control btn btn-primary',
			]);

		$action = new Hidden('action');
		$action->setValue('saveSmtp');

		$form->add($host);
		$form->add($port);
		$form->add($username);
		$form->add($password);
		$form->add($from);
		$form->add($send);
		$form->add($action);

		$inputFilter = new Zend\InputFilter\InputFilter();
		$form->setInputFilter($inputFilter);
		$inputFilter->add(
			[
				'name'=>'email',
				'validators' => [
					[
						'name' => 'StringLength',
						'options' => [
							'min' => 1,
							'max' => 255
						],
					]
					]
			]);

		$form->prepare();

		// Assuming change the / to whatever framework you're using does for routes or app urls
//		$form->setAttribute('action', '/');

		// Set the method attribute for the form
		$form->setAttribute('method', 'post');
		return $form;
	}

	public function loadEmailForm($params=[]) {
		$form = new Form('email_notifications');

		$email1 = new Element('email_1');
		$email1->setLabel('Email 1');
		$email1->setValue(@$params['email_1']);

		$email2 = new Element('email_2');
		$email2->setLabel('Email 2');
		$email2->setValue(@$params['email_2']);

		$email3 = new Element('email_3');
		$email3->setLabel('Email 3');
		$email3->setValue(@$params['email_3']);


		$send = new Element('submit');
		$send->setValue('Save Notification Settings');
		$send->setAttributes([
				'type' => 'submit',
				'class' => 'form-control btn btn-primary',
			]);

		$action = new Hidden('action');
		$action->setValue('saveEmails');

		$form->add($email1);
		$form->add($email2);
		$form->add($email3);

		$send = new Element('submit');
		$send->setValue('Save Notification Emails');
		$send->setAttributes([
				'type' => 'submit',
				'class' => 'form-control btn btn-primary',
		]);

		$form->add($action);
		$form->add($send);

		$inputFilter = new Zend\InputFilter\InputFilter();
		$form->setInputFilter($inputFilter);
		$inputFilter->add(
			[
				'name'=>'email',
				'validators' => [
					[
						'name' => 'StringLength',
						'options' => [
							'min' => 1,
							'max' => 255
						],
					]
					]
			]);

		$form->prepare();

		// Set the method attribute for the form
		$form->setAttribute('method', 'post');
		return $form;
	}
}
