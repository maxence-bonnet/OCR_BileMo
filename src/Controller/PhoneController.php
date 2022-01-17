<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Phone;
use App\Entity\Client;
use App\Exception\NoPhoneListAvailableException;
use App\Repository\PhoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class PhoneController
{
    const ADD_TO_LIST = 'add_to_my_list';
    const REMOVE_FROM_LIST = 'remove_from_my_list';
    const GET_MY_LIST = 'get_my_list';

    public function __construct(
        private PhoneRepository $phoneRepository,
        private Security $security,
        private EntityManagerInterface $entityManager
        )
    {

    }

    /**
     * From Phone customs operations
     */
    public function __invoke($data, Request $request): mixed
    {
        if ($data instanceof Phone) {
            $client = $this->getUserClient();
            if (null === $client) {
                return null;
            }
            if (str_contains($request->getRequestUri(), self::ADD_TO_LIST)) {
                return $this->addToList($client, $data);
            } elseif (str_contains($request->getRequestUri(), self::REMOVE_FROM_LIST)) {
                return $this->removeFromList($client, $data);
            }
        } elseif (str_contains($request->getRequestUri(), self::GET_MY_LIST)) {
            return $this->getClientPhonesList();
        } 
        return $this->countPhones([], $request);
    }

    /**
     * Returns number of Phones according to given parameters
     * 
     * @param array $parameters
     * 
     * @return int
     */
    private function countPhones($parameters = [], Request $request): int
    {
        $condition = $request->get('brand');

        if ($condition !== null) {
            if ((int)$condition === 0) {
                return $this->phoneRepository->countByBrandName($condition);
            }
            $parameters = ['brand' => $condition];
        }
        return $this->phoneRepository->count($parameters);
    }

    /**
     * Returns Client Phones' list.
     * Client is determined from current User.
     *  
     * @return Phone[]
     */
    private function getClientPhonesList()
    {
        if (null === $this->security->getUser()->getClient()) {
            throw new NoPhoneListAvailableException("No list of Phones could be associated with the current User");
        }

        $client = $this->getUserClient();
        return $this->phoneRepository->findByClient((int)$client->getId());
    }

    /**
     * Determines Client from current User.
     * 
     * @return ?Client
     */    
    private function getUserClient(): ?Client
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['id' => $this->security->getUser()->getId()])
            ->getClient();
    }

    /**
     * Adds the given Phone to the Client phones' list.
     * Client is determined from current User.
     * 
     * @param Client $client
     * @param Phone $phone
     * 
     * @return Phone
     */
    private function addToList(Client $client, Phone $phone): Phone
    {
        $phone->addClient($client);
        return $this->persist($phone);
    }

    /**
     * Removes the given Phone to the Client phones' list.
     * Client is determined from current User.
     * 
     * @param Client $client
     * @param Phone $phone
     * 
     * @return Phone
     */
    private function removeFromList(Client $client, Phone $phone): Phone
    {
        $phone->removeClient($client);
        return $this->persist($phone);
    }

    /**
     * Persits changes.
     * 
     * @param Phone $phone
     * 
     * @return Phone
     */
    private function persist(Phone $phone): Phone
    {
        $this->entityManager->persist($phone);
        $this->entityManager->flush();
        return $phone;
    }
}
