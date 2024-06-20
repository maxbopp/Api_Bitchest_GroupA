<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{

    private $entityManager;
    private $Users;
    private $PasswordHasher;
    private $jwtManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, UsersRepository $Users,JWTTokenManagerInterface $jwtManager, UserPasswordHasherInterface $PasswordHasher, SerializerInterface $serializer)
    {   
        // Fonctionalités et bundle utilisé dans le controller 
        $this->entityManager = $entityManager;
        $this->Users = $Users;
        $this->jwtManager = $jwtManager;
        $this->PasswordHasher = $PasswordHasher;
        $this->serializer = $serializer;
    }

    #[Route('api/register', name: 'app_users',methods: 'POST')]
    public function register(Request $request): Response
    {   
        // On décode les infos de l'utilisateur à ajouter qui à été envoyé en JSON
        $Data = json_decode($request->getContent(), true);

        // initialise un nouveau user 
        $User = New Users();

        $Email = $Data['email'];
        $UserName = $Data['username'];
        $Password = $Data['password'];
        // On hache le mot de passe pour qu'il apparraise pas en dur dans la base de donnée 
        $PasswordHashed = $this->PasswordHasher->hashPassword($User, $Password);

        if($this->Users->findOneBy(['username' => $UserName])){
            return New JsonResponse([
                'status' => false,
                'message' => 'Ce nom d\'utilisateur est déjà pris.'
            ],Response::HTTP_CONFLICT);
        }

        $User->setUserName($UserName);
        $User->setEmail($Email);
        $User->setPassword($PasswordHashed);
        $User->setRoles(['ROLE_USER']);
        // Ajouter les 500 euros à l'utilisateur quand le wallet sera crée

        // le faire persister en bdd
        $this->entityManager->persist($User);
        $this->entityManager->flush();
        
        // infos envoyé à l'utilisateur
        $token = $this->jwtManager->create($User);
        //Elias

        // On lui renvoie un JSON
        return New JsonResponse([
            'status' => true,
            'message'=> 'Votre compte à bien été crée!',
            'Token' => $token,
            'UserName' => $UserName,
        ]);
        
    }

    #[Route('api/user', name: 'app_userInfos',methods: 'GET')]
    public function userInfos(): Response 
    {   
        // On récupère l'utilisateur connécté
        $user = $this->getUser();

        // On récupére les infos utilisateur via un group grâce au serializer
        $userInfos = $this->serializer->serialize($user,'json',['groups' => 'user:read']);

        // On renvoie un json avec les infos utilisateur
        return New JsonResponse([
            'userInfos' => json_decode($userInfos)
        ]);
    }

}


