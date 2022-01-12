<?php

namespace App\Controller;

use App\Repository\PhoneRepository;
use Symfony\Component\HttpFoundation\Request;

class PhoneCountController
{
    public function __construct(private PhoneRepository $phoneRepository) {}

    public function __invoke(Request $request): int
    {
        $condition = $request->get('brand');
        $parameters = [];

        if ($condition !== null) {
            if ((int)$condition === 0) {
                return $this->phoneRepository->countByBrandName($condition);
            }
            $parameters = ['brand' => $condition];
        }
        return $this->phoneRepository->count($parameters);
    }
}
