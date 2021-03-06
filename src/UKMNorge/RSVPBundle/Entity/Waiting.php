<?php

namespace UKMNorge\RSVPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Waiting
 *
 * @ORM\Table(name="waiting")
 * @ORM\Entity(repositoryClass="UKMNorge\RSVPBundle\Repository\WaitingRepository")
 */
class Waiting
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

   /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var int
     *
     * @ORM\Column(name="user", type="integer")
     */
    private $user;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set eventId
     *
     * @param integer $eventId
     *
     * @return Waiting
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set user
     *
     * @param integer $user
     *
     * @return Waiting
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set event
     *
     * @param \UKMNorge\RSVPBundle\Entity\Event $event
     *
     * @return Waiting
     */
    public function setEvent(\UKMNorge\RSVPBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \UKMNorge\RSVPBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }
}
