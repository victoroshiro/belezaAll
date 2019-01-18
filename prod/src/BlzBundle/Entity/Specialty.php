<?php

namespace BlzBundle\Entity;

/**
 * Specialty
 */
class Specialty
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
     * @var boolean
     */
    private $active = '1';

    /**
     * @var integer
     */
    private $priority;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\Category
     */
    private $category;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return Specialty
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
     * @return Specialty
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
     * Set active
     *
     * @param boolean $active
     *
     * @return Specialty
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
     * Set priority
     *
     * @param integer $priority
     *
     * @return Specialty
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
     * Set category
     *
     * @param \BlzBundle\Entity\Category $category
     *
     * @return Specialty
     */
    public function setCategory(\BlzBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \BlzBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}

