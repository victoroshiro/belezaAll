<?php

namespace BlzBundle\Entity;

/**
 * Plan
 */
class Plan
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var integer
     */
    private $numberOfServices;

    /**
     * @var integer
     */
    private $numberOfAds;

    /**
     * @var boolean
     */
    private $active = '1';

    /**
     * @var integer
     */
    private $id;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return Plan
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Plan
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return Plan
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
     * Set numberOfServices
     *
     * @param integer $numberOfServices
     *
     * @return Plan
     */
    public function setNumberOfServices($numberOfServices)
    {
        $this->numberOfServices = $numberOfServices;

        return $this;
    }

    /**
     * Get numberOfServices
     *
     * @return integer
     */
    public function getNumberOfServices()
    {
        return $this->numberOfServices;
    }

    /**
     * Set numberOfAds
     *
     * @param integer $numberOfAds
     *
     * @return Plan
     */
    public function setNumberOfAds($numberOfAds)
    {
        $this->numberOfAds = $numberOfAds;

        return $this;
    }

    /**
     * Get numberOfAds
     *
     * @return integer
     */
    public function getNumberOfAds()
    {
        return $this->numberOfAds;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Plan
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
}

