<?php

namespace models\entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Groups
 *
 * @ORM\Table(name="groups", indexes={@ORM\Index(name="name_groups_index", columns={"name"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Groups
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
     * @ORM\Column(name="name", type="string", length=20, nullable=false)
     */
    public $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=100, nullable=false)
     */
    public $description;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="models\entities\Users_groups", mappedBy="groups", cascade={"persist","merge"})
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
     * Set name
     *
     * @param string $name
     *
     * @return Groups
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
     * @return Groups
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
     * Add usersGroup
     *
     * @param \models\entities\Users_groups $usersGroup
     *
     * @return Groups
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

