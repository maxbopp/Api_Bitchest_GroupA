<?php

namespace App\Command;

use App\Entity\CryptoCotations;
use App\Entity\Cryptos;
use App\Repository\CryptosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function GenerateCryptos\GeneratedPriceAndVariationForCryptoOn30days;

#[AsCommand(
    name: 'Bitcoins',
    description: 'Create and add prices and cotations for cryotcurrencies to the database.',
)]
class BitcoinsCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {   
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {   
        // On génére le prix et les variation pour toutes les currencies
        $CrytposGenerated = GeneratedPriceAndVariationForCryptoOn30days();

        foreach ($CrytposGenerated as $Coin) {
            // On instancies une class crytpos
            $NewCryptos = New Cryptos;
            // On set la nouvelle instance avec les bonnes informations
            $NewCryptos->setName($Coin->name); 
            $NewCryptos->setPrice($Coin->price);
            // On initie les variations 
            foreach ($Coin->Variations as $Cotation) {
                $NewCotations = New CryptoCotations;
                $NewCotations->setCotation($Cotation);
                // On set la variations dans la cryptos
                $NewCryptos->addCotation($NewCotations);
            };

            // On le fait persister la crypto 
            $this->entityManager->persist($NewCryptos);
            // et on la flush
            $this->entityManager->flush();
        }

        $output->writeln('The price and the cotations have been created and insert into the database.');
        return Command::SUCCESS;
    }
}
