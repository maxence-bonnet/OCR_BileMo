<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Phone;
use App\Entity\User;

class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security)
    {
        
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?string $operationName = null)
    {
        $this->addWhere($resourceClass, $queryBuilder);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?string $operationName = null,
        array $context = [])
    {
        $this->addWhere($resourceClass, $queryBuilder);
    }

    /**
     * Requests will be filtered according to current User's Client (hidding all other Clients data)
     * 
     * @param string $resourceClass
     * @param QueryBuilder $queryBuilder
     */
    private function addWhere(string $resourceClass, QueryBuilder $queryBuilder)
    {
        if (User::class !== $resourceClass || $this->security->isGranted('ROLE_ADMIN') || null === $user = $this->security->getUser()) {
            return;
        }
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere(sprintf('%s.client = :current_client', $rootAlias));
        $queryBuilder->setParameter('current_client', $user->getClient()->getId());
    }
}
