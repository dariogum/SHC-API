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

$container[\App\Classes\Patient::class] = function ($c) {
	$logger = $c->get('logger');
	$table = $c->get('db')->table('patients');
	$visitsTable = $c->get('db')->table('visits');
	$filesTable = $c->get('db')->table('visits_files');
	return new \App\Classes\Patient($logger, $table, $visitsTable, $filesTable);
};

$container[\App\Classes\Visit::class] = function ($c) {
	$logger = $c->get('logger');
	$table = $c->get('db')->table('visits');
	$filesTable = $c->get('db')->table('visits_files');
	return new \App\Classes\Visit($logger, $table, $filesTable);
};