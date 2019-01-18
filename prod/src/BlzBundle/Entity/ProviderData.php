<?php

namespace BlzBundle\Entity;

/**
 * ProviderData
 */
class ProviderData
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
     * @var string
     */
    private $cpf;

    /**
     * @var string
     */
    private $cnpj;

    /**
     * @var \DateTime
     */
    private $birth;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $celphone;

    /**
     * @var string
     */
    private $photo;

    /**
     * @var float
     */
    private $coordX;

    /**
     * @var float
     */
    private $coordY;

    /**
     * @var boolean
     */
    private $homeCare = '0';

    /**
     * @var string
     */
    private $push;

    /**
     * @var string
     */
    private $pushWeb;

    /**
     * @var integer
     */
    private $points = '0';

    /**
     * @var float
     */
    private $rating = '0';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\Address
     */
    private $address;

    /**
     * @var \BlzBundle\Entity\Plan
     */
    private $plan;

    /**
     * @var \BlzBundle\Entity\User
     */
    private $franchisee;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return ProviderData
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
     * @return ProviderData
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
     * Set cpf
     *
     * @param string $cpf
     *
     * @return ProviderData
     */
    public function setCpf($cpf)
    {
        $this->cpf = $cpf;

        return $this;
    }

    /**
     * Get cpf
     *
     * @return string
     */
    public function getCpf()
    {
        return $this->cpf;
    }

    /**
     * Set cnpj
     *
     * @param string $cnpj
     *
     * @return ProviderData
     */
    public function setCnpj($cnpj)
    {
        $this->cnpj = $cnpj;

        return $this;
    }

    /**
     * Get cnpj
     *
     * @return string
     */
    public function getCnpj()
    {
        return $this->cnpj;
    }

    /**
     * Set birth
     *
     * @param \DateTime $birth
     *
     * @return ProviderData
     */
    public function setBirth($birth)
    {
        $this->birth = $birth;

        return $this;
    }

    /**
     * Get birth
     *
     * @return \DateTime
     */
    public function getBirth()
    {
        return $this->birth;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return ProviderData
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set celphone
     *
     * @param string $celphone
     *
     * @return ProviderData
     */
    public function setCelphone($celphone)
    {
        $this->celphone = $celphone;

        return $this;
    }

    /**
     * Get celphone
     *
     * @return string
     */
    public function getCelphone()
    {
        return $this->celphone;
    }

    /**
     * Set photo
     *
     * @param string $photo
     *
     * @return ProviderData
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
     * Set coordX
     *
     * @param float $coordX
     *
     * @return ProviderData
     */
    public function setCoordX($coordX)
    {
        $this->coordX = $coordX;

        return $this;
    }

    /**
     * Get coordX
     *
     * @return float
     */
    public function getCoordX()
    {
        return $this->coordX;
    }

    /**
     * Set coordY
     *
     * @param float $coordY
     *
     * @return ProviderData
     */
    public function setCoordY($coordY)
    {
        $this->coordY = $coordY;

        return $this;
    }

    /**
     * Get coordY
     *
     * @return float
     */
    public function getCoordY()
    {
        return $this->coordY;
    }

    /**
     * Set homeCare
     *
     * @param boolean $homeCare
     *
     * @return ProviderData
     */
    public function setHomeCare($homeCare)
    {
        $this->homeCare = $homeCare;

        return $this;
    }

    /**
     * Get homeCare
     *
     * @return boolean
     */
    public function getHomeCare()
    {
        return $this->homeCare;
    }

    /**
     * Set push
     *
     * @param string $push
     *
     * @return ProviderData
     */
    public function setPush($push)
    {
        $this->push = $push;

        return $this;
    }

    /**
     * Get push
     *
     * @return string
     */
    public function getPush()
    {
        return $this->push;
    }

    /**
     * Set pushWeb
     *
     * @param string $pushWeb
     *
     * @return ProviderData
     */
    public function setPushWeb($pushWeb)
    {
        $this->pushWeb = $pushWeb;

        return $this;
    }

    /**
     * Get pushWeb
     *
     * @return string
     */
    public function getPushWeb()
    {
        return $this->pushWeb;
    }

    /**
     * Set points
     *
     * @param integer $points
     *
     * @return ProviderData
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
     * Set rating
     *
     * @param float $rating
     *
     * @return ProviderData
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return float
     */
    public function getRating()
    {
        return $this->rating;
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
     * Set address
     *
     * @param \BlzBundle\Entity\Address $address
     *
     * @return ProviderData
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
     * Set plan
     *
     * @param \BlzBundle\Entity\Plan $plan
     *
     * @return ProviderData
     */
    public function setPlan(\BlzBundle\Entity\Plan $plan = null)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Get plan
     *
     * @return \BlzBundle\Entity\Plan
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Set franchisee
     *
     * @param \BlzBundle\Entity\User $franchisee
     *
     * @return ProviderData
     */
    public function setFranchisee(\BlzBundle\Entity\User $franchisee = null)
    {
        $this->franchisee = $franchisee;

        return $this;
    }

    /**
     * Get franchisee
     *
     * @return \BlzBundle\Entity\User
     */
    public function getFranchisee()
    {
        return $this->franchisee;
    }
}

