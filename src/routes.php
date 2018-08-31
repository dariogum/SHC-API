<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
	$response = $next($req, $res);
	return $response
		->withHeader('Access-Control-Allow-Origin', '*')
		->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
		->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
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
$app->patch('/v1/visits/{id:\d+}', \App\Classes\Visit::class);
$app->delete('/v1/visits/{id:\d+}', \App\Classes\Visit::class);

// Catch-all route to serve a 404 Not Found page if none of the routes match
// NOTE: make sure this route is defined last
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
	$handler = $this->notFoundHandler; // handle using the default Slim page not found handler
	return $handler($req, $res);
});