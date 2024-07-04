<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\UsersRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use function PasswordGenerator\GeneratePassword;

class AdminController extends AbstractController
{
    private $entityManager;
    private $User;
    private $PasswordHasher;
    private $jwtManager;
    private $serializer;
    private $Wallet;

    public function __construct(EntityManagerInterface $entityManager, UsersRepository $Users,JWTTokenManagerInterface $jwtManager, UserPasswordHasherInterface $PasswordHasher, SerializerInterface $serializer, WalletRepository $Wallet)
    {   
        // Fonctionalités et bundle utilisé dans le controller 
        $this->entityManager = $entityManager;
        $this->User = $Users;
        $this->jwtManager = $jwtManager;
        $this->PasswordHasher = $PasswordHasher;
        $this->serializer = $serializer;
        $this->Wallet = $Wallet;
    }

    #[Route('api/hiden/CreateAdmin', name: 'AdminCreation', methods : 'POST')]
    public function CreateAdmin(Request $request): Response
    {
       // On décode les infos de l'utilisateur à ajouter qui à été envoyé en JSON
       $Data = json_decode($request->getContent(), true);

       // initialise un nouveau user 
       $NewUser = New User();

       $Email = $Data['email'];
       $UserName = $Data['username'];
       $Password = $Data['password'];
       // On hache le mot de passe pour qu'il apparraise pas en dur dans la base de donnée 
       $PasswordHashed = $this->PasswordHasher->hashPassword($NewUser, $Password);

       if($this->User->findOneBy(['username' => $UserName])){
           return New JsonResponse([
               'status' => false,
               'message' => 'This username is already taken.'
           ],Response::HTTP_CONFLICT);
       }

       $NewUser->setUserName($UserName);
       $NewUser->setEmail($Email);
       $NewUser->setPassword($PasswordHashed);
       $NewUser->setRoles(['ROLE_ADMIN']);
       
       // Il faut auusi initailiser le wallet de l'utilisateur 
       $UserWallet = New Wallet;
       // On ajoute les 500 
       $UserWallet->setBalance(500);
       // on ajoute un Wallet 
       $NewUser->setWallet($UserWallet);

       // le faire persister en bdd
       $this->entityManager->persist($NewUser);
       $this->entityManager->flush();

       // infos envoyé à l'utilisateur
       $token = $this->jwtManager->create($NewUser);
 

       // On lui renvoie un JSON
       return New JsonResponse([
           'status' => true,
           'message'=> 'Your account have been create !',
           'Token' => $token,
           'UserName' => $UserName,
       ]);
    }

    #[Route('api/AdminDashboard', name: 'Admin Dashboard', methods : 'GET')]
    // Seulement les utilisateurs  qui possédent le role admin peuvent accéder à cette route
    #[IsGranted('ROLE_ADMIN')]
    public function AdminDashboard(): Response
    {   
        // On récupère tous les utilisateurs
        $users = $this->User->findAll();
        // On serialise les utilsateurs 
        $usersInfos = $this->serializer->serialize($users,'json',['groups' => 'user:read']);
        // On retourne les urtilisateurs
        return New JsonResponse([
           "Utilisateurs" => json_decode($usersInfos)
        ]);
    }   

    #[Route('api/Admin/updateInformation', name: 'AdminUpdateInformation', methods : 'POST')]
    #[IsGranted('ROLE_ADMIN')]
    public function UpdatePassword(Request $request): Response
    {
        $Data = json_decode($request->getContent(), true);      

        // on regarde si l'admin nous fournit c'est informations
        $UserId = $Data['id'];
        $UserNewEmail = $Data['email'] ?? null;
        $UserNewName = $Data['username'] ?? null;
        $UserNewPassword = $Data['password'] ?? null;
        $UserNewRole = $Data['role'] ?? null;

        // On récupère l'id de l'utilisateur pour modifier son mdp
        $UserToModify = $this->User->find($UserId);

        // Dans le cas où on ne trouve pas l'utilisateur, on renvoie une erreur
        if(!$UserToModify){
            New JsonResponse([
                "Statut" => "False",
                "Message" => "This user doesn't exist !"
            ], Response::HTTP_NOT_FOUND);
        };
        
        // MDP
        if($UserNewPassword){
            // On hache le mdp pour le sécuriser 
            $UserPasswordHashed = $this->PasswordHasher->hashPassword($UserToModify, $UserNewPassword);
            // On set le mdp
            $UserToModify->setPassword($UserPasswordHashed);
            // On renvoie la réponse 
        }

        // Email
        if($UserNewEmail){
            $UserToModify->setEmail($UserNewEmail);
        }
        
        // Role
        if($UserNewRole){
            // Role valid pour un utilisateur
            $ValidRole = ["ROLE_ADMIN","ROLE_USER"];
            foreach ($UserNewRole as $Role) {
                // on check si les rôles fourni sont des rôles valides
                if(!in_array($Role,$ValidRole)){
                    // si ce n'est pas le cas alors on retourne une réponse
                    return New JsonResponse([
                        'status' => false,
                        'message' => 'The role is not valid !'
                    ]);
                }
            }
            // si tous les rôles fourni sont valide alors on modifie
            $UserToModify->SetRoles($UserNewRole); 
        }
        
        // Username
        if($UserNewName){
            if($this->User->findOneBy(['username'=> $UserNewName])){
               return New JsonResponse([
                    'status' => false,
                    'message' => 'This username is already taken, choose another one !'
               ],Response::HTTP_CONFLICT);
            }
            $UserToModify->SetUserName($UserNewName);
        }

        $this->entityManager->flush();

        return New JsonResponse([
            'status' => true,
            'message' => 'Your information have been updated !'
       ]);

    }

    #[Route('api/Admin/DeleteUser', name: 'AdminDeleteUser', methods : 'POST')]
    #[IsGranted('ROLE_ADMIN')]
    public function DeleteUser(Request $request): JsonResponse
    {
        $Data = json_decode($request->getContent(), true); 

        // Id de L'utilisateur à supprimer
        $UserIdToDelete = $Data['id'];

        // On regarde si l'utilisateur existe dans la bdd
        if(!$this->User->find($UserIdToDelete)){
            return New JsonResponse([
                'status' => 'false',
                'message' => 'This user doesn\'t exist !'
            ]);
        }else{
            $UserToDelete = $this->User->find($UserIdToDelete);
        }

        // on suprime l'utilisateur
        $this->entityManager->remove($UserToDelete);
        $this->entityManager->flush();    

        // On renvoie la réponse
        return New JsonResponse([
            'status' => 'true',
            'message' => 'The user have been deleted !' 
        ]);
    }

    #[Route('api/Admin/CreateUser', name: 'AdminCreateUser', methods : 'POST')]
    #[IsGranted('ROLE_ADMIN')]
    public function CreateUserFromAdmin(Request $request) : JsonResponse
    {
        $Data = json_decode($request->getContent(), true); 

        // initialise un nouveau user 
       $NewUser = New User();

       $UserName = $Data['username'];
       $Email = $Data['email'];
        // On va générer a mot passe pour l'utilisateur qu'on va crée
       $GeneratedPassword = GeneratePassword();
       // On hache le mot de passe pour qu'il apparraise pas en dur dans la base de donnée 
       $PasswordHashed = $this->PasswordHasher->hashPassword($NewUser, $GeneratedPassword);

       if($this->User->findOneBy(['username' => $UserName])){
           return New JsonResponse([
               'status' => false,
               'message' => 'This username is already taken.'
           ],Response::HTTP_CONFLICT);
       }

       $NewUser->setUserName($UserName);
       $NewUser->setEmail($Email);
       $NewUser->setPassword($PasswordHashed);
       $NewUser->setRoles(['ROLE_USER']);
       
       // Il faut auusi initailiser le wallet de l'utilisateur 
       $UserWallet = New Wallet;
       // On ajoute les 500 
       $UserWallet->setBalance(500);
       // on ajoute un Wallet 
       $NewUser->setWallet($UserWallet);

       // le faire persister en bdd
       $this->entityManager->persist($NewUser);
       $this->entityManager->flush();

       // On lui renvoie un JSON
       return New JsonResponse([
            'status' => true,
            'message'=> 'Your account have been create !',
            'password' => $GeneratedPassword
       ]);
    }

}
