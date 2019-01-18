<?php

namespace BlzBundle\Entity;

/**
 * ProviderServicePrice
 */
class ProviderServicePrice
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
     * @var \BlzBundle\Entity\ProviderPayment
     */
    private $providerPayment;

    /**
     * @var \BlzBundle\Entity\ProviderService
     */
    private $providerService;


    /**
     * Set price
     *
     * @param float $price
     *
     * @return ProviderServicePrice
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
     * Set providerPayment
     *
     * @param \BlzBundle\Entity\ProviderPayment $providerPayment
     *
     * @return ProviderServicePrice
     */
    public function setProviderPayment(\BlzBundle\Entity\ProviderPayment $providerPayment = null)
    {
        $this->providerPayment = $providerPayment;

        return $this;
    }

    /**
     * Get providerPayment
     *
     * @return \BlzBundle\Entity\ProviderPayment
     */
    public function getProviderPayment()
    {
        return $this->providerPayment;
    }

    /**
     * Set providerService
     *
     * @param \BlzBundle\Entity\ProviderService $providerService
     *
     * @return ProviderServicePrice
     */
    public function setProviderService(\BlzBundle\Entity\ProviderService $providerService = null)
    {
        $this->providerService = $providerService;

        return $this;
    }

    /**
     * Get providerService
     *
     * @return \BlzBundle\Entity\ProviderService
     */
    public function getProviderService()
    {
        return $this->providerService;
    }
}

