<?php

namespace BlzBundle\Entity;

/**
 * Finance
 */
class Finance
{
    /**
     * @var float
     */
    private $amount;

    /**
     * @var \DateTime
     */
    private $datetime;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $user;


    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return Finance
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     *
     * @return Finance
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
     * Set user
     *
     * @param \BlzBundle\Entity\User $user
     *
     * @return Finance
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

