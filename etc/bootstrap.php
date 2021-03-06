<?php
//default to develop environment
//this connects with $request->isDevelopment() $request->isDemo() $request->isEnv('prod')
_set('env', 'dev');
//override with Apache SetEnv
// or fastcgi_param   APPLICATION_ENV  production
 /*
if (array_key_exists('APPLICATION_ENV', $_SERVER)) {
	_set('env', $_SERVER['APPLICATION_ENV']);
}
if (array_key_exists('APP_ENV', $_SERVER)) {
	_set('env', $_SERVER['APP_ENV']);
}
  */

foreach (array('APPLICATION_ENV', 'APP_ENV') as $_envkey) {
	if (array_key_exists($_envkey, $_SERVER)) {
		_set('env', $_SERVER[$_envkey]);
		break;
	}
	if (getenv($_envkey) !==FALSE) {
		_set('env', getenv($_envkey));
		break;
	}
}
foreach (array('BEANSTALK_ADDRESS', 'MQTT_ADDRESS') as $_envkey) {
	if (array_key_exists($_envkey, $_SERVER)) {
		_set($_envkey, $_SERVER[$_envkey]);
		continue;
	}
	if (getenv($_envkey) !==FALSE) {
		_set($_envkey, getenv($_envkey));
		continue;
	}
}


//setup metrofw
_iCanHandle('analyze',   'metrofw/analyzer.php');
_iCanHandle('analyze',   'metrofw/router.php', 3);
_iCanHandle('resources', 'metrofw/output.php');
_iCanHandle('output',    'metrofw/output.php');
//will be removed if output.php doesn't think we need HTML output
_iCanHandle('output',    'metrofw/template.php', 3);

#raintpl
#_iCanHandle('template.main',    'template/rain.php::template', 3);
#_iCanHandle('template.main',    'template/rain.php::template', 3);
_connect('template',          'template/lightncandy.php::template', 3);
_connect('template.main',     'template/lightncandy.php::template', 3);
_connect('template.main',     'metrofw/template.php::template', 3);

_connect('template.sparkmsg', 'template/sparkmsg.php::template');
_connect('template.extraJs',  'template/extrajs.php::template');
_connect('template.pagetitle','template/pagetitle.php::template');



#_iCanHandle('exception', 'metrofw/exdump.php::onException');
if (_get('env') == 'dev') {
	_connect('exception', 'main/whoopsexception.php');
}

_iCanHandle('hangup',    'metrofw/output.php');

_didef('request',        'metrofw/request.php');
_didef('response',       'metrofw/response.php');
_didef('router',         'metrofw/router.php');
_didef('foobar',         (object)array());

_didef('logService',     'main/logger.php');
_didef('sslCertService', 'Tanc\SSL\CertService');

//metrodb
_didef('dataitem', 'metrodb/dataitem.php');
Metrodb_Connector::setDsn('default', 'sqlite3://root:mysql@127.0.0.1:3306/var/db/tanc.db');
//end metrodb

//metrou
_connect('authenticate', 'metrou/authenticator.php');
//Users
_didef('authorizer', 'metrou/authorizer.php',
	array('metrou', '/login', '/dologin', '/logout', '/dologout', '/register', '/firsttime', '/main/sslcheck')
);
_connect('authorize', array(_make('authorizer'), 'requireLogin'));


//events
#_connect('access.denied',        'metrou/accessDenied.php::accessDenied');
_connect('access.denied',           'main/accessDenied.php::accessDenied');
_connect('authenticate.success', 'metrou/login.php::authSuccess');
_connect('authenticate.failure', 'metrou/login.php::authFailure');

//things
_didef('user',           'metrou/user.php');
_didef('session',        'metrou/sessionsimple.php');
session_save_path('./var/sess');
#_didef('session',        'metrou/sessiondb.php');
//end metrou

//_didef('taxcalc',  'utils/taxcaclculatorv1.php');
//_didef('taxcalc',  '\FER\Utils\Taxcalculator');

_set('template_basedir', 'templates/');
_set('template_baseuri', 'templates/');
_set('template_name',    'tanc');
_set('site_title',       'TANC');

_set('route_rules',  array() );

_set('route_rules',
	array_merge(array('/:appName'=>array( 'modName'=>'main', 'actName'=>'main' )),
	_get('route_rules')));

_set('route_rules',
	array_merge(array('/:appName/:modName'=>array( 'actName'=>'main' )),
	_get('route_rules')));

_set('route_rules',
	array_merge(array('/:appName/:modName/:actName'=>array(  )),
	_get('route_rules')));

_set('route_rules',
	array_merge(array('/:appName/:modName/:actName/:arg'=>array(  )),
	_get('route_rules')));

_connect('resources', 'main/models.php');

_didef('beanstalkclient', function() {
	$addr = _get('BEANSTALK_ADDRESS', '127.0.0.1:11300');
	list($host, $port) = explode(':', $addr);
	include_once ('src/Beanstalk/Client.php');
	return new \Beanstalk\Client(['host'=>$host, 'port'=>$port]);
});

_didef('mqttclient', function() {
	$addr = _get('MQTT_ADDRESS', '127.0.0.1:1883');
	return new \MarkKimsal\Mqtt\Client('tcp://'.$addr);
});
