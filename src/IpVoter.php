<?php

namespace App;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class IpVoter extends Voter implements VoterInterface {
    private $requestStack;
    private $whitelistedIps;

    public function __construct(RequestStack $requestStack, array $whitelistedIps) {
        $this->requestStack = $requestStack;
        $this->whitelistedIps = $whitelistedIps;
    }

    protected function supports($attribute, $subject) {
        // Here, we're checking for a specific attribute named 'WHITELISTED_IP'
        return $attribute === 'WHITELISTED_IP';
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        $currentIp = $this->requestStack->getCurrentRequest()->getClientIp();
        return in_array($currentIp, $this->whitelistedIps);
    }
}