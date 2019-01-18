<?php

namespace BlzBundle\Entity;

/**
 * Address
 */
class Address
{
    /**
     * @var string
     */
    private $cep;

    /**
     * @var string
     */
    private $street;

    /**
     * @var string
     */
    private $neighborhood;

    /**
     * @var integer
     */
    private $number;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\City
     */
    private $city;


    /**
     * Set cep
     *
     * @param string $cep
     *
     * @return Address
     */
    public function setCep($cep)
    {
        $this->cep = $cep;

        return $this;
    }

    /**
     * Get cep
     *
     * @return string
     */
    public function getCep()
    {
        return $this->cep;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Address
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set neighborhood
     *
     * @param string $neighborhood
     *
     * @return Address
     */
    public function setNeighborhood($neighborhood)
    {
        $this->neighborhood = $neighborhood;

        return $this;
    }

    /**
     * Get neighborhood
     *
     * @return string
     */
    public function getNeighborhood()
    {
        return $this->neighborhood;
    }

    /**
     * Set number
     *
     * @param integer $number
     *
     * @return Address
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
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
     * Set city
     *
     * @param \BlzBundle\Entity\City $city
     *
     * @return Address
     */
    public function setCity(\BlzBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \BlzBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }
}

