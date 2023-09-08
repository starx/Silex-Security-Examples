<?php
namespace App;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RouteVoter extends Voter {
    protected function supports($attribute, $subject) {
        // Check if the attribute (in our case a route) is in the list we support
        return in_array($attribute, ['ADMIN', 'USER']);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        $user = $token->getUser();

        switch ($attribute) {
            case 'ADMIN':
                return in_array('ROLE_ADMIN', $user->getRoles());
            case 'USER':
                return in_array('ROLE_USER', $user->getRoles());
        }

        return false;
    }
}