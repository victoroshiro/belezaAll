<?php

namespace BlzBundle\Entity;

/**
 * City
 */
class City
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\State
     */
    private $state;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return City
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set state
     *
     * @param \BlzBundle\Entity\State $state
     *
     * @return City
     */
    public function setState(\BlzBundle\Entity\State $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return \BlzBundle\Entity\State
     */
    public function getState()
    {
        return $this->state;
    }
}

