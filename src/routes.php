<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

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
$app->patch('/v1/visits/{id:\d+}', \App\Classes\Visit::class);
$app->delete('/v1/visits/{id:\d+}', \App\Classes\Visit::class);