<?php

namespace UKMNorge\DipBundle\Controller;

// For å kunne dele sessions på flere sider
#ini_set('session.cookie_domain', '.ukm.dev' );

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

use HttpRequest;
use Symfony\Component\HttpFoundation\Request;
use UKMNorge\DipBundle\Entity\Token;
use UKMNorge\DipBundle\Entity\User;
use UKMCurl;
use Exception;
use DateTime;
use UKMNorge\DipBundle\Security\Provider\DipBUserProvider;
use RequestStack;

class TokenController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('UKMDipBundle:Default:index.html.twig', array('name' => $name));
    }

    public function loginAction(Request $request) 
    {	
        if ( $this->container->getParameter('UKM_HOSTNAME') == 'ukm.dev') {
            $this->dipURL = 'http://delta.ukm.dev/web/app_dev.php/dip/token';
            $this->deltaLoginURL = 'http://delta.ukm.dev/web/app_dev.php/login';
        } 
        else {
            $this->dipURL = 'http://delta.ukm.no/dip/token';
            $this->deltaLoginURL = 'http://delta.ukm.no/login';
        }

    	require_once('UKM/curl.class.php');

    	// Dette er entry-funksjonen til DIP-innlogging.
    	// Her sjekker vi om brukeren har en session med en autentisert token, 
    	// og hvis ikke genererer vi en og sender brukeren videre til Delta.

        $session = $this->get('session');

        // Registrer hvilken side brukeren var på i en session-variabel
        // Om lokal side vil kan de sende brukeren tilbake til den siden når brukeren kommer tilbake fra Delta.
        $previous = $request->headers->get('referer');
        if($previous) {
            $uri = parse_url($previous);
            if ($uri['host'] == ($this->getParameter('dip_location').'.'.$this->getParameter('UKM_HOSTNAME'))) {
                #$session = $request->getSession();
                $session->set('referer', $uri['path']);
            }
            // var_dump($uri);
            // var_dump(($this->getParameter('dip_location').$this->getParameter('UKM_HOSTNAME')));
            // throw new Exception('Staaaaahp');
        }
        

        // Send request to Delta with token-info
    	// $dipURL = 'http://delta.ukm.dev/web/app_dev.php/dip/token';
        #$location = 'ambassador';
        $location = $this->container->getParameter('dip_location');
        #$firewall_name = 'secure_area';
        $firewall_name = $this->container->getParameter('dip_firewall_area');
        #$entry_point = 'ukm_amb_join_address';
        $entry_point = $this->container->getParameter('dip_entry_point');
    	$curl = new UKMCurl();


    	// Har brukeren en session med token?
    	if ($session->isStarted()) {
    		$token = $session->get('token');
    		if ($token) {
    			// Hvis token finnes, sjekk at det er autentisert i databasen
    			// echo '<br>Token is: ';
    			// var_dump($token);
    			// echo '<br>';
    			$repo = $this->getDoctrine()->getRepository('UKMDipBundle:Token');
    			$existingToken = $repo->findOneBy(array('token' => $token));
    			// var_dump($existingToken);
    			if ($existingToken) {
    				// Hvis token finnes
    				if ($existingToken->getAuth() == true) {

    					// Authorized, so trigger log in
    					$userId = $existingToken->getUserId();

         //                echo '<br>';
    					// var_dump($userId);
                        
    					// Load user data?
    					$userProvider = $this->get('dipb_user_provider');
    					//$userProvider = $this->get('dipb_user_provider');
    					$user = $userProvider->loadUserByUsername($userId);
    		 			// var_dump($user);
                        // die('Hellååååå');
						// die();
				        $token = new UsernamePasswordToken($user, $user->getPassword(), $firewall_name, $user->getRoles());

				        // For older versions of Symfony, use security.context here
                        // Newer uses security.token_storage
                        if (\Symfony\Component\HttpKernel\Kernel::VERSION > 2.5) {
                            $this->get("security.token_storage")->setToken($token);
                        }
                        else {
                            $this->get("security.context")->setToken($token);
                        }

				        // Fire the login event
				        // Logging the user in above the way we do it doesn't do this automatically
				        // now dispatch the login event
                        
                        #$request = $rStack->getCurrentRequest();
                        if (!$request) {
                            $rStack = $this->get('RequestStack');
                            var_dump($rStack);
                            throw new Exception('No request! Woah, man, you need to chill out.', 20008);
                        }
					    #$request = $this->get("request");
                        #$request = $this->getRequest();
				        $event = new InteractiveLoginEvent($request, $token);
				        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

                        // Har vi en referer-side?
                        $referer = $session->get('referer');
                        $this->get('logger')->debug('UKMDipBundle: Referer: '.$referer);
                        if($referer != null) {
                            return $this->redirect($referer);
                        }
				        // Hvis ikke, redirect til en side bak firewall i stedet
				        return $this->redirect($this->generateUrl($entry_point));
    				}
    				else {
    					// Hvis token ikke er autentisert enda
    					// Fjern lagret token
    					$session->invalidate();
                        #throw new Exception('Token not authenticated');
                        return $this->redirect($this->get('router')->generate('ukm_dip_login'));
    					// return $this->render('UKMDipBundle:Default:index.html.twig', array('name' => 'Token not authorized'));
    					//TODO: Redirect til Delta-innlogging
    				}
    			}
    			// Token finnes, men ikke i databasen.
    			// Ingen token som matcher, ugyldig?
    			// Genererer ny og last inn siden på nytt?
                // Denne burde ikke dukke opp!
                $session->invalidate();

                #error_log('REDIR-loop?');
                $forsok = $request->request->get('forsok');
                if( $forsok && is_numeric( $forsok ) ) {
                	$forsok++;
                } else {
                	$forsok = 1;
                }

                #throw new Exception('Token does not exist in DB');
                return $this->redirect($this->get('router')->generate('ukm_dip_login').'?forsok='.$forsok);
    			// return $this->render('UKMDipBundle:Default:index.html.twig', array('name' => 'Token does not exist'));
    		}
    	}
    	else {
    		$session = new Session();
    		$session->start();
    	}

		// Generate token entity
		$token = new Token();
		// Update session with token
		$session->set('token', $token->getToken());
		// Update database with the new token
		$em = $this->getDoctrine()->getManager();
    	$em->persist($token);
    	$em->flush();
		
		// Send token to Delta
		$curl->post(array('location' => $location, 'token' => $token->getToken()));
		$res = $curl->process($this->dipURL);
		// var_dump($res); 
    	
		// Redirect to Delta
    	// $url = 'http://delta.ukm.dev/web/app_dev.php/login?token='.$token->getToken().'&rdirurl=ambassador';
        #$url = 'http://delta.'. ($this->getParameter('UKM_HOSTNAME') == 'ukm.dev' ? 'ukm.dev'.'/web/app_dev.php' : $this->getParameter('UKM_HOSTNAME')) . '/login?token='.$token->getToken().'&rdirurl=ambassador';
    	
        #$url = $this->deltaLoginURL.'?token='.$token->getToken().'&rdirurl='.$location;
        $url = $this->deltaLoginURL.'?token='.$token->getToken().'&rdirurl='.$location;
        return $this->redirect($url);
    	// var_dump($token);
    	// var_dump($session);

    	// return $this->render('UKMDipBundle:Default:index.html.twig', array('name' => 'LoginTesting'));
    }

    public function receiveAction() {
		// Receives a JSON-object in a POST-request from Delta
		// This is all user-data, plus a token

    	$request = Request::CreateFromGlobals();

    	$data = json_decode($request->request->get('json'));
    	#$data = json_decode($request->query->get('json'));
    	#var_dump($data);
    	$repo = $this->getDoctrine()->getRepository('UKMDipBundle:Token');
    	$existingToken = $repo->findOneBy(array('token' => $data->token));
    	// Set token as authenticated
    	if (!$existingToken) throw new Exception('Token does not exist', 20005);
    	$existingToken->setAuth(true);
    	$existingToken->setUserId($data->delta_id);

    	$em = $this->getDoctrine()->getManager();
    	$em->persist($existingToken);
    	$em->flush();

    	// Find or update user
    	$userRepo = $this->getDoctrine()->getRepository('UKMDipBundle:User');
    	$user = $userRepo->findOneBy(array('deltaId' => $data->delta_id));
    	if (!$user) {
			// Hvis user ikke finnes
    		$user = new User();
    	}

        // Vi har ikke nødvendigvis mottatt all data, så her bør det sjekkes. Kan også lagre null.
    	$user->setDeltaId($data->delta_id);
        if($data->email)
            $user->setEmail($data->email);
        if($data->phone)
            $user->setPhone($data->phone);
        if($data->address)
            $user->setAddress($data->address);
        if($data->post_number)
            $user->setPostNumber($data->post_number);
		if($data->post_place)
            $user->setPostPlace($data->post_place);
		if($data->first_name)  
            $user->setFirstName($data->first_name);
		if($data->last_name)
            $user->setLastName($data->last_name);
        if($data->facebook_id)
		  $user->setFacebookId($data->facebook_id);
		if($data->facebook_id_unencrypted)
            $user->setFacebookIdUnencrypted($data->facebook_id_unencrypted);
		if($data->facebook_access_token)
            $user->setFacebookAccessToken($data->facebook_access_token);

		$time = new DateTime();
		$user->setBirthdate($time->getTimestamp());
		#$user->setBirthdate($data['birthdate']);
		// TODO: Set birthdate

		$em->persist($user);
		$em->flush();

    	return $this->render('UKMDipBundle:Default:index.html.twig', array('name' => 'Received'));
    }

}
