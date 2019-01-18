<?php

namespace BlzBundle\Entity;

/**
 * UserPoint
 */
class UserPoint
{
    /**
     * @var integer
     */
    private $points;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\Scheduling
     */
    private $scheduling;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $user;


    /**
     * Set points
     *
     * @param integer $points
     *
     * @return UserPoint
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     *
     * @return integer
     */
    public function getPoints()
    {
        return $this->points;
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
     * Set scheduling
     *
     * @param \BlzBundle\Entity\Scheduling $scheduling
     *
     * @return UserPoint
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

    /**
     * Set user
     *
     * @param \BlzBundle\Entity\User $user
     *
     * @return UserPoint
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

