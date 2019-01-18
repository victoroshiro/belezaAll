<?php

namespace BlzBundle\Entity;

/**
 * Chat
 */
class Chat
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var \DateTime
     */
    private $datetime;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\ChatRoom
     */
    private $chatRoom;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $fromUser;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $toUser;


    /**
     * Set message
     *
     * @param string $message
     *
     * @return Chat
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     *
     * @return Chat
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Get datetime
     *
     * @return \DateTime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set chatRoom
     *
     * @param \BlzBundle\Entity\ChatRoom $chatRoom
     *
     * @return Chat
     */
    public function setChatRoom(\BlzBundle\Entity\ChatRoom $chatRoom = null)
    {
        $this->chatRoom = $chatRoom;

        return $this;
    }

    /**
     * Get chatRoom
     *
     * @return \BlzBundle\Entity\ChatRoom
     */
    public function getChatRoom()
    {
        return $this->chatRoom;
    }

    /**
     * Set fromUser
     *
     * @param \BlzBundle\Entity\User $fromUser
     *
     * @return Chat
     */
    public function setFromUser(\BlzBundle\Entity\User $fromUser = null)
    {
        $this->fromUser = $fromUser;

        return $this;
    }

    /**
     * Get fromUser
     *
     * @return \BlzBundle\Entity\User
     */
    public function getFromUser()
    {
        return $this->fromUser;
    }

    /**
     * Set toUser
     *
     * @param \BlzBundle\Entity\User $toUser
     *
     * @return Chat
     */
    public function setToUser(\BlzBundle\Entity\User $toUser = null)
    {
        $this->toUser = $toUser;

        return $this;
    }

    /**
     * Get toUser
     *
     * @return \BlzBundle\Entity\User
     */
    public function getToUser()
    {
        return $this->toUser;
    }
}

