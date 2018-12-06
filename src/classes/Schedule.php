<?php

namespace App\Classes;

use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class Schedule
{
    private $_appointments;
    private $_logger;
    private $_pattient;
    private $_resourceType = "schedule";
    private $_schedulesDays;
    private $_schedulesDaysHours;
    private $_schedulesProfessionals;
    private $_table;
    private $_url = "schedules";
    private $_user;

    public function __construct(
        Builder $appointments,
        LoggerInterface $logger,
        Patient $patient,
        Builder $table,
        Builder $schedulesDays,
        Builder $schedulesDaysHours,
        Builder $schedulesProfessionals,
        User $user
    ) {
        $this->_appointments = $appointments;
        $this->_logger = $logger;
        $this->_patient = $patient;
        $this->_schedulesDays = $schedulesDays;
        $this->_schedulesDaysHours = $schedulesDaysHours;
        $this->_schedulesProfessionals = $schedulesProfessionals;
        $this->_table = $table;
        $this->_user = $user;
    }

    private function _get(Request $request, Response $response, $args)
    {
        $newResponse = null;
        if (array_key_exists("id", $args)
            && array_key_exists("start", $args)
            && array_key_exists("end", $args)
        ) {
            $collection = $this->_readScheduleDays(
                $args["id"],
                $args["start"],
                $args["end"]
            );
            if (!$collection) {
                $newResponse = $response->withJson($this->_badRequest(), 400);
            } else {
                $newResponse = $response->withJson($collection);
            }
        } elseif (array_key_exists("id", $args)) {
            $resource = $this->_readById($args["id"]);
            if (!$resource) {
                $newResponse = $response->withJson($this->_resourceNotFound(), 404);
            } else {
                $newResponse = $response->withJson($resource);
            }
        } elseif (strpos($request->getUri()->getPath(), "search") !== false
            && array_key_exists("terms", $args)
            && strlen(trim($args["terms"]))
        ) {
            $collection = $this->_search($args["terms"]);
            if (!$collection) {
                $newResponse = $response->withJson($this->_badRequest(), 400);
            } else {
                $newResponse = $response->withJson($collection);
            }
        } else {
            $collection = $this->_readAll($request);
            if (!$collection) {
                $newResponse = $response->withJson($this->_badRequest(), 400);
            } else {
                $newResponse = $response->withJson($collection);
            }
        }

        return $newResponse;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $newResponse = null;

        switch ($request->getMethod()) {
        case "GET":
            $newResponse = $this->_get($request, $response, $args);
            break;

        case "POST":
            $resource = $this->_create($request->getParsedBody());
            if (!$resource) {
                $newResponse = $response->withJson(
                    $this->_resourceNotProcessable(),
                    422
                );
            } else {
                $newResponse = $response->withJson($resource, 201);
            }
            break;

        case "PATCH":
            if (array_key_exists("id", $args)) {
                $resource = $this->_update($args["id"], $request->getParsedBody());
                if (!$resource) {
                    $newResponse = $response->withJson(
                        $this->_resourceNotProcessable(),
                        422
                    );
                } else {
                    $newResponse = $response->withJson($resource);
                }
            }
            break;

        case "DELETE":
            if (array_key_exists("id", $args)) {
                $status = $this->_delete($args["id"]);
                $newResponse = $response->withJson($status);
            }
            break;

        default:
            break;
        }

        return $newResponse;
    }

    private function _resourceNotFound()
    {
        return [
            "errors" => [
                "id" => "404",
                "status" => "404 Not Found",
                "title" => $this->_resourceType . " not found",
            ],
        ];
    }

    private function _resourceNotProcessable()
    {
        return [
            "errors" => [
                "id" => "422",
                "status" => "422 Unprocessable Entity",
                "title" => "Data format not correct",
            ],
        ];
    }

    private function _badRequest()
    {
        return [
            "errors" => [
                "id" => "400",
                "status" => "400 Bad Request",
                "title" => "Query parameters format not correct",
            ],
        ];
    }

    private function _create($body)
    {
        $this->_logger->info("Creating a schedule");

        $body['data']['attributes']['name'] = mb_convert_case(
            mb_strtolower($body['data']['attributes']['name']),
            MB_CASE_TITLE,
            "UTF-8"
        );

        $id = $this->_table->insertGetId($body['data']['attributes']);
        if (!$id) {
            return false;
        }

        $resource = [
            "data" => [
                "attributes" => $body['data']['attributes'],
                "id" => $id,
                "type" => $this->_resourceType,
            ],
            "links" => [
                "self" => $this->_url . "/" . $id,
                "related" => $this->_url . "/" . $id . "/days",
            ],
        ];
        return $resource;
    }

    private function _readAll($request)
    {
        $this->_logger->info("Getting all the schedules");

        $query = $this->_table;

        /**
         * Sorting
        **/
        /**
         * Example: /schedules?sort=[-]attr1[,[-]attr2,..]
        **/
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

        /**
         * Filtering
        **/
        /**
         * Example: /schedules?filter=[-]attr1:string[,[-]attr2:string,..]
        **/
        $filter = $request->getParam('filter');
        if ($filter) {
            $terms = explode(',', $filter);
            foreach ($terms as $term) {
                $parameters = explode(':', $term);
                if (sizeof($parameters) != 2) {
                    return false;
                }
                if (substr($parameters[0], 0, 1) === '-') {
                    $query = $query->where(
                        ltrim($parameters[0], '-'),
                        'like',
                        $parameters[1]
                    );
                } else {
                    $query = $query->orWhere($parameters[0], 'like', $parameters[1]);
                }
            }
        }

        /**
         * Pagination
         * ToDo
        **/
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

        /**
         * Getting the schedules
        **/
        $data = [];
        $resources = $query->get();

        foreach ($resources as $resource) {
            $professionals = $this->_readScheduleProfessionals($resource);

            $id = $resource->id;
            unset($resource->id);
            $resourceData = [
                "attributes" => $resource,
                "id" => $id,
                "type" => $this->_resourceType,
                "relationships" => [
                    "professionals" => $professionals,
                ],
            ];
            $data[] = $resourceData;
        }
        $collection = [
            "data" => $data,
            "links" => [
                "self" => $this->_url,
            ],
        ];
        return $collection;
    }

    private function _readById($id)
    {
        $this->_logger->info("Getting a schedule");

        $resourceAttributes = $this->_table->find($id);

        if (!$resourceAttributes) {
            return false;
        }

        $professionals = $this->_readScheduleProfessionals($resourceAttributes);
        $days = $this->_readScheduleDays($resourceAttributes);
        unset($resourceAttributes->id);

        $resource = [
            "data" => [
                "attributes" => $resourceAttributes,
                "id" => $id,
                "type" => $this->_resourceType,
                "relationships" => [
                    "days" => $days,
                    "professionals" => $professionals,
                ],
            ],
            "links" => [
                "self" => $this->_url . "/" . $id,
            ],
        ];
        return $resource;
    }

    private function _update($id, $body)
    {
        $this->_logger->info("Updating a schedule");

        $body['data']['attributes']['name'] = ucwords(
            strtolower($body['data']['attributes']['name'])
        );

        $status = $this->_table
            ->where('id', $id)
            ->update($body["data"]["attributes"]);
        if (!$status) {
            return false;
        }
        $resourceAttributes = $this->_table->find($id);
        if (!$resourceAttributes) {
            return false;
        }

        $professionals = $this->_readScheduleProfessionals($resource);
        $days = $this->_readScheduleDays($resource);
        unset($resourceAttributes->id);

        $resource = [
            "data" => [
                "attributes" => $resourceAttributes,
                "id" => $id,
                "type" => $this->_resourceType,
                "relationships" => [
                    "days" => $days,
                    "professionals" => $professionals,
                ],
            ],
            "links" => [
                "self" => $this->_url . "/" . $id,
                "related" => $this->_url . "/" . $id . "/days",
            ],
        ];
        return $resource;
    }

    private function _delete($id)
    {
        $this->_logger->info("Deleting a schedule");
        return $this->_table->where('id', $id)->delete();
    }

    private function _readScheduleProfessionals($schedule)
    {
        $query = clone $this->_schedulesProfessionals;
        $professionals = $query->where("schedule", $schedule->id)->get();
        $data = [];
        if ($professionals) {
            foreach ($professionals as $professional) {
                $user = $this->_user->readById($professional->user);
                if ($user) {
                    $id = $user["data"]["id"];
                    unset($user["data"]["id"]);
                    unset($user["data"]["attributes"]->password);
                    $data[] = [
                        "attributes" => $user["data"]["attributes"],
                        "id" => $id,
                        "type" => "user",
                    ];
                }
            }
        }
        return $data;
    }

    private function _readScheduleDays(
        $schedule,
        $startDate = false,
        $endDate = false
    ) {
        if ($startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($endDate);
            return $this->_readScheduleDaysAndAppointments(
                $schedule,
                $startDate,
                $endDate
            );
        }

        $query = clone $this->_schedulesDays;
        $days = $query->where("schedule", $schedule->id)->get();
        $data = [];
        if ($days) {
            foreach ($days as $day) {
                $hours = $this->_readDaysHours($day);
                $id = $day->id;
                unset($day->id);
                $data[] = [
                    "attributes" => $day,
                    "id" => $id,
                    "type" => "day",
                    "relationships" => [
                        "hours" => $hours,
                    ],
                ];
            }
        }
        return $data;
    }

    private function _readDaysHours($day)
    {
        $query = clone $this->_schedulesDaysHours;
        $dayHours = $query->where("day", $day->id)->get();
        $data = [];
        if ($dayHours) {
            foreach ($dayHours as $dayHour) {
                $id = $dayHour->id;
                unset($dayHour->id);
                $data[] = [
                    "attributes" => $dayHour,
                    "id" => $id,
                    "type" => "dayHour",
                ];
            }
        }
        return $data;
    }

    private function _readScheduleDaysAndAppointments(
        $schedule,
        $startDate,
        $endDate
    ) {
        $schedule = $this->_table->find($schedule);
        if ($schedule) {
            $data = [];
            while ($startDate <= $endDate) {
                $query = clone $this->_schedulesDays;
                $query->where('schedule', $schedule->id);
                if ($schedule->periodicity === 1) {
                    $query->where('weekDay', $startDate->format('N') - 1);
                } else {
                    $query->where('date', $startDate->format('Y-m-d'));
                }
                $query->where('active', 1);
                $day = $query->first();

                if ($day) {
                    if (!$day->date) {
                        $day->date = $startDate->format('Y-m-d');
                    }
                    $hoursQuery = clone $this->_schedulesDaysHours;
                    $hours = $hoursQuery->where("day", $day->id)->get();
                    if ($hours) {
                        $appointments = $this->_getDateAppointments(
                            $schedule,
                            $startDate,
                            $hours
                        );
                        $id = $day->id;
                        unset($day->id);
                        $data[] = [
                            "attributes" => $day,
                            "id" => $id,
                            "type" => "day",
                            "relationships" => [
                                "appointments" => $appointments,
                            ],
                        ];
                    }
                }
                $startDate->modify('+1 day');
            }
        }
        $collection = [
            "data" => $data,
            "links" => [
                "self" => $this->_url . '/' . $schedule->id . '/day',
            ],
        ];
        return $collection;
    }

    private function _getDateAppointments($schedule, $date, $hours)
    {
        $appointments = [];
        foreach ($hours as $hour) {
            $start = \DateTime::createFromFormat('H:i:s', $hour->start);
            $end = \DateTime::createFromFormat('H:i:s', $hour->end);
            while ($start < $end) {
                $query = clone $this->_appointments;
                $resource = $query->where('date', $date->format('Y-m-d'))
                    ->where('schedule', $schedule->id)
                    ->where('hour', $start->format('H:i:s'))
                    ->first();
                if ($resource) {
                    $professional = $this->_user->readById($resource->professional);
                    $patient = $this->_patient->readById($resource->patient);
                    unset($professional["data"]["attributes"]->password);
                    $id = $resource->id;
                    unset($resource->id);
                    $resource->hour = \DateTime::createFromFormat(
                        'H:i:s',
                        $resource->hour
                    )->format('H:i');
                    $appointments[] = [
                        "attributes" => $resource,
                        "id" => $id,
                        "type" => "appointment",
                        "relationships" => [
                            "patient" => $patient,
                            "professional" => $professional,
                        ],
                    ];
                } else {
                    $appointments[] = [
                        "type" => "appointment",
                        "attributes" => [
                            "confirmed" => null,
                            "date" => $date->format('Y-m-d'),
                            "id" => null,
                            "indications" => null,
                            "hour" => $start->format('H:i'),
                            "patient" => null,
                            "printed" => null,
                            "professional" => null,
                            "reminderData" => null,
                            "reminderSent" => null,
                            "reminderWay" => null,
                            "reprogrammed" => null,
                            "schedule" => $schedule->id,
                        ],
                    ];
                }
                $start->modify('+' . $schedule->appointmentInterval . ' minutes');
            }
        }
        return $appointments;
    }
}
