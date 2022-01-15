<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JWTAuthenticationSubscriber implements EventSubscriberInterface
{
    public function onLexikJwtAuthenticationOnJwtCreated($event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        if ($user instanceof User) {
            $data['id'] = $user->getId();
            if ($user->getClient()) {
                $data['client']['id'] = $user->getClient()->getId();
                $data['client']['name'] = $user->getClient()->getName();
                $data['client']['createdAt'] = $user->getClient()->getCreatedAt();                
            }
        }
        $event->setData($data);
    }

    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_jwt_created' => 'onLexikJwtAuthenticationOnJwtCreated',
        ];
    }
}
