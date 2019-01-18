<?php

namespace BlzBundle\Entity;

/**
 * SchedulingService
 */
class SchedulingService
{
    /**
     * @var float
     */
    private $price;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\ProviderService
     */
    private $service;

    /**
     * @var \BlzBundle\Entity\Scheduling
     */
    private $scheduling;


    /**
     * Set price
     *
     * @param float $price
     *
     * @return SchedulingService
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
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
     * Set service
     *
     * @param \BlzBundle\Entity\ProviderService $service
     *
     * @return SchedulingService
     */
    public function setService(\BlzBundle\Entity\ProviderService $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return \BlzBundle\Entity\ProviderService
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set scheduling
     *
     * @param \BlzBundle\Entity\Scheduling $scheduling
     *
     * @return SchedulingService
     */
    public function setScheduling(\BlzBundle\Entity\Scheduling $scheduling = null)
    {
        $this->scheduling = $scheduling;

        return $this;
    }

    /**
     * Get scheduling
     *
     * @return \BlzBundle\Entity\Scheduling
     */
    public function getScheduling()
    {
        return $this->scheduling;
    }
}

