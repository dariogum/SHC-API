<?php

namespace App\Classes;

use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class Patient
{
    private $logger;
    private $table = "patients";
    private $type = "patient";
    private $url = "patients";

    public function __construct(LoggerInterface $logger, Builder $table)
    {
        $this->logger = $logger;
        $this->table = $table;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        switch ($request->getMethod()) {
            case "GET":
                return $this->read($request, $response, $args);
                break;
            case "POST":
                return $this->create($request, $response, $args);
                break;
            case "PUT":
                return $this->update($request, $response, $args);
                break;
            case "DELETE":
                return $this->delete($request, $response, $args);
                break;
        }
        return $response->withJson([
            "data" => null,
            "jsonapi" => [
                "version" => "2.0",
            ],
            "links" => [
                "self" => "/" . $this->url,
            ],
            "meta" => [
                "copyright" => "Copyright 2019 DarioGUM",
                "authors" => [
                    "Darío Germán Uberti Manassero",
                ],
            ],
        ]);
    }

    private function read($request, $response, $args)
    {
        if (array_key_exists("id", $args)) {
            $resource = $this->table->find($args["id"]);
            if ($resource) {
                return $response->withJson($this->parse($resource), 200);
            }
            return $response->withJson($resource, 404);
        } else {
            $resources = $this->table->get();
            return $response->withJson($this->parse($resources), 200);
        }
    }

    private function create($request, $response, $args)
    {}

    private function update($request, $response, $args)
    {
        if (array_key_exists("id", $args)) {
            $body = $request->getParsedBody();
            $body['data']['attributes']['lastname'] = mb_convert_case(mb_strtolower($body['data']['attributes']['lastname']), MB_CASE_TITLE, "UTF-8");
            $body['data']['attributes']['name'] = mb_convert_case(mb_strtolower($body['data']['attributes']['name']), MB_CASE_TITLE, "UTF-8");
            $updated = $this->table->where('id', $args["id"])->update($body["data"]["attributes"]);
            if ($updated) {
                return $this->read($request, $response, $args);
            }
        }
    }

    private function delete($request, $response, $args)
    {}

    private function parse($data)
    {
        if (is_iterable($data)) {
            $parsedData = [];
            foreach ($data as $attributes) {
                $parsedData[] = $this->parseResource($attributes);
            }
        } else {
            $parsedData = $this->parseResource($data);
        }
        return [
            "data" => $parsedData,
        ];
    }

    private function parseResource($attributes)
    {
        $id = $attributes->id;
        unset($attributes->id);
        return [
            "attributes" => $attributes,
            "id" => $id,
            "relationships" => [],
            "type" => $this->type,
            "links" => [
                "self" => "/" . $this->url . "/" . $id,
            ],
        ];
    }
}
