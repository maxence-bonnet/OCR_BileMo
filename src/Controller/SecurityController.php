<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/api/authenticate', name: 'api_authenticate', methods: ['POST'])]
    public function login()
    {
        // Handled by Lexik JWT
    }
}
