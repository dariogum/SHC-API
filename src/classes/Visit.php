<?php

namespace App\Classes;

use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class Visit {
	private $logger;
	protected $table;

	public function __construct(
		LoggerInterface $logger,
		Builder $table
	) {
		$this->logger = $logger;
		$this->table = $table;
	}

	public function __invoke(Request $request, Response $response, $args) {
		$method = $request->getMethod();

		$result = [];

		switch ($method) {
		case 'GET':
			$newResponse = $this->get($request, $response, $args);
			break;

		case 'POST':
			$newResponse = $this->add($request, $response, $args);
			break;

		case 'PATCH':
			$newResponse = $this->update($request, $response, $args);
			break;

		case 'DELETE':
			$newResponse = $this->delete($request, $response, $args);
			break;

		default:
			break;
		}

		return $newResponse;
	}

	private function get(Request $request, Response $response, $args) {
		$result = [];
		$data = [];

		if (array_key_exists("id", $args)) {

			$result = $this->getOne($args['id']);

		} else {
			$this->logger->info("Get visits");

			$query = $this->table;

			$sort = $request->getParam('sort');
			if ($sort) {
				$columns = explode(',', $sort);
				foreach ($columns as $column) {
					$column = explode('-', $column);
					if (count($column) == 2) {
						$query = $query->orderBy($column[1], 'desc');
					} elseif (count($column) == 1) {
						$query = $query->orderBy($column[0], 'asc');
					} else {
						$errors = [
							"errors" => [
								"id" => "400",
								"status" => "400 Bad Request",
								"title" => "Sort format not correct",
							],
						];
						$newResponse = $response->withJson($errors, 400);
						return $newResponse;
					}
				}
			}

			$filter = $request->getParam('filter');
			if ($filter) {
				$columns = explode(',', $filter);
				foreach ($columns as $column) {
					$column = explode(':', $column);
					if (count($column) != 2) {
						$errors = [
							"errors" => [
								"id" => "400",
								"status" => "400 Bad Request",
								"title" => "Filter format not correct",
							],
						];
						$newResponse = $response->withJson($errors, 400);
						return $newResponse;
					}
					$query = $query->where($column[0], 'like', '%' . $column[1] . '%');
				}
			}

			$page = $request->getParam('page');
			if ($page) {
				switch ($page) {
				case 'first':
					$query = $query->take(10);
					break;

				default:
					$limits = explode(',', $page);
					if (count($limits) != 2) {
						$errors = [
							"errors" => [
								"id" => "400",
								"status" => "400 Bad Request",
								"title" => "Page format not correct",
							],
						];
						$newResponse = $response->withJson($errors, 400);
						return $newResponse;
					}
					$query = $query->skip($limits[0])->take($limits[1]);
					break;
				}
			}

			$visits = $query->get();

			foreach ($visits as $visit) {
				$data[] = [
					"type" => "visit",
					"id" => $visit->id,
					"attributes" => [
						"name" => $visit->name,
						"description" => $visit->description,
						"type" => $visit->type,
					],
				];
			}

			$result = [
				"links" => [
					"self" => "/visits",
					"first" => "/visits?page=first",
					"last" => "/visits?page=last",
					"prev" => "/visits?page=prev",
					"next" => "/visits?page=next",
				],
				"data" => $data,
			];
		}

		$newResponse = $response->withJson($result);

		return $newResponse;
	}

	public function getOne($id) {
		$this->logger->info("Get a visit");

		$visit = $this->table->find($id);

		$data = [
			"type" => "visit",
			"id" => $id,
			"attributes" => [
				"name" => $visit->name,
				"description" => $visit->description,
				"type" => $visit->type,
			],
		];

		$result = [
			"links" => [
				"self" => "/visits/" . $id,
			],
			"data" => $data,
		];

		return $result;
	}

	private function add(Request $request, Response $response, $args) {
		$this->logger->info("Add a visit");

		$body = $request->getParsedBody();

		$attributes = $body['data']['attributes'];

		$statusId = $this->table->insertGetId($attributes);

		if (!$visitId) {
			$errors = [
				"errors" => [
					"id" => "422",
					"status" => "422 Unprocessable Entity",
					"title" => "Data format not correct",
				],
			];
			$newResponse = $response->withJson($errors, 422);
			return $newResponse;
		}

		$body['data']['id'] = $visitId;

		$result = [
			"links" => [
				"self" => "/visits/" . $visitId,
			],
			"data" => $body,
		];

		$newResponse = $response->withJson($result, 201);

		return $newResponse;
	}

	private function update(Request $request, Response $response, $args) {
		$this->logger->info("Update a visit");

		$result = null;

		if (array_key_exists("id", $args)) {

			$id = $args['id'];

			$body = $request->getParsedBody();

			$attributes = $body['data']['attributes'];

			$visit = $this->table->find($id);

			if ($visit && $attributes) {
				$success = $this->table->where('id', $id)->update($attributes);
			} else {
				$errors = [
					"errors" => [
						"id" => "404",
						"status" => "404 Not Found",
						"title" => "ID Visit not found",
					],
				];
				$newResponse = $response->withJson($errors, 404);
				return $newResponse;
			}

			$visit = $this->table->find($id);

			$result = [
				"links" => [
					"self" => "/visits/" . $id,
				],
				"data" => $visit,
			];

		}

		$newResponse = $response->withJson($result);

		return $newResponse;
	}

	private function delete(Request $request, Response $response, $args) {
		$this->logger->info("Delete a visit");

		$result = null;

		if (array_key_exists("id", $args)) {

			$id = $args['id'];

			$visit = $this->table->find($id);

			if ($visit) {
				$result = $this->table->where('id', $id)->delete();
			} else {
				$errors = [
					"errors" => [
						"id" => "404",
						"status" => "404 Not Found",
						"title" => "ID Visit not found",
					],
				];
				$newResponse = $response->withJson($errors, 404);
				return $newResponse;
			}

		}

		$newResponse = $response->withJson($result);

		return $newResponse;
	}
}