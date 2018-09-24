<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
	$settings = $c->get('settings')['renderer'];
	return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
	$settings = $c->get('settings')['logger'];
	$logger = new Monolog\Logger($settings['name']);
	$logger->pushProcessor(new Monolog\Processor\UidProcessor());
	$logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
	return $logger;
};

// eloquent
$container['db'] = function ($c) {
	$capsule = new \Illuminate\Database\Capsule\Manager;
	$capsule->addConnection($c['settings']['db']);
	$capsule->setAsGlobal();
	$capsule->bootEloquent();
	return $capsule;
};

// cloudinary
$container['cloudinary'] = function ($c) {
	$config = $c->get('cloudinary')['config'];
	return $config;
};

$container[\App\Classes\File::class] = function ($c) {
	$logger = $c->get('logger');
	$table = $c->get('db')->table('visits_files');
	return new \App\Classes\File($logger, $table);
};

$container[\App\Classes\Visit::class] = function ($c) {
	$logger = $c->get('logger');
	$table = $c->get('db')->table('visits');
	$file = $c->get(\App\Classes\File::class);
	return new \App\Classes\Visit($logger, $table, $file);
};

$container[\App\Classes\Patient::class] = function ($c) {
	$logger = $c->get('logger');
	$table = $c->get('db')->table('patients');
	$visit = $c->get(\App\Classes\Visit::class);
	return new \App\Classes\Patient($logger, $table, $visit);
};

$container[\App\Classes\User::class] = function ($c) {
	$logger = $c->get('logger');
	$table = $c->get('db')->table('users');
	return new \App\Classes\User($logger, $table);
};

$container[\App\Classes\Stats::class] = function ($c) {
	$logger = $c->get('logger');
	$patients = $c->get('db')->table('patients');
	$users = $c->get('db')->table('users');
	$visits = $c->get('db')->table('visits');
	return new \App\Classes\Stats($logger, $patients, $users, $visits);
};