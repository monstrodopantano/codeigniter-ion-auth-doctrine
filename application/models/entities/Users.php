<?php

namespace models\entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Users
 *
 * @ORM\Table(name="users", indexes={@ORM\Index(name="logins_index", columns={"username", "email"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Users
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=15)
     */
    private $ip_address;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=100)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=40, nullable=true)
     */
    private $salt;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="activation_code", type="string", length=40, nullable=true)
     */
    private $activation_code;

    /**
     * @var string
     *
     * @ORM\Column(name="forgotten_password_code", type="string", length=40, nullable=true)
     */
    private $forgotten_password_code;

    /**
     * @var integer
     *
     * @ORM\Column(name="forgotten_password_time", type="integer", nullable=true)
     */
    private $forgotten_password_time;

    /**
     * @var string
     *
     * @ORM\Column(name="remember_code", type="string", length=40, nullable=true)
     */
    private $remember_code;

    /**
     * @var integer
     *
     * @ORM\Column(name="created_on", type="integer")
     */
    private $created_on;

    /**
     * @var integer
     *
     * @ORM\Column(name="last_login", type="integer", nullable=true)
     */
    private $last_login;

    /**
     * @var integer
     *
     * @ORM\Column(name="active", type="integer", nullable=true)
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=50, nullable=true)
     */
    public $first_name;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=50, nullable=true)
     */
    public $last_name;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=100, nullable=true)
     */
    public $company;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     */
    public $phone;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="models\entities\Users_groups", mappedBy="users", cascade={"persist","merge"})
     */
    private $users_groups;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users_groups = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set ipAddress
     *
     * @param string $ipAddress
     *
     * @return Users
     */
    public function setIpAddress($ipAddress)
    {
        $this->ip_address = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return Users
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Users
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
     * Set salt
     *
     * @param string $salt
     *
     * @return Users
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Users
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
     * Set activationCode
     *
     * @param string $activationCode
     *
     * @return Users
     */
    public function setActivationCode($activationCode)
    {
        $this->activation_code = $activationCode;

        return $this;
    }

    /**
     * Get activationCode
     *
     * @return string
     */
    public function getActivationCode()
    {
        return $this->activation_code;
    }

    /**
     * Set forgottenPasswordCode
     *
     * @param string $forgottenPasswordCode
     *
     * @return Users
     */
    public function setForgottenPasswordCode($forgottenPasswordCode)
    {
        $this->forgotten_password_code = $forgottenPasswordCode;

        return $this;
    }

    /**
     * Get forgottenPasswordCode
     *
     * @return string
     */
    public function getForgottenPasswordCode()
    {
        return $this->forgotten_password_code;
    }

    /**
     * Set forgottenPasswordTime
     *
     * @param integer $forgottenPasswordTime
     *
     * @return Users
     */
    public function setForgottenPasswordTime($forgottenPasswordTime)
    {
        $this->forgotten_password_time = $forgottenPasswordTime;

        return $this;
    }

    /**
     * Get forgottenPasswordTime
     *
     * @return integer
     */
    public function getForgottenPasswordTime()
    {
        return $this->forgotten_password_time;
    }

    /**
     * Set rememberCode
     *
     * @param string $rememberCode
     *
     * @return Users
     */
    public function setRememberCode($rememberCode)
    {
        $this->remember_code = $rememberCode;

        return $this;
    }

    /**
     * Get rememberCode
     *
     * @return string
     */
    public function getRememberCode()
    {
        return $this->remember_code;
    }

    /**
     * Set createdOn
     *
     * @param integer $createdOn
     *
     * @return Users
     */
    public function setCreatedOn($createdOn)
    {
        $this->created_on = $createdOn;

        return $this;
    }

    /**
     * Get createdOn
     *
     * @return integer
     */
    public function getCreatedOn()
    {
        return $this->created_on;
    }

    /**
     * Set lastLogin
     *
     * @param integer $lastLogin
     *
     * @return Users
     */
    public function setLastLogin($lastLogin)
    {
        $this->last_login = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return integer
     */
    public function getLastLogin()
    {
        return $this->last_login;
    }

    /**
     * Set active
     *
     * @param integer $active
     *
     * @return Users
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return integer
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Users
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Users
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set company
     *
     * @param string $company
     *
     * @return Users
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Users
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
     * Add usersGroup
     *
     * @param \models\entities\Users_groups $usersGroup
     *
     * @return Users
     */
    public function addUsersGroup(\models\entities\Users_groups $usersGroup)
    {
        $this->users_groups[] = $usersGroup;

        return $this;
    }

    /**
     * Remove usersGroup
     *
     * @param \models\entities\Users_groups $usersGroup
     */
    public function removeUsersGroup(\models\entities\Users_groups $usersGroup)
    {
        $this->users_groups->removeElement($usersGroup);
    }

    /**
     * Get usersGroups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsersGroups()
    {
        return $this->users_groups;
    }
    /**
     * @ORM\PrePersist
     */
    public function doStuffOnPrePersist()
    {
        // Add your code here
    }

    /**
     * @ORM\PrePersist
     */
    public function doOtherStuffOnPrePersistToo()
    {
        // Add your code here
    }

    /**
     * @ORM\PostPersist
     */
    public function doStuffOnPostPersist()
    {
        // Add your code here
    }
}

