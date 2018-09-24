<?php

namespace App\Classes;

use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class Stats {
	private $logger;
	private $patients;
	private $url = "stats";
	private $users;
	private $visits;

	public function __construct(LoggerInterface $logger, Builder $patients, Builder $users, Builder $visits) {
		$this->logger = $logger;
		$this->patients = $patients;
		$this->users = $users;
		$this->visits = $visits;
	}

	private function get(Request $request, Response $response, $args) {
		$newResponse = null;

		if (array_key_exists("report", $args) && array_key_exists("dates", $args)) {
			$dates = explode(',', $args["dates"]);
			if (count($dates) === 2) {
				switch ($args["report"]) {
				case 'newPatients':
					$newResponse = $response->withJson($this->newPatients($dates));
					break;

				case 'visits':
					$newResponse = $response->withJson($this->visits($dates));
					break;

				case 'visitsByMonth':
					$newResponse = $response->withJson($this->visitsByMonth($dates));
					break;

				case 'visitsByPatients':
					$newResponse = $response->withJson($this->visitsByPatients($dates));
					break;

				case 'visitsBySocialSecurity':
					$newResponse = $response->withJson($this->visitsBySocialSecurity($dates));
					break;

				default:
					$newResponse = $response->withJson(["error" => "Stat not implemented"]);
				}
			} else {
				$newResponse = $response->withJson($this->badRequest(), 400);
			}
		} else {
			$newResponse = $response->withJson($this->badRequest(), 400);
		}
		return $newResponse;
	}

	public function __invoke(Request $request, Response $response, $args) {
		$newResponse = null;

		switch ($request->getMethod()) {
		case "GET":
			$newResponse = $this->get($request, $response, $args);
			break;

		default:
			break;
		}

		return $newResponse;
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

	public function newPatients($dates) {
		return $this->patients
			->whereBetween('createdAt', [$dates[0], $dates[1]])
			->count();
	}

	public function visits($dates) {
		return $this->visits
			->whereBetween('date', [$dates[0], $dates[1]])
			->count();
	}

	public function visitsByMonth($dates) {
		return $this->visits
			->select($this->visits->raw('MONTH(date) as month, YEAR(date) as year, count(*) as visits'))
			->whereBetween('date', [$dates[0], $dates[1]])
			->groupBy('month', 'year')
			->orderBy('year', 'month')
			->get();
	}

	public function visitsByPatients($dates) {
		return $this->visits
			->select($this->visits->raw('concat(patients.lastname, " ", patients.name) as patientName'), $this->visits->raw('count(*) as visits'))
			->whereBetween('date', [$dates[0], $dates[1]])
			->join('patients', 'patient', '=', 'patients.id')
			->groupBy('patient')
			->get();
	}

	public function visitsBySocialSecurity($dates) {
		return $this->visits
			->select('patients.socialSecurity1 as socialSecurity', $this->visits->raw('count(*) as visits'))
			->whereBetween('date', [$dates[0], $dates[1]])
			->join('patients', 'patient', '=', 'patients.id')
			->groupBy('patients.socialSecurity1')
			->get();
	}

}