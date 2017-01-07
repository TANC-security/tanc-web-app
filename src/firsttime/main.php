<?php
use Zend\Form\Element;
use Zend\Form\Form;

class Firsttime_Main {

	public function output() {
		_set('page_title', 'First Time Installation');
	}

	public function mainAction($request, $response) {

		$response->form = $this->loadFirstTimeForm([
			'email'    => $request->cleanString('email'),
			'password' => $request->cleanString('password'),
		]);
	}

	public function createAction($request, $response) {
		$response->form = $this->loadFirstTimeForm([
			'email'    => $request->cleanString('email'),
			'password' => $request->cleanString('password'),
		]);
		_set('template.main.file', 'main_main');

		$data = [
			'email'    => $request->cleanString('email'),
			'password' => $request->cleanString('password'),
		];

		$response->form->setData($data);
		if(!$response->form->isValid()) {
			return;
		}
		$user = \_make('user');

		//save new user
		$clean          = $response->form->getData();
		$user->email    = $clean['email'];
		$user->username = $clean['email'];
		$user->password = $clean['password'];
		$user->hashPassword();
		$user->addToGroup(1, 'adm');
		$ret = $user->save();
		if (!$ret) {
			//TODO: var/db/ is not writable
		}

		//log user in
		$session = \_make('session');
		$user->bindSession($session);

		//redirect
		$response->redir = m_appurl('');
	}

	public function loadFirstTimeForm($params=[]) {
		$email = new Element('email');
		$email->setLabel('Email');
		$email->setAttributes([
			'type' => 'email',
		]);
		$email->setValue(@$params['email']);

		$password = new Element('password');
		$password->setLabel('Password');
		$password->setAttributes([
			'type' => 'password',
		]);
		$password->setValue(@$params['password']);


		$password2 = new Element('password2');
		$password2->setLabel('Re-type');
		$password2->setAttributes([
			'type' => 'password',
		]);
		$password2->setValue(@$params['password']);

		$send = new Element('submit');
		$send->setValue('Create Account');
		$send->setAttributes([
				'type' => 'submit',
				'class' => 'form-control btn btn-primary',
			]);

		$message = new Element\Textarea('message');
		$message->setLabel('Message');

		$form = new Form('adminaccount');
		$form->add($email);
		$form->add($password);
		$form->add($password2);
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

		// Assuming change the / to whatever framework you're using does for routes or app urls
		$form->setAttribute('action', '/');

		// Set the method attribute for the form
		$form->setAttribute('method', 'post');
		$form->setAttribute('action', m_appurl('firsttime/main/create'));
		return $form;
	}
}
