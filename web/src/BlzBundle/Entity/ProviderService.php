<?php

namespace BlzBundle\Entity;

/**
 * ProviderService
 */
class ProviderService
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
     * @var integer
     */
    private $time;

    /**
     * @var boolean
     */
    private $active = '1';

    /**
     * @var boolean
     */
    private $deleted = '0';

    /**
     * @var integer
     */
    private $priority;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\Specialty
     */
    private $specialty;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $user;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return ProviderService
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
     * @return ProviderService
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
     * Set time
     *
     * @param integer $time
     *
     * @return ProviderService
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return integer
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return ProviderService
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
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return ProviderService
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     *
     * @return ProviderService
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
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
     * Set specialty
     *
     * @param \BlzBundle\Entity\Specialty $specialty
     *
     * @return ProviderService
     */
    public function setSpecialty(\BlzBundle\Entity\Specialty $specialty = null)
    {
        $this->specialty = $specialty;

        return $this;
    }

    /**
     * Get specialty
     *
     * @return \BlzBundle\Entity\Specialty
     */
    public function getSpecialty()
    {
        return $this->specialty;
    }

    /**
     * Set user
     *
     * @param \BlzBundle\Entity\User $user
     *
     * @return ProviderService
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

