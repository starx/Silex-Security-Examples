<?php

namespace Starx\SilexDocker\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ActiveUserVoter implements VoterInterface
{
    private $decisionManager;

    private $usersData = [];

    public function __construct($usersDb) {
        $this->usersData = $usersDb;
    }

    public function supportsAttribute($attribute)
    {
        return true;
        // TODO: Implement supportsAttribute() method.
    }

    public function supportsClass($class)
    {
        return true;
        // TODO: Implement supportsClass() method.
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $user = $token->getUser();
        $userDetails = $this->usersData[$user->getUsername()];
        $customActive = $userDetails['custom_active_check'] === true;
        if($customActive) {
            return VoterInterface::ACCESS_GRANTED; 
        }

        $request = $object;
        $request
            ->attributes->set('_access_denied_reason', 'Customer active user voter denied.');
        return VoterInterface::ACCESS_DENIED;
    }

}
