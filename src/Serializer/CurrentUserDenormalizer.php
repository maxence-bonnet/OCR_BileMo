<?php

namespace App\Serializer;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

/**
 * This custom denormalizer automatically assigns a created User to the Client of the User (authenticated) who created it.
 * Administrator by-passes this rule since he is not related to a Client
 */
class CurrentUserDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    
    use DenormalizerAwareTrait;

    public function __construct(
        private Security $security,
        private ClientRepository $clientRepository,
        private UserPasswordHasherInterface $hasher)
    {
        
    }

    private const ALREADY_CALLED_DENORMALIZER = 'CurrentUserDenormalizerCalled'; // Avoid denormalizer infinite loop 

    public function denormalize($data, string $type, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED_DENORMALIZER] = true;

        /** @var User $user */
        $user = $this->denormalizer->denormalize($data, $type, $format, $context);

        $rawPassword = $user->getPassword();
        $user->setPassword($this->hasher->hashPassword($user, $rawPassword));

        /**
         * if the Client object is taken directly from JWT payload, 
         * a new client will be added to database, no matter its given id
         */

        /** @var Client $payloadClient */
        $payloadClient = $this->security->getUser()->getClient();

        /** @var Client $client */
        $client =  $this->clientRepository->findOneBy(['id' => $payloadClient?->getId()]);

        if (!$this->security->isGranted('ROLE_ADMIN') && null !== $client) {
            $user->setClient($client);
        }
        return $user;
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = [])
    {
        $alreadyCalled = $context[self::ALREADY_CALLED_DENORMALIZER] ?? false;
        return (User::class === $type && false === $alreadyCalled);
    }
}
