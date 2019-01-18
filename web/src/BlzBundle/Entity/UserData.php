<?php

namespace BlzBundle\Entity;

/**
 * UserData
 */
class UserData
{
    /**
     * @var string
     */
    private $rg;

    /**
     * @var string
     */
    private $cpf;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $celphone;

    /**
     * @var boolean
     */
    private $social = '0';

    /**
     * @var string
     */
    private $photo;

    /**
     * @var string
     */
    private $push;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set rg
     *
     * @param string $rg
     *
     * @return UserData
     */
    public function setRg($rg)
    {
        $this->rg = $rg;

        return $this;
    }

    /**
     * Get rg
     *
     * @return string
     */
    public function getRg()
    {
        return $this->rg;
    }

    /**
     * Set cpf
     *
     * @param string $cpf
     *
     * @return UserData
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
     * Set phone
     *
     * @param string $phone
     *
     * @return UserData
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
     * @return UserData
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
     * Set social
     *
     * @param boolean $social
     *
     * @return UserData
     */
    public function setSocial($social)
    {
        $this->social = $social;

        return $this;
    }

    /**
     * Get social
     *
     * @return boolean
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * Set photo
     *
     * @param string $photo
     *
     * @return UserData
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
     * Set push
     *
     * @param string $push
     *
     * @return UserData
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}

