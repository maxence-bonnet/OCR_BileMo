<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    private const DELETE = 'DELETE';

    public function __construct(private Security $security)
    {

    }

    protected function supports(string $attribute, $subject): bool
    {
        $supportsAttribute = in_array($attribute, [self::DELETE]);
        $supportsSubject = $subject instanceof User;

        return $supportsAttribute && $supportsSubject;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof User) {
            return false;
        }

        /** @var User $user */
        $user = $subject;
          
        if ($this->security->isGranted('ROLE_ADMIN')) {
            if ($attribute === self::DELETE) {
                return $this->notSelfDelete($currentUser, $user);
            }
            return true;
        }

        /**
         * Should not be necessary but just in case
         */
        if (null === $currentUser->getClient()) {
            return false;
        }

        switch ($attribute) {
            case self::DELETE:
                return $this->canDelete($currentUser, $user);
                break;
            default:
                return false;
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * Returns true if current User's Client is the same as deleted User's Client,
     * and if it is not a self delete. Anyway, due to App\Doctrine\CurrentUserExtension,
     * authenticated Users should not even be able to find Users form foreign Clients.
     * 
     * @param User $currentUser
     * @param User $user
     * 
     * @return bool
     */
    private function canDelete(User $currentUser, User $user): bool
    {
        $sameClient = $currentUser->getClient()->getId() === $user->getClient()->getId();
        return $sameClient && $this->notSelfDelete($currentUser, $user);
    }

    /**
     * Returns true if it is not a self delete
     * 
     * @param User $currentUser
     * @param User $user
     * 
     * @return bool
     */
    private function notSelfDelete(User $currentUser, User $user): bool
    {
        return $currentUser->getId() !== $user->getId();
    }
}
