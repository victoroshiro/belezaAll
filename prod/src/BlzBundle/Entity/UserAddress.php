<?php

namespace BlzBundle\Entity;

/**
 * UserAddress
 */
class UserAddress
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\Address
     */
    private $address;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $user;


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
     * Set address
     *
     * @param \BlzBundle\Entity\Address $address
     *
     * @return UserAddress
     */
    public function setAddress(\BlzBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \BlzBundle\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set user
     *
     * @param \BlzBundle\Entity\User $user
     *
     * @return UserAddress
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

