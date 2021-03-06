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
    return new \App\Classes\Patient($logger, $table);
};

$container[\App\Classes\PatientBackground::class] = function ($c) {
    $logger = $c->get('logger');
    $table = $c->get('db')->table('patients_background');
    return new \App\Classes\PatientBackground($logger, $table);
};

$container[\App\Classes\SocialSecurity::class] = function ($c) {
    $logger = $c->get('logger');
    $table = $c->get('db')->table('social_securities');
    return new \App\Classes\SocialSecurity($logger, $table);
};

$container[\App\Classes\PatientSocialSecurity::class] = function ($c) {
    $logger = $c->get('logger');
    $table = $c->get('db')->table('patients_social_securities');
    $socialSecurity = $c->get(\App\Classes\SocialSecurity::class);
    return new \App\Classes\PatientSocialSecurity($logger, $table, $socialSecurity);
};

$container[\App\Classes\User::class] = function ($c) {
    $logger = $c->get('logger');
    $table = $c->get('db')->table('users');
    $users_roles = $c->get('db')->table('users_roles');
    return new \App\Classes\User($logger, $table, $users_roles);
};

$container[\App\Classes\Stats::class] = function ($c) {
    $logger = $c->get('logger');
    $patients = $c->get('db')->table('patients');
    $users = $c->get('db')->table('users');
    $visits = $c->get('db')->table('visits');
    return new \App\Classes\Stats($logger, $patients, $users, $visits);
};

$container[\App\Classes\Schedule::class] = function ($c) {
    $logger = $c->get('logger');
    $schedules = $c->get('db')->table('schedules');
    $schedulesDays = $c->get('db')->table('schedules_days');
    $schedulesDaysHours = $c->get('db')->table('schedules_days_hours');
    $schedulesProfessionals = $c->get('db')->table('schedules_professionals');
    $appointments = $c->get('db')->table('appointments');
    $patient = $c->get(\App\Classes\Patient::class);
    $user = $c->get(\App\Classes\User::class);
    return new \App\Classes\Schedule(
        $appointments,
        $logger,
        $patient,
        $schedules,
        $schedulesDays,
        $schedulesDaysHours,
        $schedulesProfessionals,
        $user
    );
};

$container[\App\Classes\Appointment::class] = function ($c) {
    $logger = $c->get('logger');
    $appointments = $c->get('db')->table('appointments');
    $patient = $c->get(\App\Classes\Patient::class);
    $user = $c->get(\App\Classes\User::class);
    return new \App\Classes\Appointment($logger, $appointments, $patient, $user);
};

$container[\App\Classes\Application::class] = function ($c) {
    $logger = $c->get('logger');
    $applications = $c->get('db')->table('applications');
    return new \App\Classes\Application($logger, $applications);
};
