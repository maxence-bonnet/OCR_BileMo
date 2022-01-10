<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;  
use Symfony\Component\HttpFoundation\Request;

class UserRolesController
{
    const ROLE_USER = '';
    const ROLE_CLIENT_ADMIN = 'ROLE_CLIENT_ADMIN';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public function __construct(private EntityManagerInterface $entityManager) {}

    public function __invoke(User $data, Request $request): User
    {
        $user = $data;

        if (key_exists(self::ROLE_SUPER_ADMIN, $user->getRoles())) {
            return $data;
        }

        if (str_contains($request->getRequestUri(), 'upgrade') && !key_exists(self::ROLE_CLIENT_ADMIN, $user->getRoles())) {
            $roles[] = self::ROLE_CLIENT_ADMIN;
        } elseif (str_contains($request->getRequestUri(), 'downgrade') && !key_exists(self::ROLE_USER, $user->getRoles())) {
            $roles[] = self::ROLE_USER;
        }

        $user->setRoles($roles);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $data;
    }
}