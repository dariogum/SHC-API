<?php

namespace App\Classes;

use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class Patient
{
    private $logger;
    private $resourceType = "patient";
    private $table;
    private $url = "patients";
    private $visit;

    public function __construct(LoggerInterface $logger, Builder $table, Visit $visit)
    {
        $this->logger = $logger;
        $this->table = $table;
        $this->visit = $visit;
    }

    private function get(Request $request, Response $response, $args)
    {
        $newResponse = null;

        if (array_key_exists("id", $args)) {
            $visits = strpos($request->getUri()->getPath(), "visits");
            if ($visits === false) {
                $resource = $this->readById($args["id"]);
                if (!$resource) {
                    $newResponse = $response->withJson($this->resourceNotFound(), 404);
                } else {
                    $newResponse = $response->withJson($resource);
                }
            } else {
                $newResponse = $response->withJson($this->visit->readByPatient($args["id"]));
            }
        } else {
            $search = strpos($request->getUri()->getPath(), "search");
            if ($search === false) {
                $collection = $this->readAll($request);
                if (!$collection) {
                    $newResponse = $response->withJson($this->badRequest(), 400);
                } else {
                    $newResponse = $response->withJson($collection);
                }
            } elseif (array_key_exists("terms", $args) && strlen(trim($args["terms"]))) {
                $newResponse = $response->withJson($this->search($args["terms"]));
            }
        }
        return $newResponse;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
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

    private function resourceNotFound()
    {
        return [
            "errors" => [
                "id" => "404",
                "status" => "404 Not Found",
                "title" => $this->resourceType . " not found",
            ],
        ];
    }

    private function resourceNotProcessable()
    {
        return [
            "errors" => [
                "id" => "422",
                "status" => "422 Unprocessable Entity",
                "title" => "Data format not correct",
            ],
        ];
    }

    private function badRequest()
    {
        return [
            "errors" => [
                "id" => "400",
                "status" => "400 Bad Request",
                "title" => "Query parameters format not correct",
            ],
        ];
    }

    public function create($body)
    {
        $this->logger->info("Creating a patient");

        $body['data']['attributes']['lastname'] = mb_convert_case(mb_strtolower($body['data']['attributes']['lastname']), MB_CASE_TITLE, "UTF-8");
        $body['data']['attributes']['name'] = mb_convert_case(mb_strtolower($body['data']['attributes']['name']), MB_CASE_TITLE, "UTF-8");

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

    public function readAll($request)
    {
        $this->logger->info("Getting all the patients");

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
                    return false;
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
                    return false;
                }
                if (substr($parameters[0], 0, 1) === '-') {
                    $query = $query->where(ltrim($parameters[0], '-'), 'like', $parameters[1]);
                } else {
                    $query = $query->orWhere($parameters[0], 'like', $parameters[1]);
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

        /** Getting the patients **/
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

    public function readById($id)
    {
        $this->logger->info("Getting a patient");
        $query = clone $this->table;
        $resourceAttributes = $query->find($id);
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
                    "visits" => $this->visit->readByPatient($id),
                ],
            ],
            "links" => [
                "self" => $this->url . "/" . $id,
                "related" => $this->url . "/" . $id . "/visits",
            ],
        ];
        return $resource;
    }

    public function update($id, $body)
    {
        $this->logger->info("Updating a patient");

        $body['data']['attributes']['lastname'] = mb_convert_case(mb_strtolower($body['data']['attributes']['lastname']), MB_CASE_TITLE, "UTF-8");
        $body['data']['attributes']['name'] = mb_convert_case(mb_strtolower($body['data']['attributes']['name']), MB_CASE_TITLE, "UTF-8");

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
                    "visits" => $this->visit->readByPatient($id),
                ],
            ],
            "links" => [
                "self" => $this->url . "/" . $id,
                "related" => $this->url . "/" . $id . "/visits",
            ],
        ];
        return $resource;
    }

    public function delete($id)
    {
        $this->logger->info("Deleting a patient");
        return $this->table->where('id', $id)->delete();
    }

    public function search($terms)
    {
        $this->logger->info("Searching patients");

        $searchTerms = explode(' ', $terms);
        $terms = '';
        foreach ($searchTerms as $term) {
            $terms .= $term . '%';
        }

        $query = $this->table;
        $query = $query->whereRaw("TRIM(CONCAT(LOWER(name),LOWER(lastname))) LIKE '%" . $terms . "'");
        $query = $query->orWhereRaw("TRIM(CONCAT(LOWER(lastname),LOWER(name))) LIKE '%" . $terms . "'");
        $query = $query->orderByRaw("TRIM(CONCAT(LOWER(lastname),LOWER(name)))");

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
}
