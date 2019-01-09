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
    return $response->withJson(["jsonapi" => ["version" => "2.0"]]);
});

$app->get('/v1/patients', \App\Classes\Patient::class);
$app->get('/v1/patients/{id:\d+}', \App\Classes\Patient::class);
$app->get('/v1/patients/search/{term}', \App\Classes\Patient::class);
$app->post('/v1/patients', \App\Classes\Patient::class);
$app->put('/v1/patients/{id:\d+}', \App\Classes\Patient::class);
$app->delete('/v1/patients/{id:\d+}', \App\Classes\Patient::class);

$app->get('/v1/patientsbackground/{patient:\d+}', \App\Classes\PatientBackground::class);
$app->post('/v1/patientsbackground', \App\Classes\PatientBackground::class);
$app->put('/v1/patientsbackground/{id:\d+}', \App\Classes\PatientBackground::class);
$app->delete('/v1/patientsbackground/{id:\d+}', \App\Classes\PatientBackground::class);

$app->get('/v1/patientssocialsecurity/{patient:\d+}', \App\Classes\PatientSocialSecurity::class);
$app->post('/v1/patientssocialsecurity', \App\Classes\PatientSocialSecurity::class);
$app->put('/v1/patientssocialsecurity/{id:\d+}', \App\Classes\PatientSocialSecurity::class);
$app->delete('/v1/patientssocialsecurity/{id:\d+}', \App\Classes\PatientSocialSecurity::class);

$app->get('/v1/socialsecurities', \App\Classes\SocialSecurity::class);
$app->get('/v1/socialsecurities/{id:\d+}', \App\Classes\SocialSecurity::class);
$app->post('/v1/socialsecurities', \App\Classes\SocialSecurity::class);
$app->put('/v1/socialsecurities/{id:\d+}', \App\Classes\SocialSecurity::class);
$app->delete('/v1/socialsecurities/{id:\d+}', \App\Classes\SocialSecurity::class);

$app->get('/v1/visits', \App\Classes\Visit::class);
$app->get('/v1/visits/{id:\d+}', \App\Classes\Visit::class);
$app->get('/v1/visits/{id:\d+}/files', \App\Classes\Visit::class);
$app->post('/v1/visits', \App\Classes\Visit::class);
$app->patch('/v1/visits/{id:\d+}', \App\Classes\Visit::class);
$app->delete('/v1/visits/{id:\d+}', \App\Classes\Visit::class);

$app->get('/v1/stats/{report}/{dates}', \App\Classes\Stats::class);

$app->get('/v1/files/{id:\d+}', \App\Classes\File::class);
$app->post('/v1/files', \App\Classes\File::class);
$app->patch('/v1/files/{id:\d+}', \App\Classes\File::class);
$app->delete('/v1/files/{id:\d+}', \App\Classes\File::class);

$app->get('/v1/users', \App\Classes\User::class);
$app->get('/v1/users/professionals', \App\Classes\User::class);
$app->get('/v1/users/search/{terms}', \App\Classes\User::class);
$app->get('/v1/users/{id:\d+}', \App\Classes\User::class);
$app->post('/v1/users', \App\Classes\User::class);
$app->post('/v1/users/login', \App\Classes\User::class);
$app->patch('/v1/users/{id:\d+}', \App\Classes\User::class);
$app->delete('/v1/users/{id:\d+}', \App\Classes\User::class);

$app->get('/v1/schedules', \App\Classes\Schedule::class);
$app->get('/v1/schedules/{id:\d+}', \App\Classes\Schedule::class);
$app->get('/v1/schedules/{id:\d+}/{start}/{end}', \App\Classes\Schedule::class);
$app->post('/v1/schedules', \App\Classes\Schedule::class);
$app->patch('/v1/schedules/{id:\d+}', \App\Classes\Schedule::class);
$app->delete('/v1/schedules/{id:\d+}', \App\Classes\Schedule::class);

$app->get('/v1/appointments', \App\Classes\Appointment::class);
$app->get('/v1/appointments/{id:\d+}', \App\Classes\Appointment::class);
$app->post('/v1/appointments', \App\Classes\Appointment::class);
$app->patch('/v1/appointments/{id:\d+}', \App\Classes\Appointment::class);
$app->delete('/v1/appointments/{id:\d+}', \App\Classes\Appointment::class);

$app->get('/v1/applications', \App\Classes\Application::class);
$app->get('/v1/applications/{id:\d+}', \App\Classes\Application::class);
$app->get('/v1/applications/byPatient/{patient:\d+}', \App\Classes\Application::class);
$app->post('/v1/applications', \App\Classes\Application::class);
$app->patch('/v1/applications/{id:\d+}', \App\Classes\Application::class);
$app->delete('/v1/applications/{id:\d+}', \App\Classes\Application::class);
