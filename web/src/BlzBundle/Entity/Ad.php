<?php

namespace BlzBundle\Entity;

/**
 * Ad
 */
class Ad
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
     * @var \DateTime
     */
    private $validity;

    /**
     * @var string
     */
    private $photo;

    /**
     * @var \DateTime
     */
    private $datetime;

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
    private $provider;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return Ad
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
     * @return Ad
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
     * Set validity
     *
     * @param \DateTime $validity
     *
     * @return Ad
     */
    public function setValidity($validity)
    {
        $this->validity = $validity;

        return $this;
    }

    /**
     * Get validity
     *
     * @return \DateTime
     */
    public function getValidity()
    {
        return $this->validity;
    }

    /**
     * Set photo
     *
     * @param string $photo
     *
     * @return Ad
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     *
     * @return Ad
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
     * Set active
     *
     * @param boolean $active
     *
     * @return Ad
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
     * Set provider
     *
     * @param \BlzBundle\Entity\User $provider
     *
     * @return Ad
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

