<?php

namespace Starx\SilexDocker\Security\Provider;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;





class UserProvider implements UserProviderInterface
{
    private $usersData = [];

    public function __construct($usersDb) {
        $this->usersData = $usersDb;
    }

    public function loadUserByUsername($username)
    {
        if(empty($this->usersData[$username])) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }
        $userData = $this->usersData[$username];
        return new User(
            $userData['username'],
            $userData['password'],
            $userData['roles']

        );
        // TODO: Implement loadUserByUsername() method.
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }

}