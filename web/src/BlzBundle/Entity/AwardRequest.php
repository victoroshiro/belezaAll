<?php

namespace BlzBundle\Entity;

/**
 * AwardRequest
 */
class AwardRequest
{
    /**
     * @var integer
     */
    private $points;

    /**
     * @var boolean
     */
    private $delivered = '0';

    /**
     * @var \DateTime
     */
    private $datetime;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\Address
     */
    private $address;

    /**
     * @var \BlzBundle\Entity\Award
     */
    private $award;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $user;


    /**
     * Set points
     *
     * @param integer $points
     *
     * @return AwardRequest
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     *
     * @return integer
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set delivered
     *
     * @param boolean $delivered
     *
     * @return AwardRequest
     */
    public function setDelivered($delivered)
    {
        $this->delivered = $delivered;

        return $this;
    }

    /**
     * Get delivered
     *
     * @return boolean
     */
    public function getDelivered()
    {
        return $this->delivered;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     *
     * @return AwardRequest
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
     * Set address
     *
     * @param \BlzBundle\Entity\Address $address
     *
     * @return AwardRequest
     */
    public function setAddress(\BlzBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \BlzBundle\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set award
     *
     * @param \BlzBundle\Entity\Award $award
     *
     * @return AwardRequest
     */
    public function setAward(\BlzBundle\Entity\Award $award = null)
    {
        $this->award = $award;

        return $this;
    }

    /**
     * Get award
     *
     * @return \BlzBundle\Entity\Award
     */
    public function getAward()
    {
        return $this->award;
    }

    /**
     * Set user
     *
     * @param \BlzBundle\Entity\User $user
     *
     * @return AwardRequest
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

