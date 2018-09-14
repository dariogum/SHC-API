<?php

namespace App\Classes;

use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Http\Stream;

class File {
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

	public function get(Request $request, Response $response, $args) {
		$this->logger->info("Get a file");

		$file = $this->table->find($args['id']);
		$path = __DIR__ . '/../../uploads/';

		if ($file) {
			$path = $path . $file->name;
			$fileData = fopen($path, 'rb');
			$file_stream = new Stream($fileData);
		} else {
			$errors = [
				"errors" => [
					"id" => "404",
					"status" => "404 Not Found",
					"title" => "ID File not found",
				],
			];
			$newResponse = $response->withJson($errors, 404);
			return $newResponse;
		}

		return $response->withBody($file_stream)
			->withHeader('Content-Disposition', 'inline')
			->withHeader('Content-Type', mime_content_type($path))
			->withHeader('Content-Length', filesize($path));
	}

	private function delete(Request $request, Response $response, $args) {
		$this->logger->info("Delete a file");

		$result = null;

		if (array_key_exists("id", $args)) {

			$id = $args['id'];

			$file = $this->table->find($id);

			if ($file) {
				$result = $this->table->where('id', $id)->delete();
			} else {
				$errors = [
					"errors" => [
						"id" => "404",
						"status" => "404 Not Found",
						"title" => "ID File not found",
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