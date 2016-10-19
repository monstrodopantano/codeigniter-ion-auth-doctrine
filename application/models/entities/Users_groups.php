<?php

namespace models\entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Users_groups
 *
 * @ORM\Table(name="users_groups")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Users_groups
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
     * @var integer
     *
     * @ORM\Column(name="group_id", type="integer")
     */
    private $group_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $user_id;

    /**
     * @var \models\entities\Groups
     *
     * @ORM\ManyToOne(targetEntity="models\entities\Groups", inversedBy="users_groups")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     * })
     */
    private $groups;

    /**
     * @var \models\entities\Users
     *
     * @ORM\ManyToOne(targetEntity="models\entities\Users", inversedBy="users_groups")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $users;


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
     * Set groupId
     *
     * @param integer $groupId
     *
     * @return Users_groups
     */
    public function setGroupId($groupId)
    {
        $this->group_id = $groupId;

        return $this;
    }

    /**
     * Get groupId
     *
     * @return integer
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return Users_groups
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set groups
     *
     * @param \models\entities\Groups $groups
     *
     * @return Users_groups
     */
    public function setGroups(\models\entities\Groups $groups = null)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Get groups
     *
     * @return \models\entities\Groups
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set users
     *
     * @param \models\entities\Users $users
     *
     * @return Users_groups
     */
    public function setUsers(\models\entities\Users $users = null)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Get users
     *
     * @return \models\entities\Users
     */
    public function getUsers()
    {
        return $this->users;
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

