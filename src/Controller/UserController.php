<?php

namespace App\Controller;

use App\Entity\CryptoWallet;
use App\Entity\Transactions;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\CryptoCotationsRepository;
use App\Repository\CryptosRepository;
use App\Repository\CryptoWalletRepository;
use App\Repository\UsersRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class UserController extends AbstractController
{

    private $entityManager;
    private $User;
    private $PasswordHasher;
    private $jwtManager;
    private $serializer;
    private $Wallet;
    private $Cryptos;
    private $Cotations;
    private $CryptoWallet;

    public function __construct(EntityManagerInterface $entityManager, UsersRepository $User, JWTTokenManagerInterface $jwtManager, UserPasswordHasherInterface $PasswordHasher, SerializerInterface $serializer, WalletRepository $Wallet, CryptosRepository $Cryptos,CryptoCotationsRepository $Cotations,CryptoWalletRepository $CryptoWallet)
    {   
        // Fonctionalités et bundle utilisé dans le controller 
        $this->entityManager = $entityManager;
        $this->User = $User;
        $this->jwtManager = $jwtManager;
        $this->PasswordHasher = $PasswordHasher;
        $this->serializer = $serializer;
        $this->Wallet = $Wallet;
        $this->Cryptos = $Cryptos;
        $this->Cotations = $Cotations;
        $this->CryptoWallet = $CryptoWallet;
    }

    #[Route('api/register', name: 'app_users',methods: 'POST')]
    public function register(Request $request): Response
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
        $NewUser->setRoles(['ROLE_USER']);

        // infos envoyé à l'utilisateur
        $token = $this->jwtManager->create($NewUser);

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
            'message'=> 'Your account have been created !',
            'Token' => $token,
            'UserName' => $UserName,
        ]);
        
    }

    #[Route('api/user/dashboard', name: 'app_dashboard',methods: 'GET')]
    public function UserDashboard() : JsonResponse
    {
        // On récupére l'utilisateur connecté
        $UserConnected = $this->getUser();
        // On récupére le username de l'utilisateur connecté
        $UserName = $UserConnected->getUserIdentifier();
        // On récupérer la liste de tous les bitcoins et les cotations par la même occasion grâce à la relation entre les deux entités
        $ListCoins = $this->Cryptos->findAll();
        // On serialize poujr récupérer les données 
        $ListCoinsSerialized = $this->serializer->serialize($ListCoins,'json');
 
        return New JsonResponse([
            'status' => 'true',
            'message' => 'User Connected, here your information',
            'userConnected' => $UserName,
            'Cryptos' => json_decode($ListCoinsSerialized),
        ]);
    }

    /*
    #[Route('api/user/transactions', name: 'app_dashboard',methods: 'POST')]
    public function Transactions(Request $request) : JsonResponse
    {
        $Data = json_decode($request->getContent(), true);

        // On récupère les éléments nécessaire à la transaction
        $TransactionType = $Data['type'];
        $TrasactionsBitcoin = $Data['bitcoins'];
        $TrasactionsAmount = $Data['amount'];
        $TrasactionsCotation = $Data['cotation'];

        // On récupére l'utilisateur connécté pour pouvoir lui assigner cette transaction
        $UserConnected = $this->User->find($this->getUser()->getId());
        // On récupère son wallet
        $UserWallet = $UserConnected->getWallet();
        // On récpère les cryptos possédé par l'utilisateur
        $UserCryptos = $UserWallet->getCryptoWallet();

        // On récupère le coins pour pouvoir accéder à ces propriétés 
        $Coin = $this->Cryptos->findOneBy(['name' => $TrasactionsBitcoin]);

        // Derniére variation pour ce prix 
        $Cotations = $this->Cotations->findBy(['Cryptos' => $Coin]);
        // On recupère la derniére variations
        $LastCotation = end($Cotations);

        // On récupère la balance du user 
        $BalanceUser = $UserConnected->getWallet()->getBalance();

        if($TransactionType == 'purchase'){
            // on calcule l'evolution du prix
            $TodayPrice = $Coin->getPrice() - (($LastCotation->getCotation() * $Coin->getPrice()) / 100);
            // On calcule la somme nécessaire pour pouvoir effectuer la transactions
            $AmountPurchase = $TodayPrice *  $TrasactionsAmount;
            // On va comparer avec la balance pour savoir si il peut réaliser cette transactions
            if($AmountPurchase > $BalanceUser){
                return New JsonResponse([
                    'status' => 'false',
                    'message' => 'You can\'t purchase those coins, your balance is too low' 
                ]);
            }
            // On crée une transactions poiur garder des traces et l'avoir dans l'historique
            $NewTransaction = New Transactions;
            $NewTransaction->setType($TransactionType);
            $NewTransaction->setAmount($TrasactionsAmount);
            $NewTransaction->setCrypto($Coin->getName());
            $NewTransaction->setUnitPrice($TodayPrice);
            $NewTransaction->setTotal($AmountPurchase);

            // On ajoute cette transactions au user connecté
            $NewTransaction->setWallet($UserWallet);

            // On ajoute la crypto au wallet
            $NewCryptoWallet = New CryptoWallet;
            $NewCryptoWallet->setCrypto($Coin->getName());
            $NewCryptoWallet->setAmount($TrasactionsAmount);
            $UserWallet->addCryptoWallet($NewCryptoWallet);

            // On retire la somme payé au balance du user
            $UserWallet->setBalance($UserWallet->getBalance() - $AmountPurchase);

            // On persist en bdd et on flush 
            $this->entityManager->persist($NewTransaction);
            $this->entityManager->persist($UserWallet);
            $this->entityManager->flush();

            return New JsonResponse([
                'status' => 'true',
                'message' => 'You purchase is done !' 
            ]);
        }
        elseif($TransactionType == 'sale'){
            // on calcule l'evolution du prix
            $TodayPrice = $Coin->getPrice() - (($LastCotation->getCotation() * $Coin->getPrice()) / 100);
            // prix de la vente de la cryto
            $AmountSale = $TodayPrice *  $TrasactionsAmount;
            // On regarde  si l'utilisateur posséde la crypto pour la vendre 
            if(!$this->CryptoWallet->find($Coin->getName())){

                return New JsonResponse([
                    'status' => 'false',
                    'message' => 'You can\'t sell this coin, your don\'t posses this coin !' 
                ]);
            };

            // On crée une transactions poiur garder des traces et l'avoir dans l'historique
            $NewTransaction = New Transactions;
            $NewTransaction->setType($TransactionType);
            $NewTransaction->setAmount($TrasactionsAmount);
            $NewTransaction->setCrypto($Coin->getName());
            $NewTransaction->setUnitPrice($TodayPrice);
            $NewTransaction->setTotal($AmountSale);

            // On ajoute cette transactions au user connecté
            $NewTransaction->setWallet($UserWallet);

            // On retire la crypto au wallet
            $NewCryptoWallet = New CryptoWallet;
            $NewCryptoWallet->setCrypto($Coin->getName());
            $NewCryptoWallet->setAmount($TrasactionsAmount);
            $UserWallet->addCryptoWallet($NewCryptoWallet);
            
            // On ajoute la somme payé au balance du user
            $UserWallet->setBalance($UserWallet->getBalance() + $AmountSale);
        }

    }
    */

    #[Route('api/user', name: 'app_userInfos',methods: 'GET')]
    public function userInfos(): Response 
    {   
        // On récupère l'utilisateur connécté
        $UserConnected = $this->getUser();

        // On récupére les infos utilisateur via un group grâce au serializer
        $UserInfos = $this->serializer->serialize($UserConnected,'json',['groups' => 'user:read']);

        // On renvoie un json avec les infos utilisateur
        return New JsonResponse([
            'userInfos' => json_decode($UserInfos)
        ]);
    }

    #[Route('api/user/updateInformation', name: 'UserUpdateInformation', methods : 'POST')]
    public function UpdatePassword(Request $request): Response
    {
        $Data = json_decode($request->getContent(), true);      

        // on regarde si l'admin nous fournit c'est informations
        $UserNewEmail = $Data['email'] ?? null;
        $UserNewName = $Data['username'] ?? null;
        $UserNewPassword = $Data['password'] ?? null;

        // On récupère l'utilisateur connecté
        $UserConnected = $this->getUser();

        // On récupère l'utilisateur dans la base de donnée via son id 
        $UserToModify = $this->User->find($UserConnected->getId());

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
        
        // Username
        if($UserNewName){
            if($this->User->findOneBy(['username'=> $UserNewName])){
               return New JsonResponse([
                    'status' => false,
                    'message' => 'This username is already taken, choose a another one !'
               ],Response::HTTP_CONFLICT);
            }
            $UserToModify->SetUserName($UserNewName);
        }

        $this->entityManager->flush();    

        return New JsonResponse([
            'status' => true,
            'message' => 'Your information have been updated'
        ]);

    }

    #[Route('api/user/delete', name: 'UserDelete', methods : 'POST')]
    public function DeleteAccount() : JsonResponse
    {
        // On récupère l'utilisateur connécté
        $UserConnected = $this->getUser();
        // On trouve cette utilisateur dans la table user 
        $UserToDelete = $this->User->find($UserConnected->getId());
        // On le supprime
        $this->entityManager->remove($UserToDelete);
        $this->entityManager->flush();

        return New JsonResponse([
            'status' => true,
            'message' => 'This user have been deleted !'
        ]);

    }
    
}
