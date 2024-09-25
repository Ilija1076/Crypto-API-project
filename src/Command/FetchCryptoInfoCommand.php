<?php

namespace App\Command;

use App\Entity\CryptoCurrency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchCryptoInfoCommand extends Command
{
    protected static $defaultName = 'app:fetch-crypto-data';


    //entity manager
    private $em;
    private $httpClient;

    public  function __construct(EntityManagerInterface $entityManager, HttpClientInterface $httpClient)
    {
        $this->em = $entityManager;
        $this->httpClient = $httpClient;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription("Fetching cryptocurrency data from CoinGecko and saves it in the database");

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=50&page=1&sparkline=false';
        //using http client to fetch data from url
        $response = $this->httpClient->request('GET', $url);
        $info = $response->toArray();
        //going through each cryptocurrency and returning their values
        foreach($info as $value){
            $crypto = new CryptoCurrency();
            $crypto->setName($value['name']);
            $crypto->setSymbol($value['symbol']);
            $crypto->setCurrentPrice($value['current_price']);
            $crypto->setTotalVolume($value['total_volume']);
            $crypto->setAth($value['ath']);
            $crypto->setAthDate(new \DateTime($value['ath_date']));
            $crypto->setAtl($value['atl']);
            $crypto->setAtlDate(new \DateTime($value['atl_date']));
            $crypto->setUpdatedAt(new \DateTime($value['last_updated']));
        //persisting the entity after getting information
             $this->em->persist($crypto);
        }
        $this->em->flush();
        $output->writeln('Cryptocurrency fetched and saved');
        return Command::SUCCESS;
    }
}
