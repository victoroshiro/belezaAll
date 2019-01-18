<?php

namespace BlzBundle\Entity;

/**
 * ProviderPayment
 */
class ProviderPayment
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\PaymentMethod
     */
    private $paymentMethod;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $provider;


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
     * Set paymentMethod
     *
     * @param \BlzBundle\Entity\PaymentMethod $paymentMethod
     *
     * @return ProviderPayment
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
     * Set provider
     *
     * @param \BlzBundle\Entity\User $provider
     *
     * @return ProviderPayment
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

