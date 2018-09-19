<?php

namespace App\Classes;

use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Http\Stream;

class File {
	private $logger;
	private $resourceType = "file";
	private $table;
	private $uploadsPath = __DIR__ . '/../../uploads/';
	private $url = "files";

	public function __construct(LoggerInterface $logger, Builder $table) {
		$this->logger = $logger;
		$this->table = $table;
	}

	private function get(Request $request, Response $response, $args) {
		$newResponse = null;

		if (array_key_exists("id", $args)) {
			$resource = $this->readById($args["id"]);
			if (!$resource) {
				$newResponse = $response->withJson($this->resourceNotFound(), 404);
			} else {
				$newResponse = $response->withBody($resource["file_stream"])
					->withHeader('Content-Disposition', 'inline')
					->withHeader('Content-Type', mime_content_type($resource["path"]))
					->withHeader('Content-Length', filesize($resource["path"]));
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
			$resource = $this->create($request->getParsedBody(), $request->getUploadedFiles());
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
				"title" => $this->resourceType . "not found",
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

	public function create($getParsedBody, $getUploadedFiles) {
		$this->logger->info("Creating a file");

		$data = [];

		foreach ($getUploadedFiles['visitFiles'] as $uploadedFile) {
			if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
				//\Cloudinary\Uploader::upload("sample.jpg", array("crop"=>"limit", "tags"=>"samples", "width"=>3000, "height"=>2000));
				$filename = $this->moveUploadedFile($this->uploadsPath, $uploadedFile);
				$id = $this->table->insertGetId([
					"visit" => $getParsedBody["visit"],
					"name" => $filename,
				]);

				$resource = [
					"data" => [
						"attributes" => [
							"name" => $filename,
						],
						"id" => $id,
						"type" => "file",
					],
					"links" => [
						"self" => "/" . $this->url . "/" . $id,
					],
				];
			}
			$data[] = $resource;
		}

		$collection = [
			"data" => $data,
			"links" => [
				"self" => "visits/" . $getParsedBody["visit"] . "/files",
			],
		];
		return $collection;
	}

	public function readAll($request) {
		$this->logger->info("Getting all the files");

		$query = $this->table;

		/** Sorting **/
		/** Example: /files?sort=[-]attr1[,[-]attr2,..] **/
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
		/** Example: /files?filter=[-]attr1:string[,[-]attr2:string,..] **/
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

		/** Getting the files **/
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
		$this->logger->info("Getting a file");

		$resourceAttributes = $this->table->find($id);
		if (!$resourceAttributes) {
			return false;
		}

		$path = $this->uploadsPath . $resourceAttributes->name;
		$fileData = fopen($path, 'rb');
		if (!$fileData) {
			return false;
		}
		$file_stream = new Stream($fileData);

		return [
			"path" => $path,
			"file_stream" => $file_stream,
		];
	}

	public function readByVisit($visitId) {
		$this->logger->info("Getting all the files of a visit");

		$data = [];
		$filesQueryBuilder = clone $this->table;
		$resources = $filesQueryBuilder->where('visit', $visitId)->get();

		foreach ($resources as $resource) {
			$id = $resource->id;
			unset($resource->id);
			$relatedResource = [
				"data" => [
					"attributes" => $resource,
					"id" => $id,
					"type" => $this->resourceType,
				],
				"links" => [
					"self" => "/" . $this->url . "/" . $id,
				],
			];
			$data[] = $relatedResource;
		}
		$collection = [
			"data" => $data,
			"links" => [
				"self" => "visits/" . $visitId . "/" . $this->url,
			],
		];
		return $collection;
	}

	public function update($id, $body) {
		$this->logger->info("Updating a file");

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
			],
			"links" => [
				"self" => $this->url . "/" . $id,
			],
		];
		return $resource;
	}

	public function delete($id) {
		$this->logger->info("Deleting a file");

		$resource = $this->table->find($id);
		if ($resource) {
			$path = $this->uploadsPath . $resource->name;
			if (is_file($path)) {
				unlink($path);
			}
			return $this->table->where('id', $id)->delete();
		}
		return false;
	}
}