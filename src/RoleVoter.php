<?php
namespace App;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RoleVoter extends Voter {
    protected function supports($attribute, $subject) {
        return in_array($attribute, ['ROLE_ADMIN', 'ROLE_USER']);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        $user = $token->getUser();

        // For this example, simply check if the role exists in user roles.
        // More complex logic can be added here as needed.
        return in_array($attribute, $user->getRoles());
    }
}