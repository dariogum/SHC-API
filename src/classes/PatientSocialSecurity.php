<?php

namespace App\Classes;

use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class PatientSocialSecurity
{
    private $logger;
    private $table = "patients_social_securities";
    private $type = "patientSocialSecurity";
    private $url = "patientssocialsecurity";

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
            
    private function read($request, $response, $args, $responseCode = 200)
    {
        $data = null;
        if (array_key_exists("patient", $args)) {
            $data = $this->table->where('patient', $args["patient"])->get();
        }
        if ($data) {
            return $response->withJson($this->parse($data), $responseCode);
        }
        return $response->withJson(null, 404);
    }

    private function create($request, $response, $args)
    {
        $body = $request->getParsedBody();
        $created = $this->table->insertGetId($body['data']['attributes']);
        if ($created) {
            $args["id"] = $created;
            return $this->read($request, $response, $args, 201);
        }
        return $response->withJson(null, 409);
    }

    private function update($request, $response, $args)
    {
        if (array_key_exists("id", $args)) {
            $body = $request->getParsedBody();
            $updated = $this->table->where('id', $args["id"])->update($body["data"]["attributes"]);
            if ($updated) {
                return $this->read($request, $response, $args);
            }
        }
        return $response->withJson(null, 409);
    }

    private function delete($request, $response, $args)
    {
        if (array_key_exists("id", $args)) {
            if ($this->table->where('id', $args["id"])->delete()) {
                return $response->withJson(null, 200);
            }
            return $response->withJson(null, 404);
        }
    }

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
