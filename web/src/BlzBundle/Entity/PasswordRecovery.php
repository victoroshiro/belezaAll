<?php

namespace BlzBundle\Entity;

/**
 * PasswordRecovery
 */
class PasswordRecovery
{
    /**
     * @var string
     */
    private $hash;

    /**
     * @var \DateTime
     */
    private $datetime;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $user;


    /**
     * Set hash
     *
     * @param string $hash
     *
     * @return PasswordRecovery
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     *
     * @return PasswordRecovery
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param \BlzBundle\Entity\User $user
     *
     * @return PasswordRecovery
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

