<?php
/**
 * Created by PhpStorm.
 * User: cyril
 * Date: 31/07/17
 * Time: 13:11
 */

namespace App\Entity\User;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Util\ClassUtils;


/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */

class User implements UserInterface
{



    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $id;



    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string")

     */
    protected $username;

    /**
     * @ORM\Column(type="json")
     */
    protected $roles=[];


    /**
     * @ORM\Column(type="json")
     */
    protected $opRoles=[];



    /**
     * @return User
     */
    public function SetId($id)
    {
        $this->id= $id;

        return $this;
    }

    public function getUserIdentifier(): string
    {

        return $this->id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getOpRoles(): array
    {
        return $this->opRoles;
    }


    public function setOpRoles(array $roles): self
    {
        $this->opRoles = $roles;
        return $this;
    }


    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {


        $this->roles = array_unique($roles);


        return $this;
    }



    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
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
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    // -------------------------------------------


    /**
     * @return string
     */
    public function serialize()
    {
        return __serialize([$this->id, $this->username, $this->roles,$this->opRoles, $this->email]);
    }

    /**
     * @return void
     */
    public function unserialize($serialized)
    {
        list($this->id, $this->username, $this->roles, $this->opRoles,$this->email) = __unserialize($serialized);
    }






    public function getPassword():?string
    {
        return '';
    }
    /**
     * @see UserInterface
     */
    public function getSalt():?string
    {
        return ''; // not needed for apps that do not check user passwords
    }
    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }



}
