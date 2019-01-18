<?php

namespace BlzBundle\Entity;

/**
 * ChatRoom
 */
class ChatRoom
{
    /**
     * @var string
     */
    private $lastMessage;

    /**
     * @var \DateTime
     */
    private $lastDatetime;

    /**
     * @var boolean
     */
    private $active = '1';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $user;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $provider;


    /**
     * Set lastMessage
     *
     * @param string $lastMessage
     *
     * @return ChatRoom
     */
    public function setLastMessage($lastMessage)
    {
        $this->lastMessage = $lastMessage;

        return $this;
    }

    /**
     * Get lastMessage
     *
     * @return string
     */
    public function getLastMessage()
    {
        return $this->lastMessage;
    }

    /**
     * Set lastDatetime
     *
     * @param \DateTime $lastDatetime
     *
     * @return ChatRoom
     */
    public function setLastDatetime($lastDatetime)
    {
        $this->lastDatetime = $lastDatetime;

        return $this;
    }

    /**
     * Get lastDatetime
     *
     * @return \DateTime
     */
    public function getLastDatetime()
    {
        return $this->lastDatetime;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return ChatRoom
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
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
     * Set user
     *
     * @param \BlzBundle\Entity\User $user
     *
     * @return ChatRoom
     */
    public function setUser(\BlzBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \BlzBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set provider
     *
     * @param \BlzBundle\Entity\User $provider
     *
     * @return ChatRoom
     */
    public function setProvider(\BlzBundle\Entity\User $provider = null)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @return \BlzBundle\Entity\User
     */
    public function getProvider()
    {
        return $this->provider;
    }
}

