<?php

namespace BlzBundle\Entity;

/**
 * Scheduling
 */
class Scheduling
{
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \DateTime
     */
    private $time;

    /**
     * @var boolean
     */
    private $homeCare = '0';

    /**
     * @var string
     */
    private $notes;

    /**
     * @var string
     */
    private $datetime;

    /**
     * @var float
     */
    private $rating;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\Address
     */
    private $address;

    /**
     * @var \BlzBundle\Entity\Finance
     */
    private $amount;

    /**
     * @var \BlzBundle\Entity\Finance
     */
    private $franchiseeTax;

    /**
     * @var \BlzBundle\Entity\Finance
     */
    private $systemTax;

    /**
     * @var \BlzBundle\Entity\PaymentMethod
     */
    private $paymentMethod;

    /**
     * @var \BlzBundle\Entity\SchedulingStatus
     */
    private $status;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $user;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $provider;


    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Scheduling
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
     * Set time
     *
     * @param \DateTime $time
     *
     * @return Scheduling
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set homeCare
     *
     * @param boolean $homeCare
     *
     * @return Scheduling
     */
    public function setHomeCare($homeCare)
    {
        $this->homeCare = $homeCare;

        return $this;
    }

    /**
     * Get homeCare
     *
     * @return boolean
     */
    public function getHomeCare()
    {
        return $this->homeCare;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Scheduling
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set datetime
     *
     * @param string $datetime
     *
     * @return Scheduling
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Get datetime
     *
     * @return string
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set rating
     *
     * @param float $rating
     *
     * @return Scheduling
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return float
     */
    public function getRating()
    {
        return $this->rating;
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
     * @return Scheduling
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
     * Set amount
     *
     * @param \BlzBundle\Entity\Finance $amount
     *
     * @return Scheduling
     */
    public function setAmount(\BlzBundle\Entity\Finance $amount = null)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return \BlzBundle\Entity\Finance
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set franchiseeTax
     *
     * @param \BlzBundle\Entity\Finance $franchiseeTax
     *
     * @return Scheduling
     */
    public function setFranchiseeTax(\BlzBundle\Entity\Finance $franchiseeTax = null)
    {
        $this->franchiseeTax = $franchiseeTax;

        return $this;
    }

    /**
     * Get franchiseeTax
     *
     * @return \BlzBundle\Entity\Finance
     */
    public function getFranchiseeTax()
    {
        return $this->franchiseeTax;
    }

    /**
     * Set systemTax
     *
     * @param \BlzBundle\Entity\Finance $systemTax
     *
     * @return Scheduling
     */
    public function setSystemTax(\BlzBundle\Entity\Finance $systemTax = null)
    {
        $this->systemTax = $systemTax;

        return $this;
    }

    /**
     * Get systemTax
     *
     * @return \BlzBundle\Entity\Finance
     */
    public function getSystemTax()
    {
        return $this->systemTax;
    }

    /**
     * Set paymentMethod
     *
     * @param \BlzBundle\Entity\PaymentMethod $paymentMethod
     *
     * @return Scheduling
     */
    public function setPaymentMethod(\BlzBundle\Entity\PaymentMethod $paymentMethod = null)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get paymentMethod
     *
     * @return \BlzBundle\Entity\PaymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Set status
     *
     * @param \BlzBundle\Entity\SchedulingStatus $status
     *
     * @return Scheduling
     */
    public function setStatus(\BlzBundle\Entity\SchedulingStatus $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \BlzBundle\Entity\SchedulingStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set user
     *
     * @param \BlzBundle\Entity\User $user
     *
     * @return Scheduling
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
     * @return Scheduling
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

