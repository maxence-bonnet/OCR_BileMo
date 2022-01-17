<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Exception\UserSelfDeleteException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    private const DELETE = 'DELETE';

    private const EDIT = 'EDIT';

    public function __construct(private Security $security)
    {

    }

    protected function supports(string $attribute, $subject): bool
    {
        $supportsAttribute = in_array($attribute, [self::DELETE, self::EDIT]);
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
            case self::EDIT:
                return $this->canEdit($currentUser, $user);
                break;
            case self::DELETE:
                return $this->canDelete($currentUser, $user);
                break;
            default:
                return false;
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(User $currentUser, User $user): bool
    {
        return $this->haveSameClient($currentUser, $user);
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
        return $this->haveSameClient($currentUser, $user) && $this->notSelfDelete($currentUser, $user);
    }

    /**
     * Checks if both users have the same Client
     * 
     * @param User $currentUser
     * @param User $user
     * 
     * @return bool
     */
    private function haveSameClient(User $currentUser, User $user): bool
    {
        return $currentUser->getClient()->getId() === $user->getClient()->getId();
    }

    /**
     * Returns true if it is not a self delete
     * 
     * @param User $currentUser
     * @param User $user
     * 
     * @return bool
     * 
     * @throws UserSelfDeleteException
     */
    private function notSelfDelete(User $currentUser, User $user): bool
    {
        if ($currentUser->getId() !== $user->getId()) {
            return true;
        }
        throw new UserSelfDeleteException('For safety reasons, Users are not allowed to delete themself. Please contact an administrator if you are sure you want to do so.');
    }
}
