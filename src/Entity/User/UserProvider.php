<?php

namespace App\Entity\User;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use App\Entity\User\User;

use Symfony\Component\Security\Core\Exception;

class UserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    protected $em;
    protected $factory;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;

    }


    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {


        $data=$response->getData();


        $id=$data['sub'];

        if (isset($data['name']))
            $name=$data['name'];
        else
            $name='anonymous';

        if (isset($data['email']))
        $email=$data['email'];
        else
           $email='anonymous@anonymous.org';


        if (isset($data['eduperson_entitlement']))
        $opRoles= $data['eduperson_entitlement'];
        else
            $opRoles=$data;

        $roles= ['ROLE_USER'];


        $existingUser=$this->em->getRepository(\App\Entity\User\User::class)->findOneBy(array('id'=>$id));

        if ($existingUser!=null)
        {
            $user=$existingUser;
        }
        else {
            $user = new \App\Entity\User\User();
            $user->SetId($id);
            $user->setEmail($email);
            $user->setUsername($name);
            $user->setRoles($roles);
            $user->setopRoles($opRoles);
        }


            try {
                $this->em->persist($user);
                $this->em->flush();
            } catch (ORMException $e) {
           }


            return $user;

    }

    public function loadUserByUsername($username)
    {

        $existingUser=$this->em->getRepository(\App\Entity\User\User::class)->findOneBy(array('username'=>$username));
        if ($existingUser!=null)
            return $existingUser;
        else
            return new User();
    }


    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }



    public function supportsClass($class): bool
    {
        return $class === 'App\Entity\User\User';
    }


    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // TODO: Implement loadUserByIdentifier() method.
    }
}

