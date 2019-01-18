<?php

namespace BlzBundle\Entity;

/**
 * AvailableDate
 */
class AvailableDate
{
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $user;


    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return AvailableDate
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
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
     * @return AvailableDate
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
}

