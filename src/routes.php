<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
	$this->logger->info("Slim-Skeleton '/' route");
	return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/v1/categories', \App\Classes\Category::class);
$app->get('/v1/categories/{id:\d+}', \App\Classes\Category::class);
$app->post('/v1/categories', \App\Classes\Category::class);
$app->patch('/v1/categories/{id:\d+}', \App\Classes\Category::class);
$app->delete('/v1/categories/{id:\d+}', \App\Classes\Category::class);

$app->get('/v1/priorities', \App\Classes\Priority::class);
$app->get('/v1/priorities/{id:\d+}', \App\Classes\Priority::class);
$app->post('/v1/priorities', \App\Classes\Priority::class);
$app->patch('/v1/priorities/{id:\d+}', \App\Classes\Priority::class);
$app->delete('/v1/priorities/{id:\d+}', \App\Classes\Priority::class);

$app->get('/v1/statuses', \App\Classes\Status::class);
$app->get('/v1/statuses/{id:\d+}', \App\Classes\Status::class);
$app->post('/v1/statuses', \App\Classes\Status::class);
$app->patch('/v1/statuses/{id:\d+}', \App\Classes\Status::class);
$app->delete('/v1/statuses/{id:\d+}', \App\Classes\Status::class);

$app->get('/v1/tags', \App\Classes\Tag::class);
$app->get('/v1/tags/{id:\d+}', \App\Classes\Tag::class);
$app->post('/v1/tags', \App\Classes\Tag::class);
$app->patch('/v1/tags/{id:\d+}', \App\Classes\Tag::class);
$app->delete('/v1/tags/{id:\d+}', \App\Classes\Tag::class);

$app->get('/v1/units', \App\Classes\Unit::class);
$app->get('/v1/units/{id:\d+}', \App\Classes\Unit::class);
$app->post('/v1/units', \App\Classes\Unit::class);
$app->patch('/v1/units/{id:\d+}', \App\Classes\Unit::class);
$app->delete('/v1/units/{id:\d+}', \App\Classes\Unit::class);

$app->get('/v1/projects', \App\Classes\Project::class);
$app->get('/v1/projects/{id:\d+}', \App\Classes\Project::class);
$app->post('/v1/projects', \App\Classes\Project::class);
$app->patch('/v1/projects/{id:\d+}', \App\Classes\Project::class);
$app->delete('/v1/projects/{id:\d+}', \App\Classes\Project::class);

/*
$app->get('/v1/projects', function (Request $request, Response $response, array $args) {
$this->logger->info("Slim-Skeleton '/projects' route");

$sort = $request->getQueryParams()['sort'];
if ($sort) {
$fields = explode(",", $sort);
}

$page = $request->getQueryParams()['page'];
switch ($page) {
case 'first':
# code...
break;

case 'last':
# code...
break;

case 'prev':
# code...
break;

case 'next':
# code...
break;

default:
# code...
break;
}

$filter = $request->getQueryParams()['filter'];
if ($filter) {

}

$data = [
[
"type" => "project",
"id" => 1,
"attributes" => [
"name" => "Project 1",
"description" => "Description Project 1",
],
"relationships" => [
"status" => [
"links" => [
"self" => "projects/1/relationships/status",
"related" => "projects/1/status",
],
"data" => [
"type" => "status",
"id" => 1,
],
],
"category" => [
"links" => [
"self" => "projects/1/relationships/category",
"related" => "projects/1/category",
],
"data" => [
"type" => "category",
"id" => 1,
],
],
"assignee" => [
"links" => [
"self" => "projects/1/relationships/assignee",
"related" => "projects/1/assignee",
],
"data" => [
"type" => "user",
"id" => 1,
],
],
"followers" => [
"links" => [
"self" => "projects/1/relationships/followers",
"related" => "projects/1/followers",
],
"data" => [
[
"type" => "user",
"id" => 1,
],
[
"type" => "user",
"id" => 2,
],
],
],
"groups" => [
"links" => [
"self" => "projects/1/relationships/groups",
"related" => "projects/1/groups",
],
"data" => [
[
"type" => "group",
"id" => 1,
],
[
"type" => "group",
"id" => 2,
],
],
],
],
"links" => [
"self" => "projects/1",
],
],
];

$result = [
"jsonapi" => [
"version" => "1.0",
],
"links" => [
"self" => "/projects",
"first" => "/projects?page=first",
"last" => "/projects?page=last",
"prev" => "/projects?page=prev",
"next" => "/projects?page=next",
],
"data" => $data,
];

$newResponse = $response->withJson($result);

return $newResponse;
});

$app->post('/v1/projects', function (Request $request, Response $response, array $args) {
$this->logger->info("Slim-Skeleton '/projects' route");

$origin = [
"data" => [
"type" => "project",
"attributes" => [
"name" => "Project 2",
"description" => "Description Project 2",
],
],
"relationships" => [
"status" => [
"data" => [
"type" => "status",
"id" => 1,
],
],
"category" => [
"data" => [
"type" => "category",
"id" => 1,
],
],
"assignee" => [
"data" => [
"type" => "user",
"id" => 1,
],
],
"followers" => [
"data" => [
[
"type" => "user",
"id" => 1,
],
[
"type" => "user",
"id" => 2,
],
],
],
],
];

$result = [
"jsonapi" => [
"version" => "1.0",
],
"data" => [
"type" => "project",
"id" => 2,
"attributes" => [
"name" => "Project 2",
"description" => "Description Project 2",
],
"relationships" => [
"status" => [
"data" => [
"type" => "status",
"id" => 1,
],
],
"category" => [
"data" => [
"type" => "category",
"id" => 1,
],
],
"assignee" => [
"data" => [
"type" => "user",
"id" => 1,
],
],
"followers" => [
"data" => [
[
"type" => "user",
"id" => 1,
],
[
"type" => "user",
"id" => 2,
],
],
],
],
"links" => [
"self" => "projects/2",
],
],
];

$newResponse = $response->withAddedHeader('Location', 'projects/2');
$newResponse = $newResponse->withJson($result, 201);

return $newResponse;
});

$app->get('/v1/projects/{id:\d+}', function (Request $request, Response $response, array $args) {
$this->logger->info("Slim-Skeleton '/projects/id' route");

$id = (int) $args['id'];

$data = null;

$result = json_encode([
"jsonapi" => [
"version" => "1.0",
],
"data" => $data,
]);

return $result;
});

$app->patch('/v1/projects/{id:\d+}', function (Request $request, Response $response, array $args) {
$this->logger->info("Slim-Skeleton '/projects/id' route");

$id = (int) $args['id'];

$data = [
"data" => [
"type" => "project",
"id" => 2,
"attributes" => [
"name" => "Project 2 modified",
"description" => "Description Project 2 modified",
],
],
];

$newResponse = $response->withJson($data);

return $newResponse;
});

$app->delete('/v1/projects/{id:\d+}', function (Request $request, Response $response, array $args) {
$this->logger->info("Slim-Skeleton '/projects/id' route");

$id = (int) $args['id'];

$newResponse = $response->withJson([
"jsonapi" => [
"version" => "1.0",
],
]);

return $newResponse;
});
 */