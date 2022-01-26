<?php

namespace App\Controller;

use App\Repository\PhoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class PhoneController
{
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
}
