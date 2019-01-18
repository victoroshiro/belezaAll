<?php

namespace BlzBundle\Entity;

/**
 * FranchiseeData
 */
class FranchiseeData
{
    /**
     * @var string
     */
    private $name;

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
    private $bank;

    /**
     * @var string
     */
    private $agency;

    /**
     * @var string
     */
    private $account;

    /**
     * @var string
     */
    private $nameBank;

    /**
     * @var string
     */
    private $cpfBank;

    /**
     * @var string
     */
    private $cnpjBank;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \BlzBundle\Entity\Address
     */
    private $address;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return FranchiseeData
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
     * Set cpf
     *
     * @param string $cpf
     *
     * @return FranchiseeData
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
     * @return FranchiseeData
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
     * @return FranchiseeData
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
     * @return FranchiseeData
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
     * @return FranchiseeData
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
     * Set bank
     *
     * @param string $bank
     *
     * @return FranchiseeData
     */
    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    /**
     * Get bank
     *
     * @return string
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * Set agency
     *
     * @param string $agency
     *
     * @return FranchiseeData
     */
    public function setAgency($agency)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return string
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Set account
     *
     * @param string $account
     *
     * @return FranchiseeData
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set nameBank
     *
     * @param string $nameBank
     *
     * @return FranchiseeData
     */
    public function setNameBank($nameBank)
    {
        $this->nameBank = $nameBank;

        return $this;
    }

    /**
     * Get nameBank
     *
     * @return string
     */
    public function getNameBank()
    {
        return $this->nameBank;
    }

    /**
     * Set cpfBank
     *
     * @param string $cpfBank
     *
     * @return FranchiseeData
     */
    public function setCpfBank($cpfBank)
    {
        $this->cpfBank = $cpfBank;

        return $this;
    }

    /**
     * Get cpfBank
     *
     * @return string
     */
    public function getCpfBank()
    {
        return $this->cpfBank;
    }

    /**
     * Set cnpjBank
     *
     * @param string $cnpjBank
     *
     * @return FranchiseeData
     */
    public function setCnpjBank($cnpjBank)
    {
        $this->cnpjBank = $cnpjBank;

        return $this;
    }

    /**
     * Get cnpjBank
     *
     * @return string
     */
    public function getCnpjBank()
    {
        return $this->cnpjBank;
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
     * @return FranchiseeData
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
}

