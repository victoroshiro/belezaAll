<?php

namespace BlzBundle\Entity;

/**
 * AvailableTime
 */
class AvailableTime
{
    /**
     * @var \DateTime
     */
    private $time;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\AvailableDate
     */
    private $date;


    /**
     * Set time
     *
     * @param \DateTime $time
     *
     * @return AvailableTime
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \BlzBundle\Entity\AvailableDate $date
     *
     * @return AvailableTime
     */
    public function setDate(\BlzBundle\Entity\AvailableDate $date = null)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \BlzBundle\Entity\AvailableDate
     */
    public function getDate()
    {
        return $this->date;
    }
}

