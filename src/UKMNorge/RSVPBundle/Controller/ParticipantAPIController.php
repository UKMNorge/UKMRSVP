<?php

namespace UKMNorge\RSVPBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use UKMNorge\RSVPBundle\Entity\Response;
use UKMNorge\RSVPBundle\Entity\Waiting;
use Exception;
use stdClass;
use DateTime;

use UKMNorge\APIBundle\Util\Access;

class ParticipantAPIController extends Controller {

	public function allAttendingForOwnerAction(Request $request) {
		$access = $this->getAccessFromRequest($request);

		$response = new stdClass();
		try {
			if($access->got('readParticipants')) {
				$response->success = true;
				$owner = $request->request->get('owner');
				$events = $this->get('ukmrsvp.event')->getByOwner($owner);
				$this->get('logger')->debug('UKMRSVPBundle:ParticipantAPIController:allAttendingForOwnerAction: Events: '.var_export($events, true));
				$participants = array();
				foreach($events as $event) {
					$e_participants = $this->get('ukmrsvp.event')->getAttending($event);
					$participants = array_merge($participants, $e_participants);
				}
				$response->data = $participants;
				$this->get('logger')->debug('UKMRSVPBundle:ParticipantAPIController:allAttendingForOwnerAction: Response-data: '.var_export($response->data, true));
				return new JsonResponse($response);
			}
			else {
				$response->success = false;
				$response->errors = $access->errors();
				$response->errors[] = "UKMRSVPBundle:ParticipantAPIController: Du har ikke tilgang til å hente ut deltakere. Krever 'readParticipants'-tilgangen.";
			}
		}
		catch(Exception $e) {
			$response->success = false;
			$response->errors[] = 'UKMRSVPBundle:ParticipantAPIController: Det oppsto en ukjent feil. Ta kontakt med support. Feilmelding: '.$e->getMessage();
			$this->get('logger')->error('UKMRSVPBundle:ParticipantAPIController:allAttendingForOwnerAction: Det oppsto en feil: '.$e->getMessage());
			return new JsonResponse($response);
		}		
	}

	public function attendingAction(Request $request) {
		$access = $this->getAccessFromRequest($request);
		$response = new stdClass();
		try {
			if($access->got('readParticipants')) {
				$response->success = true;
				
				$event_id = $request->request->get('event_id');
				$event = $this->get('ukmrsvp.event')->get($event_id);
				$participants = $this->get('ukmrsvp.event')->getAttending($event);
				$pList = array();
				foreach($participants as $participant) {
					$pList[] = $participant->expose();
				}
				$response->data = $pList;
			}
			else {
				$response->success = false;
				$response->errors = $access->errors();
				$response->errors[] = "UKMRSVPBundle:ParticipantAPIController: Du har ikke tilgang til å hente ut deltakere. Krever 'readParticipants'-tilgangen.";
			}
			return new JsonResponse($response);
		}
		catch(Exception $e) {
			$response->success = false;
			$response->errors[] = 'UKMRSVPBundle:ParticipantAPIController: Det oppsto en ukjent feil. Ta kontakt med support. Feilmelding: '.$e->getMessage();
			$this->get('logger')->error('UKMRSVPBundle:ParticipantAPIController:attendingAction: Det oppsto en feil: '.$e->getMessage());
			return new JsonResponse($response);
		}
	}

	public function waitingAction(Request $request) {
		$access = $this->getAccessFromRequest($request);
		$response = new stdClass();
		try {
			if($access->got('readParticipants')) {
				$response->success = true;
				
				$event_id = $request->request->get('event_id');
				$event = $this->get('ukmrsvp.event')->get($event_id);
				$participants = $this->get('ukmrsvp.event')->getWaiting($event);
				$pList = array();
				foreach($participants as $participant) {
					$pList[] = $participant->expose();
				}
				$response->data = $pList;
			}
			else {
				$response->success = false;
				$response->errors = $access->errors();
				$response->errors[] = "UKMRSVPBundle:ParticipantAPIController: Du har ikke tilgang til å hente ut deltakere. Krever 'readParticipants'-tilgangen.";
			}
			return new JsonResponse($response);
		}
		catch(Exception $e) {
			$response->success = false;
			$response->errors[] = 'UKMRSVPBundle:ParticipantAPIController: Det oppsto en ukjent feil. Ta kontakt med support. Feilmelding: '.$e->getMessage();
			$this->get('logger')->error('UKMRSVPBundle:ParticipantAPIController:waitingAction: Det oppsto en feil: '.$e->getMessage());
			return new JsonResponse($response);
		}
	}

	public function notComingAction(Request $request) {
		$access = $this->getAccessFromRequest($request);
		$response = new stdClass();
		try {
			if($access->got('readParticipants')) {
				$response->success = true;
				
				$event_id = $request->request->get('event_id');
				$event = $this->get('ukmrsvp.event')->get($event_id);
				$participants = $this->get('ukmrsvp.event')->getNotComing($event);
				$pList = array();
				foreach($participants as $participant) {
					$pList[] = $participant->expose();
				}
				$response->data = $pList;
			}
			else {
				$response->success = false;
				$response->errors = $access->errors();
				$response->errors[] = "UKMRSVPBundle:ParticipantAPIController: Du har ikke tilgang til å hente ut deltakere. Krever 'readParticipants'-tilgangen.";
			}
			return new JsonResponse($response);
		}
		catch(Exception $e) {
			$response->success = false;
			$response->errors[] = 'UKMRSVPBundle:ParticipantAPIController: Det oppsto en ukjent feil. Ta kontakt med support. Feilmelding: '.$e->getMessage();
			$this->get('logger')->error('UKMRSVPBundle:ParticipantAPIController:notComingAction: Det oppsto en feil: '.$e->getMessage());
			return new JsonResponse($response);
		}
	}

	public function allAction(Request $request) {
		$access = $this->getAccessFromRequest($request);
		$response = new stdClass();
		try {
			if($access->got('readParticipants')) {
				$response->success = true;
				
				$event_id = $request->request->get('event_id');
				$event = $this->get('ukmrsvp.event')->get($event_id);
				$participants = $this->get('ukmrsvp.event')->getAllParticipants($event);
				$pList = array();
				foreach($participants as $participant) {
					$pList[] = $participant->expose();
				}
				$response->data = $pList;
			}
			else {
				$response->success = false;
				$response->errors = $access->errors();
				$response->errors[] = "UKMRSVPBundle:ParticipantAPIController: Du har ikke tilgang til å hente ut deltakere. Krever 'readParticipants'-tilgangen.";
			}
			return new JsonResponse($response);
		}
		catch(Exception $e) {
			$response->success = false;
			$response->errors[] = 'UKMRSVPBundle:ParticipantAPIController: Det oppsto en ukjent feil. Ta kontakt med support. Feilmelding: '.$e->getMessage();
			$this->get('logger')->error('UKMRSVPBundle:ParticipantAPIController:allAction: Det oppsto en feil: '.$e->getMessage());
			return new JsonResponse($response);
		}
	}

	private function getAccessFromRequest($request) {
		try {
			if($request->getMethod() == 'GET') {
				$this->access = new Access($request->query->get('API_KEY'), $this->getParameter('ukmapi.api_key'), $this->getParameter('ukmapi.api_secret'));
				$this->access->validate($request->query->all());
			} 
			else {
				$this->access = new Access($request->request->get('API_KEY'), $this->getParameter('ukmapi.api_key'), $this->getParameter('ukmapi.api_secret'));
				$this->access->validate($request->request->all());
			}
			
			return $this->access;
		}
		catch(Exception $e) {
			throw new Exception('Klarte ikke å validere spørringen - er APIBundle installert?');
		}
	}
}