<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->add(function ($req, $res, $next) {
	$response = $next($req, $res);
	return $response
		->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
		->withHeader('Access-Control-Allow-Methods', 'GET, POST, HEAD, OPTIONS, PATCH, DELETE');
});

$app->get('/v1/files/{name}', function (Request $request, Response $response, array $args) {
	die('lalala');
	$file = file_get_contents(__DIR__ . "../uploads/" . $args['name']);
	$response->getBody()->write($file);
	return $response;
});

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
	$this->logger->info("Slim-Skeleton '/' route");
	return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/v1/patients', \App\Classes\Patient::class);
$app->get('/v1/patients/{id:\d+}', \App\Classes\Patient::class);
$app->post('/v1/patients', \App\Classes\Patient::class);
$app->patch('/v1/patients/{id:\d+}', \App\Classes\Patient::class);
$app->delete('/v1/patients/{id:\d+}', \App\Classes\Patient::class);

$app->get('/v1/visits', \App\Classes\Visit::class);
$app->get('/v1/visits/{id:\d+}', \App\Classes\Visit::class);
$app->post('/v1/visits', \App\Classes\Visit::class);
$app->post('/v1/visits/{id:\d+}/addFiles', \App\Classes\Visit::class);
$app->patch('/v1/visits/{id:\d+}', \App\Classes\Visit::class);
$app->delete('/v1/visits/{id:\d+}', \App\Classes\Visit::class);