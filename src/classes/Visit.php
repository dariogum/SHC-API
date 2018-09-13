<?php

namespace App\Classes;

use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class Visit {
	private $logger;
	protected $table;
	protected $filesTable;

	public function __construct(
		LoggerInterface $logger,
		Builder $table,
		Builder $filesTable
	) {
		$this->logger = $logger;
		$this->table = $table;
		$this->filesTable = $filesTable;
	}

	public function __invoke(Request $request, Response $response, $args) {
		$method = $request->getMethod();

		$result = [];

		switch ($method) {
		case 'GET':
			$newResponse = $this->get($request, $response, $args);
			break;

		case 'POST':
			if (array_key_exists("id", $args)) {
				$newResponse = $this->addFiles($request, $response, $args);
			} else {
				$newResponse = $this->add($request, $response, $args);
			}
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

	private function visitToJson($visit, $withVisitFiles = true) {
		$visitData = [
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
		];

		if ($withVisitFiles) {
			$files = $this->filesTable->where('visit', $visit->id)->get();
			$visitData["relationships"]["files"] = [];

			foreach ($files as $file) {
				$fileData = [
					"links" => [
						"self" => "/files/" . $file->name,
					],
					"data" => [
						"type" => "file",
						"id" => $file->id,
						"attributes" => [
							"name" => $file->name,
						],
					],
				];

				$visitData["relationships"]["files"][] = $fileData;
			}
		}

		return $visitData;
	}

	private function get(Request $request, Response $response, $args) {
		$result["links"] = [
			"self" => "/visits",
		];

		if (array_key_exists("id", $args)) {
			return $this->getOne($args['id'], $response);
		} else {
			$this->logger->info("Get visits");
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
			/** Example: /visits?filter=[-]attr1:string[,[-]attr2:string,..] **/
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

			$visits = $query->get();
			$data = [];

			foreach ($visits as $visit) {
				$data[] = $this->visitToJson($visit);
			}

			$result["data"] = $data;
		}

		$newResponse = $response->withJson($result);

		return $newResponse;
	}

	public function getOne($id, $response) {
		$result["links"] = [
			"self" => "/visits/" . $id,
		];

		$this->logger->info("Get a visit");

		$visit = $this->table->find($id);
		$data = [];

		if ($visit) {
			$data = $this->visitToJson($visit);
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

		$result["data"] = $data;

		$newResponse = $response->withJson($result);

		return $newResponse;
	}

	private function add(Request $request, Response $response, $args) {
		$this->logger->info("Add a visit");

		$body = $request->getParsedBody();

		$attributes = $body['data']['attributes'];

		$visitId = $this->table->insertGetId($attributes);

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
			"data" => $body["data"],
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

	private function addFiles(Request $request, Response $response, $args) {
		$this->logger->info("Add files to a visit");

		$result = [];
		$directory = __DIR__ . '/../../uploads';
		$getUploadedFiles = $request->getUploadedFiles();
		$visitId = null;

		if (array_key_exists("id", $args)) {
			$visitId = $args["id"];
			$visit = $this->table->find($visitId);
			if (!$visit) {
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

		foreach ($getUploadedFiles['visitFiles'] as $uploadedFile) {
			if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
				$filename = $this->moveUploadedFile($directory, $uploadedFile);
				$fileId = $this->filesTable->insertGetId([
					"visit" => $visitId,
					"name" => $filename,
				]);
				$result[] = ["id" => $fileId, "filename" => $filename, "uploaded" => true];
			} else {
				$result[] = ["id" => null, "filename" => $uploadedFile->name, "uploaded" => false];
			}
		}

		$newResponse = $response->withJson($result);

		return $newResponse;
	}

	/**
	 * Moves the uploaded file to the upload directory and assigns it a unique name
	 * to avoid overwriting an existing uploaded file.
	 *
	 * @param string $directory directory to which the file is moved
	 * @param UploadedFile $uploaded file uploaded file to move
	 * @return string filename of moved file
	 */
	private function moveUploadedFile($directory, \Slim\Http\UploadedFile $uploadedFile) {
		$extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
		$basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
		$filename = sprintf('%s.%0.8s', $basename, $extension);

		$uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

		return $filename;
	}
}