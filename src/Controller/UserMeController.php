<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Security;

class UserMeController
{
    public function __construct(private Security $security) {}

    public function __invoke()
    {
        return $this->security->getUser();  
    }
}
