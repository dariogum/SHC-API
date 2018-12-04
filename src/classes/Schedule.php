<?php

namespace App\Classes;

use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class Schedule {
	private $appointments;
	private $logger;
	private $patient;
	private $resourceType = "schedule";
	private $schedulesDays;
	private $schedulesDaysHours;
	private $schedulesProfessionals;
	private $table;
	private $url = "schedules";
	private $user;

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
		$this->appointments = $appointments;
		$this->logger = $logger;
		$this->patient = $patient;
		$this->schedulesDays = $schedulesDays;
		$this->schedulesDaysHours = $schedulesDaysHours;
		$this->schedulesProfessionals = $schedulesProfessionals;
		$this->table = $table;
		$this->user = $user;
	}

	private function get(Request $request, Response $response, $args) {
		$newResponse = null;
		if (array_key_exists("id", $args) && array_key_exists("start", $args) && array_key_exists("end", $args)) {
			$collection = $this->readScheduleDays($args["id"], $args["start"], $args["end"]);
			if (!$collection) {
				$newResponse = $response->withJson($this->badRequest(), 400);
			} else {
				$newResponse = $response->withJson($collection);
			}
		} else if (array_key_exists("id", $args)) {
			$resource = $this->readById($args["id"]);
			if (!$resource) {
				$newResponse = $response->withJson($this->resourceNotFound(), 404);
			} else {
				$newResponse = $response->withJson($resource);
			}
		} else if (strpos($request->getUri()->getPath(), "search") !== false
			&& array_key_exists("terms", $args) && strlen(trim($args["terms"]))) {
			$collection = $this->search($args["terms"]);
			if (!$collection) {
				$newResponse = $response->withJson($this->badRequest(), 400);
			} else {
				$newResponse = $response->withJson($collection);
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
		$this->logger->info("Creating a schedule");

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
				"related" => $this->url . "/" . $id . "/days",
			],
		];
		return $resource;
	}

	public function readAll($request) {
		$this->logger->info("Getting all the schedules");

		$query = $this->table;

		/** Sorting **/
		/** Example: /schedules?sort=[-]attr1[,[-]attr2,..] **/
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
		/** Example: /schedules?filter=[-]attr1:string[,[-]attr2:string,..] **/
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

		/** Getting the schedules **/
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
		$this->logger->info("Getting a schedule");

		$resourceAttributes = $this->table->find($id);

		if (!$resourceAttributes) {
			return false;
		}

		$professionals = $this->readScheduleProfessionals($resourceAttributes);
		$days = $this->readScheduleDays($resourceAttributes);
		unset($resourceAttributes->id);

		$resource = [
			"data" => [
				"attributes" => $resourceAttributes,
				"id" => $id,
				"type" => $this->resourceType,
				"relationships" => [
					"days" => $days,
					"professionals" => $professionals,
				],
			],
			"links" => [
				"self" => $this->url . "/" . $id,
			],
		];
		return $resource;
	}

	public function update($id, $body) {
		$this->logger->info("Updating a schedule");

		$body['data']['attributes']['name'] = ucwords(strtolower($body['data']['attributes']['name']));

		$status = $this->table->where('id', $id)->update($body["data"]["attributes"]);
		if (!$status) {
			return false;
		}
		$resourceAttributes = $this->table->find($id);
		if (!$resourceAttributes) {
			return false;
		}

		$professionals = $this->readScheduleProfessionals($resource);
		$days = $this->readScheduleDays($resource);
		unset($resourceAttributes->id);

		$resource = [
			"data" => [
				"attributes" => $resourceAttributes,
				"id" => $id,
				"type" => $this->resourceType,
				"relationships" => [
					"days" => $days,
					"professionals" => $professionals,
				],
			],
			"links" => [
				"self" => $this->url . "/" . $id,
				"related" => $this->url . "/" . $id . "/days",
			],
		];
		return $resource;
	}

	public function delete($id) {
		$this->logger->info("Deleting a schedule");
		return $this->table->where('id', $id)->delete();
	}

	private function readScheduleProfessionals($schedule) {
		$query = clone $this->schedulesProfessionals;
		$professionals = $query->where("schedule", $schedule->id)->get();
		$data = [];
		if ($professionals) {
			foreach ($professionals as $professional) {
				$id = $professional->id;
				unset($professional->id);
				$data[] = [
					"attributes" => $professional,
					"id" => $id,
					"type" => "user",
				];
			}
		}
		return $data;
	}

	private function readScheduleDays($schedule, $startDate = false, $endDate = false) {
		if ($startDate && $endDate) {
			$startDate = new \DateTime($startDate);
			$endDate = new \DateTime($endDate);
			return $this->readScheduleDaysAndAppointments($schedule, $startDate, $endDate);
		}

		$query = clone $this->schedulesDays;
		$days = $query->where("schedule", $schedule->id)->get();
		$data = [];
		if ($days) {
			foreach ($days as $day) {
				$hours = $this->readDaysHours($day);
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

	private function readDaysHours($day) {
		$query = clone $this->schedulesDaysHours();
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

	private function readScheduleDaysAndAppointments($schedule, $startDate, $endDate) {
		$schedule = $this->table->find($schedule);
		if ($schedule) {
			$data = [];
			while ($startDate <= $endDate) {
				$query = clone $this->schedulesDays;
				$query->where('schedule', $schedule->id);
				if ($schedule->periodicity === 1) {
					$query->where('weekDay', $startDate->format('N') - 1);
				} else {
					$query->where('date', $startDate->format('Y-m-d'));
				}
				$query->where('active', 1);
				$day = $query->first();

				if ($day) {
					$hoursQuery = clone $this->schedulesDaysHours;
					$hours = $hoursQuery->where("day", $day->id)->get();
					if ($hours) {
						$appointments = $this->getDateAppointments($schedule, $startDate, $hours);
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
				"self" => $this->url . '/' . $schedule->id . '/day',
			],
		];
		return $collection;
	}

	private function getDateAppointments($schedule, $date, $hours) {
		$appointments = [];
		foreach ($hours as $hour) {
			$start = \DateTime::createFromFormat('H:i:s', $hour->start);
			$end = \DateTime::createFromFormat('H:i:s', $hour->end);
			while ($start < $end) {
				$query = clone $this->appointments;
				$resource = $query->where('date', $date->format('Y-m-d'))
					->where('schedule', $schedule->id)
					->where('hour', $start->format('H:i:s'))
					->first();
				if ($resource) {
					$professional = $this->user->readById($resource->professional);
					$patient = $this->patient->readById($resource->patient);
					unset($professional["data"]["attributes"]->password);
					$id = $resource->id;
					unset($resource->id);
					$resource->hour = \DateTime::createFromFormat('H:i:s', $resource->hour)->format('H:i');
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