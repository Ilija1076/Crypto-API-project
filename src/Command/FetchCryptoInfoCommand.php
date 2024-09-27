<?php
namespace App\Command;

use App\Entity\CryptoCurrency;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchCryptoInfoCommand extends Command
{
    protected static $defaultName = 'app:fetch-crypto-data';

    // Entity Manager
    private $em;
    private $httpClient;

    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $httpClient)
    {
        $this->em = $entityManager;
        $this->httpClient = $httpClient;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription("Fetches cryptocurrency data from CoinGecko and saves it in the database.");
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=50&page=1&sparkline=false';
        try {
            // Using HTTP client to fetch data from the URL
            $response = $this->httpClient->request('GET', $url);
            $info = $response->toArray();
            // Clear existing records so that we don't duplicate cryptocurrencies
            $this->clearExistingRecords();
            // Going through each cryptocurrency and returning their values
            foreach ($info as $value) {
                $crypto = new CryptoCurrency();
                $crypto->setName($value['name']);
                $crypto->setSymbol($value['symbol']);
                $crypto->setCurrentPrice($value['current_price']);
                $crypto->setTotalVolume($value['total_volume']);
                $crypto->setAth($value['ath']);
                // Attempt to set ATH date field and catch exceptions
                try {
                    $crypto->setAthDate(new DateTime($value['ath_date']));
                } catch (Exception $e) {
                    $output->writeln('Invalid ATH date for ' . $value['name'] . ': ' . $e->getMessage());
                }
                $crypto->setAtl($value['atl']);
                // Attempt to set ATL date field and catch exceptions
                try {
                    $crypto->setAtlDate(new DateTime($value['atl_date']));
                } catch (Exception $e) {
                    $output->writeln('Invalid ATL date for ' . $value['name'] . ': ' . $e->getMessage());
                }
                // Attempt to set updated date field and catch exceptions
                try {
                    $crypto->setUpdatedAt(new DateTime($value['last_updated']));
                } catch (Exception $e) {
                    $output->writeln('Invalid updated date for ' . $value['name'] . ': ' . $e->getMessage());
                }
                // Persisting the entity after getting information
                $this->em->persist($crypto);
            }
            $this->em->flush();
            $output->writeln('Cryptocurrency fetched and saved');
            return Command::SUCCESS;
        } catch (TransportExceptionInterface $e) {
            $output->writeln('Error fetching data: ' . $e->getMessage());
            return Command::FAILURE;
        } catch (Exception $e) {
            $output->writeln('An unexpected error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    //function for clearing the CryptoCurrency table in the database
    private function clearExistingRecords(): void
    {
        $cryptoRepo = $this->em->getRepository(CryptoCurrency::class);
        $cryptos = $cryptoRepo->findAll();
        foreach ($cryptos as $crypto) {
            $this->em->remove($crypto);
        }
        $this->em->flush();
    }
}

