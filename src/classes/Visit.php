<?php

namespace App\Classes;

use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class Visit {
	private $file;
	private $logger;
	private $resourceType = "visit";
	private $table;
	private $url = "visits";

	public function __construct(LoggerInterface $logger, Builder $table, File $file) {
		$this->logger = $logger;
		$this->table = $table;
		$this->file = $file;
	}

	private function get(Request $request, Response $response, $args) {
		$newResponse = null;

		if (array_key_exists("id", $args)) {
			$files = strpos($request->getUri()->getPath(), "files");
			if ($files !== false) {
				$newResponse = $response->withJson($this->file->readByVisit($args["id"]));
			} else {
				$resource = $this->readById($args["id"]);
				if (!$resource) {
					$newResponse = $response->withJson($this->resourceNotFound(), 404);
				} else {
					$newResponse = $response->withJson($resource);
				}
			}
		} else {
			$collection = $this->readAll($request);
			if (!$collection) {
				$newResponse = $response->withJson($this->badRequest(), 400);
			} else {
				$newResponse = $response->withJson($collection);
			}
		}
		return $newResponse;
	}

	public function __invoke(Request $request, Response $response, $args) {
		$newResponse = null;

		switch ($request->getMethod()) {
		case "GET":
			$newResponse = $this->get($request, $response, $args);
			break;

		case "POST":
			$resource = $this->create($request->getParsedBody());
			if (!$resource) {
				$newResponse = $response->withJson($this->resourceNotProcessable(), 422);
			} else {
				$newResponse = $response->withJson($resource, 201);
			}
			break;

		case "PATCH":
			if (array_key_exists("id", $args)) {
				$resource = $this->update($args["id"], $request->getParsedBody());
				if (!$resource) {
					$newResponse = $response->withJson($this->resourceNotProcessable(), 422);
				} else {
					$newResponse = $response->withJson($resource);
				}
			}
			break;

		case "DELETE":
			if (array_key_exists("id", $args)) {
				$status = $this->delete($args["id"]);
				$newResponse = $response->withJson($status);
			}
			break;

		default:
			break;
		}

		return $newResponse;
	}

	private function resourceNotFound() {
		return [
			"errors" => [
				"id" => "404",
				"status" => "404 Not Found",
				"title" => $this->resourceType . " not found",
			],
		];
	}

	private function resourceNotProcessable() {
		return [
			"errors" => [
				"id" => "422",
				"status" => "422 Unprocessable Entity",
				"title" => "Data format not correct",
			],
		];
	}

	private function badRequest() {
		return [
			"errors" => [
				"id" => "400",
				"status" => "400 Bad Request",
				"title" => "Query parameters format not correct",
			],
		];
	}

	public function create($body) {
		$this->logger->info("Creating a visit");

		$id = $this->table->insertGetId($body['data']['attributes']);
		if (!$id) {
			return false;
		}

		$resource = [
			"data" => [
				"attributes" => $body['data']['attributes'],
				"id" => $id,
				"type" => $this->resourceType,
			],
			"links" => [
				"self" => $this->url . "/" . $id,
				"related" => $this->url . "/" . $id . "/visits",
			],
		];
		return $resource;
	}

	public function readAll($request) {
		$this->logger->info("Getting all the visits");

		$query = $this->table;

		/** Sorting **/
		/** Example: /visits?sort=[-]attr1[,[-]attr2,..] **/
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
					return false;
				}
			}
		}

		/** Filtering **/
		/** Example: /visits?filter=[-]attr1:string[,[-]attr2:string,..] **/
		$filter = $request->getParam('filter');
		if ($filter) {
			$terms = explode(',', $filter);
			foreach ($terms as $term) {
				$parameters = explode(':', $term);
				if (sizeof($parameters) != 2) {
					return false;
				}
				if (substr($parameters[0], 0, 1) === '-') {
					$query = $query->where(ltrim($parameters[0], '-'), 'like', '%' . $parameters[1] . '%');
				} else {
					$query = $query->orWhere($parameters[0], 'like', '%' . $parameters[1] . '%');
				}
			}
		}

		/** Pagination **/
		/** ToDo **/
		$page = $request->getParam('page');
		if ($page) {
			switch ($page) {
			case 'first':
				$query = $query->take(10);
				break;

			default:
				$limits = explode(',', $page);
				if (count($limits) != 2) {
					return false;
				}
				$query = $query->skip($limits[0])->take($limits[1]);
				break;
			}
		}

		/** Getting the visits **/
		$data = [];
		$resources = $query->get();

		foreach ($resources as $resource) {
			$id = $resource->id;
			unset($resource->id);
			$resourceData = [
				"attributes" => $resource,
				"id" => $id,
				"type" => $this->resourceType,
			];
			$data[] = $resourceData;
		}
		$collection = [
			"data" => $data,
			"links" => [
				"self" => $this->url,
			],
		];
		return $collection;
	}

	public function readById($id) {
		$this->logger->info("Getting a visit");

		$resourceAttributes = $this->table->find($id);
		if (!$resourceAttributes) {
			return false;
		}
		unset($resourceAttributes->id);

		$resource = [
			"data" => [
				"attributes" => $resourceAttributes,
				"id" => $id,
				"type" => $this->resourceType,
				"relationships" => [
					"files" => $this->file->readByVisit($id),
				],
			],
			"links" => [
				"self" => $this->url . "/" . $id,
				"related" => $this->url . "/" . $id . "/files",
			],
		];
		return $resource;
	}

	public function readByPatient($patientId) {
		$this->logger->info("Getting all the visits of a patient");

		$data = [];
		$visitsQueryBuilder = clone $this->table;
		$resources = $visitsQueryBuilder->where('patient', $patientId)->orderBy('date', 'desc')->get();

		foreach ($resources as $resource) {
			$id = $resource->id;
			unset($resource->id);
			$relatedResource = [
				"data" => [
					"attributes" => $resource,
					"id" => $id,
					"type" => $this->resourceType,
					"relationships" => [
						"files" => $this->file->readByVisit($id),
					],
				],
				"links" => [
					"self" => $this->url . "/" . $id,
					"related" => $this->url . "/" . $id . "/files",
				],
			];
			$data[] = $relatedResource;
		}
		$collection = [
			"data" => $data,
			"links" => [
				"self" => "patients/" . $patientId . "/" . $this->url,
			],
		];
		return $collection;
	}

	public function update($id, $body) {
		$this->logger->info("Updating a visit");

		$status = $this->table->where('id', $id)->update($body["data"]["attributes"]);
		if (!$status) {
			return false;
		}
		$resourceAttributes = $this->table->find($id);
		if (!$resourceAttributes) {
			return false;
		}
		unset($resourceAttributes->id);

		$resource = [
			"data" => [
				"attributes" => $resourceAttributes,
				"id" => $id,
				"type" => $this->resourceType,
				"relationships" => [
					"files" => $this->file->readByVisit($id),
				],
			],
			"links" => [
				"self" => $this->url . "/" . $id,
				"related" => $this->url . "/" . $id . "/files",
			],
		];
		return $resource;
	}

	public function delete($id) {
		$this->logger->info("Deleting a visit");
		return $this->table->where('id', $id)->delete();
	}
}