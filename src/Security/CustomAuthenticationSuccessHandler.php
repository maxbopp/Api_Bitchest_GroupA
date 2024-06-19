<?php
namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class CustomAuthenticationSuccessHandler
{
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
      $data = $event->getData();
      $user = $event->getUser();

      if (!$user instanceof UserInterface) {
          return;
      }
      // $data['id'] = $user->getId();
      // $data['username'] = $user->getUsername();
      $data["user"] = $user;
      $event->setData($data);
    }
}