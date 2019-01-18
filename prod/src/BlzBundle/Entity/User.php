<?php

namespace BlzBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 */
class User implements UserInterface, \Serializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var boolean
     */
    private $active = '1';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\ProviderData
     */
    private $providerData;

    /**
     * @var \BlzBundle\Entity\UserType
     */
    private $type;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
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
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return User
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
     * Set providerData
     *
     * @param \BlzBundle\Entity\ProviderData $providerData
     *
     * @return User
     */
    public function setProviderData(\BlzBundle\Entity\ProviderData $providerData = null)
    {
        $this->providerData = $providerData;

        return $this;
    }

    /**
     * Get providerData
     *
     * @return \BlzBundle\Entity\ProviderData
     */
    public function getProviderData()
    {
        return $this->providerData;
    }

    /**
     * Set type
     *
     * @param \BlzBundle\Entity\UserType $type
     *
     * @return User
     */
    public function setType(\BlzBundle\Entity\UserType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \BlzBundle\Entity\UserType
     */
    public function getType()
    {
        return $this->type;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function getRoles(){
        if(!empty($this->getActive())){
            switch($this->getType()->getId()){
                case 1:
                    return array("ROLE_ADMIN");
                    break;
                case 2:
                    return array("ROLE_FRANCHISEE");
                    break;
                case 3:
                    return array("ROLE_PROVIDER");
                    break;
                default:
                    return array("ROLE_USER");
                    break;
            }
        }
        else{
            return array();
        }
    }

    public function eraseCredentials()
    {

    }

    public function getSalt()
    {
        return null;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
        ));
    }

    public function unserialize($serialized)
    {
        list (
        $this->id,
        $this->email,
        $this->password,
        ) = unserialize($serialized);
    }
    /**
     * @var \DateTime
     */
    private $datetime;


    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     *
     * @return User
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
     * @var \BlzBundle\Entity\FranchiseeData
     */
    private $franchiseeData;


    /**
     * Set franchiseeData
     *
     * @param \BlzBundle\Entity\FranchiseeData $franchiseeData
     *
     * @return User
     */
    public function setFranchiseeData(\BlzBundle\Entity\FranchiseeData $franchiseeData = null)
    {
        $this->franchiseeData = $franchiseeData;

        return $this;
    }

    /**
     * Get franchiseeData
     *
     * @return \BlzBundle\Entity\FranchiseeData
     */
    public function getFranchiseeData()
    {
        return $this->franchiseeData;
    }
    
    /**
     * @var \BlzBundle\Entity\UserData
     */
    private $userData;


    /**
     * Set userData
     *
     * @param \BlzBundle\Entity\UserData $userData
     *
     * @return User
     */
    public function setUserData(\BlzBundle\Entity\UserData $userData = null)
    {
        $this->userData = $userData;

        return $this;
    }

    /**
     * Get userData
     *
     * @return \BlzBundle\Entity\UserData
     */
    public function getUserData()
    {
        return $this->userData;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $address;


    /**
     * Add address
     *
     * @param \BlzBundle\Entity\Address $address
     *
     * @return User
     */
    public function addAddress(\BlzBundle\Entity\Address $address)
    {
        $this->address[] = $address;

        return $this;
    }

    /**
     * Remove address
     *
     * @param \BlzBundle\Entity\Address $address
     */
    public function removeAddress(\BlzBundle\Entity\Address $address)
    {
        $this->address->removeElement($address);
    }

    /**
     * Get address
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAddress()
    {
        return $this->address;
    }
    /**
     * @var boolean
     */
    private $privacyAccepted = '0';


    /**
     * Set privacyAccepted
     *
     * @param boolean $privacyAccepted
     *
     * @return User
     */
    public function setPrivacyAccepted($privacyAccepted)
    {
        $this->privacyAccepted = $privacyAccepted;

        return $this;
    }

    /**
     * Get privacyAccepted
     *
     * @return boolean
     */
    public function getPrivacyAccepted()
    {
        return $this->privacyAccepted;
    }
}
