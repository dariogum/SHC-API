<?php

namespace App\Classes;

use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class Patient {
	private $logger;
	protected $table;
	protected $visitsTable;

	public function __construct(
		LoggerInterface $logger,
		Builder $table,
		Builder $visitsTable
	) {
		$this->logger = $logger;
		$this->table = $table;
		$this->visitsTable = $visitsTable;
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
		$result["links"] = [
			"self" => "/patients",
		];

		$data = [];

		if (array_key_exists("id", $args)) {
			return $this->getOne($args['id'], $response);
		} else {
			$this->logger->info("Get patients");
			$query = $this->table;

			/** Sorting **/
			/** Example: /patients?sort=[-]attr1[,[-]attr2,..] **/
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

			/** Filtering **/
			/** Example: /patients?filter=[-]attr1:string[,[-]attr2:string,..] **/
			$filter = $request->getParam('filter');
			if ($filter) {
				$terms = explode(',', $filter);
				foreach ($terms as $term) {
					$parameters = explode(':', $term);
					if (sizeof($parameters) != 2) {
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

			$patients = $query->get();

			foreach ($patients as $patient) {
				$data[] = [
					"type" => "patient",
					"id" => $patient->id,
					"attributes" => [
						"lastname" => $patient->lastname,
						"name" => $patient->name,
						"birthday" => $patient->birthday,
						"gender" => $patient->gender,
						"docType" => $patient->docType,
						"doc" => $patient->doc,
						"phone1" => $patient->phone1,
						"phone2" => $patient->phone2,
						"country" => $patient->country,
						"state" => $patient->state,
						"city" => $patient->city,
						"street" => $patient->street,
						"number" => $patient->number,
						"floor" => $patient->floor,
						"apartment" => $patient->apartment,
						"socialSecurity1" => $patient->socialSecurity1,
						"socialSecurity1Number" => $patient->socialSecurity1Number,
						"socialSecurity2" => $patient->socialSecurity2,
						"socialSecurity2Number" => $patient->socialSecurity2Number,
						"birthType" => $patient->birthType,
						"rhFactor" => $patient->rhFactor,
						"apgar" => $patient->apgar,
						"gestationalAge" => $patient->gestationalAge,
						"comments" => $patient->comments,
						"father" => $patient->father,
						"mother" => $patient->mother,
						"brothers" => $patient->brothers,
						"others" => $patient->others,
					],
				];

				$visitsQuery = $this->visitsTable;
				$visitsQuery = $visitsQuery->where('patient', $patient->id);
				$visits = $visitsQuery->get();

				$data[sizeof($data) - 1]["relationships"]["visits"] = [];

				foreach ($visits as $visit) {
					$data[sizeof($data) - 1]["relationships"]["visits"][] = [
						"links" => [
							"self" => "/visits/" . $visit->id,
						],
						"data" => [
							"type" => "visit",
							"id" => $visit->id,
							"attributes" => [
								"patient" => $visit->patient,
								"date" => $visit->date,
								"weight" => $visit->weight,
								"height" => $visit->height,
								"perimeter" => $visit->perimeter,
								"diagnosis" => $visit->diagnosis,
								"treatment" => $visit->treatment,
							],
						],
					];
				}
			}

			$result["data"] = $data;
		}

		$newResponse = $response->withJson($result);

		return $newResponse;
	}

	public function getOne($id, $response) {
		$result["links"] = [
			"self" => "/patients/" . $id,
		];

		$data = [];

		$this->logger->info("Get a patient");

		$patient = $this->table->find($id);

		if ($patient) {
			$data = [
				"type" => "patient",
				"id" => $patient->id,
				"attributes" => [
					"lastname" => $patient->lastname,
					"name" => $patient->name,
					"birthday" => $patient->birthday,
					"gender" => $patient->gender,
					"docType" => $patient->docType,
					"doc" => $patient->doc,
					"phone1" => $patient->phone1,
					"phone2" => $patient->phone2,
					"country" => $patient->country,
					"state" => $patient->state,
					"city" => $patient->city,
					"street" => $patient->street,
					"number" => $patient->number,
					"floor" => $patient->floor,
					"apartment" => $patient->apartment,
					"socialSecurity1" => $patient->socialSecurity1,
					"socialSecurity1Number" => $patient->socialSecurity1Number,
					"socialSecurity2" => $patient->socialSecurity2,
					"socialSecurity2Number" => $patient->socialSecurity2Number,
					"birthType" => $patient->birthType,
					"bloodType" => $patient->bloodType,
					"rhFactor" => $patient->rhFactor,
					"apgar" => $patient->apgar,
					"gestationalAge" => $patient->gestationalAge,
					"comments" => $patient->comments,
					"father" => $patient->father,
					"mother" => $patient->mother,
					"brothers" => $patient->brothers,
					"others" => $patient->others,
				],
			];

			$visitsQuery = $this->visitsTable;
			$visitsQuery = $visitsQuery->where('patient', $patient->id);
			$visits = $visitsQuery->get();

			foreach ($visits as $visit) {
				$data["relationships"]["visits"][] = [
					"links" => [
						"self" => "/visits/" . $visit->id,
					],
					"data" => [
						"type" => "visit",
						"id" => $visit->id,
						"attributes" => [
							"patient" => $visit->patient,
							"date" => $visit->date,
							"weight" => $visit->weight,
							"height" => $visit->height,
							"perimeter" => $visit->perimeter,
							"diagnosis" => $visit->diagnosis,
							"treatment" => $visit->treatment,
						],
					],
				];
			}
		} else {
			$errors = [
				"errors" => [
					"id" => "404",
					"status" => "404 Not Found",
					"title" => "ID Patient not found",
				],
			];
			$newResponse = $response->withJson($errors, 404);
			return $newResponse;
		}

		$result["data"] = $data;

		$newResponse = $response->withJson($result);

		return $newResponse;
	}

	private function add(Request $request, Response $response, $args) {
		$this->logger->info("Add a patient");

		$body = $request->getParsedBody();

		$attributes = $body['data']['attributes'];

		$patientId = $this->table->insertGetId($attributes);

		if (!$patientId) {
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

		$body['data']['id'] = $patientId;

		$result = [
			"links" => [
				"self" => "/patients/" . $patientId,
			],
			"data" => $body["data"],
		];

		$newResponse = $response->withJson($result, 201);

		return $newResponse;
	}

	private function update(Request $request, Response $response, $args) {
		$this->logger->info("Update a patient");

		$result = null;

		if (array_key_exists("id", $args)) {

			$id = $args['id'];

			$body = $request->getParsedBody();

			$attributes = $body['data']['attributes'];

			$patient = $this->table->find($id);

			if ($patient && $attributes) {
				$success = $this->table->where('id', $id)->update($attributes);
			} else {
				$errors = [
					"errors" => [
						"id" => "404",
						"status" => "404 Not Found",
						"title" => "ID Patient not found",
					],
				];
				$newResponse = $response->withJson($errors, 404);
				return $newResponse;
			}

			$patient = $this->table->find($id);

			$result = [
				"links" => [
					"self" => "/patients/" . $id,
				],
				"data" => $patient,
			];

		}

		$newResponse = $response->withJson($result);

		return $newResponse;
	}

	private function delete(Request $request, Response $response, $args) {
		$this->logger->info("Delete a patient");

		$result = null;

		if (array_key_exists("id", $args)) {

			$id = $args['id'];

			$patient = $this->table->find($id);

			if ($patient) {
				$result = $this->table->where('id', $id)->delete();
			} else {
				$errors = [
					"errors" => [
						"id" => "404",
						"status" => "404 Not Found",
						"title" => "ID Patient not found",
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