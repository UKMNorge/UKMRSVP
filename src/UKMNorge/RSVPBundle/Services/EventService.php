<?php
namespace UKMNorge\RSVPBundle\Services;

use DateTime;
use UKMNorge\RSVPBundle\Entity\Event;

class EventService {
	
	var $container;
	
	public function __construct( $container, $router ) {
		$this->container = $container;
		$this->doctrine = $this->container->get('doctrine');
		$this->em 		= $this->doctrine->getManager();
		$this->repo 	= $this->doctrine->getRepository('UKMRSVPBundle:Event');
		$this->responseRepo = $this->doctrine->getRepository('UKMRSVPBundle:Response');
		$this->router 	= $router;
	}

	public function create($name, $place, $owner, $spots, $image, DateTime $date_start, DateTime $date_stop, $description) {
		$event = new Event();
		$event->setName($name)
			->setPlace($place)
			->setOwner($owner)
			->setSpots($spots)
			->setImage($image)
			->setDateStart($date_start)
			->setDateStop($date_stop)
			->setDescription($description);
		$this->save($event);

		return $event;
	}

	public function edit($id, $name, $place, $owner, $spots, $image, DateTime $date_start, DateTime $date_stop, $description) {
		$event = $this->get($id);

		$event->setName($name)
			->setPlace($place)
			->setOwner($owner)
			->setSpots($spots)
			->setImage($image)
			->setDateStart($date_start)
			->setDateStop($date_stop)
			->setDescription($description);
		$this->save($event);

		return $event;
	}

	public function save(Event $event) {
		$this->em->persist($event);
		$this->em->flush();
		return true;
	}
	
	public function get( $id ) {
		return $this->repo->findOneById( $id );
	}

	public function getByOwner($owner) {
		return $this->repo->findBy(array('owner' => $owner));
	}
	
	public function getAll() {
		return $this->repo->findAll();
	}
	
	public function getName( $event ) {
		return $event->getName();
	}
	
	public function getDateStart( $event ) {
		return $event->getDateStart();
	}

	public function getDateStop( $event ) {
		return $event->getDateStop();
	}
	
	public function getPlace( $event ) {
		return $event->getPlace();
	}
	
	public function getImage($event) {
		return $event->getImage();
	}

	public function isOpen( $event ) {
		if($event->getSpots() == 0)
			return true;
		return 0 < $this->getSpotsAvailable( $event );
	}
	
	public function getSpots( $event ) {
		return $event->getSpots();
	}
	
	public function getSpotsAvailable( $event ) {
		// 0 spots = ingen begrensning
		if($event->getSpots() == 0)
			return true;
		
		$spots = $this->getSpots( $event );
		$taken = $this->getSpotsTaken( $event );
		return (int) $spots - (int) $taken;
	}

	public function getAttending($event) {
		$userProvider = $this->container->get('dipb_user_provider');
		$attending = array();
		$list = $this->responseRepo->findBy(array('event'=>$event, 'status' => 'yes'));
		foreach ($list as $item) {
			$attending[] = $userProvider->loadUserByUsername($item->getUser());
		}
		return $attending;
	}

	public function getWaiting($event) {
		$userProvider = $this->container->get('dipb_user_provider');
		$attending = array();
		$list = $this->responseRepo->findBy(array('event'=>$event, 'status' => 'maybe'));
		foreach ($list as $item) {
			$attending[] = $userProvider->loadUserByUsername($item->getUser());
		}
		return $attending;
	}
	
	public function getNotComing($event) {
		$userProvider = $this->container->get('dipb_user_provider');
		$attending = array();
		$list = $this->responseRepo->findBy(array('event'=>$event, 'status' => 'no'));
		foreach ($list as $item) {
			$attending[] = $userProvider->loadUserByUsername($item->getUser());
		}
		return $attending;
	}

	public function getAllParticipants($event) {
		$userProvider = $this->container->get('dipb_user_provider');
		$people = array();
		$list = $this->responseRepo->findBy(array('event' => $event));
		foreach ($list as $item) {
			$people[] = $userProvider->loadUserByUsername($item->getUser());
		}
		return $people;
	}

	public function getSpotsTaken( $event ) {
		return $this->getStatusCountYes( $event );
	}
	
	public function getStatusCountYes( $event ) {
		$responseServ = $this->container->get('ukmrsvp.response');
		return $responseServ->getCountYes( $event );
	}
	
	public function getStatusCountNo( $event ) {
		$responseServ = $this->container->get('ukmrsvp.response');
		return $responseServ->getCountNo( $event );
	}
	
	public function getStatusCountMaybe( $event ) {
		$responseServ = $this->container->get('ukmrsvp.response');
		return $responseServ->getCountMaybe( $event );
	}

	public function getUrl( $event ) {
		$data = array();
		$data['id'] = $event->getId();
		$data['name'] = $this->getName( $event );
		return $this->router->generate('ukmrsvp_event', $data);
	}
	
	public function getUrlResponse( $event, $response='yes' ) {
		$data = array();
		$data['id'] = $event->getId();
		$data['name'] = $this->getName( $event );
		$data['response'] = $response;
		return $this->router->generate( 'ukmrsvp_event_response', $data );
	}
	
	public function getDescription( $event ) {
		return $event->getDescription();
		#return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce eget erat ac tellus molestie condimentum. Pellentesque in est tortor. Mauris vestibulum purus et libero imperdiet, suscipit facilisis neque hendrerit. Suspendisse a nunc eget mi aliquam pretium eget eget felis. Suspendisse quis lacinia metus. Nam a maximus lacus. Aliquam neque ex, dignissim non risus iaculis, varius vehicula ligula. Cras a urna eget ligula ultricies convallis.';
	}
}