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

$app->get('/v1', function (Request $request, Response $response, array $args) {
	$this->logger->info("/v1 visited");
	return $response->withJson(["jsonapi" => ["version" => "1.0"]]);
});

$app->get('/v1/patients', \App\Classes\Patient::class);
$app->get('/v1/patients/{id:\d+}', \App\Classes\Patient::class);
$app->get('/v1/patients/{id:\d+}/visits', \App\Classes\Patient::class);
$app->post('/v1/patients', \App\Classes\Patient::class);
$app->patch('/v1/patients/{id:\d+}', \App\Classes\Patient::class);
$app->delete('/v1/patients/{id:\d+}', \App\Classes\Patient::class);

$app->get('/v1/visits', \App\Classes\Visit::class);
$app->get('/v1/visits/{id:\d+}', \App\Classes\Visit::class);
$app->get('/v1/visits/{id:\d+}/files', \App\Classes\Visit::class);
$app->post('/v1/visits', \App\Classes\Visit::class);
$app->patch('/v1/visits/{id:\d+}', \App\Classes\Visit::class);
$app->delete('/v1/visits/{id:\d+}', \App\Classes\Visit::class);

$app->get('/v1/stats/{report}/{dates}', \App\Classes\Visit::class);

$app->get('/v1/files/{id:\d+}', \App\Classes\File::class);
$app->post('/v1/files', \App\Classes\File::class);
$app->patch('/v1/files/{id:\d+}', \App\Classes\File::class);
$app->delete('/v1/files/{id:\d+}', \App\Classes\File::class);

$app->get('/v1/users/{id:\d+}', \App\Classes\User::class);
$app->post('/v1/users', \App\Classes\User::class);
$app->post('/v1/users/login', \App\Classes\User::class);
$app->patch('/v1/users/{id:\d+}', \App\Classes\User::class);
$app->delete('/v1/users/{id:\d+}', \App\Classes\User::class);