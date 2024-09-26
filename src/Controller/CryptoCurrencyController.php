<?php

namespace App\Controller;

use App\Entity\CryptoCurrency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CryptoCurrencyController extends AbstractController
{
    private $em;
    private $serializer;


    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->em = $entityManager;
        $this->serializer = $serializer;
    }

    /*since both are using the same url I had to make a function containing both min and max,
    get from the request the right one or both and return the values below or above them */

    /**
     * @Route("/api/crypto-currency", name="crypto_currency_by_price", methods={"GET"})
     */
    public function getByPrice(Request $request): JsonResponse
    {
        // Get the min and max parameters from the query string
        $minPrice = $request->query->get('min');
        $maxPrice = $request->query->get('max');

        // Base query builder
        $queryBuilder = $this->em->getRepository(CryptoCurrency::class)->createQueryBuilder('c');

        // If both min and max are provided, use both conditions
        if ($minPrice !== null && $maxPrice !== null) {
            $queryBuilder->where('c.currentPrice > :minPrice')
                ->andWhere('c.currentPrice < :maxPrice')
                ->setParameter('minPrice', $minPrice)
                ->setParameter('maxPrice', $maxPrice);
        }
        // If only min is provided
        elseif ($minPrice !== null) {
            $queryBuilder->where('c.currentPrice > :minPrice')
                ->setParameter('minPrice', $minPrice);
        }
        // If only max is provided
        elseif ($maxPrice !== null) {
            $queryBuilder->where('c.currentPrice < :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        // Execute the query and get results
        $cryptos = $queryBuilder->getQuery()->getResult();

        // Serialize the data to JSON
        $data = $this->serializer->serialize($cryptos, 'json', ['groups' => ['crypto_currency']]);

        return JsonResponse::fromJsonString($data, 200);
    }

    //API for getting the current top 10 cryptocurrencies by current_price
    /**
     * @Route("/api/crypto-currency/top-10-current", name="crypto_currency_top_10_current_price", methods={"GET"})
     */
    public function getTop10ByCurrentPrice(): JsonResponse
    {

        $cryptos = $this->em->getRepository(CryptoCurrency::class)->createQueryBuilder('c')
            ->orderBy('c.currentPrice', 'DESC') // Sorting by highest price
            ->setMaxResults(10) // Limiting to top 10
            ->getQuery()
            ->getResult();


        $data = $this->serializer->serialize($cryptos, 'json', ['groups' => ['crypto_currency']]);

        return JsonResponse::fromJsonString($data, 200);
    }

    //API for getting the top 10 all time high cryptocurrencies
    /**
     * @Route("/api/crypto-currency/top-10-ath", name="crypto_currency_top_10_ath", methods={"GET"})
     */
    public function getTop10ByATH(): JsonResponse
    {
        // Fetch top 10 cryptocurrencies sorted by all-time high (ath)
        $cryptos = $this->em->getRepository(CryptoCurrency::class)->createQueryBuilder('c')
            ->orderBy('c.ath', 'DESC') // Sorting by highest all-time high price
            ->setMaxResults(10) // Limiting to top 10
            ->getQuery()
            ->getResult();


        $data = $this->serializer->serialize($cryptos, 'json', ['groups' => ['crypto_currency']]);

        return JsonResponse::fromJsonString($data, 200);
    }
    //API for comparing two cryptocurrencies by their symbols
    /**
     * @Route("/api/crypto-currency/compare", name="crypto_currency_compare", methods={"GET"})
     */
    public function compareCryptocurrencies(Request $request): JsonResponse
    {
        // Get symbols from the query parameters
        $symbol1 = $request->query->get('symbol1');
        $symbol2 = $request->query->get('symbol2');

        $crypto1 = $this->em->getRepository(CryptoCurrency::class)->findOneBy(['symbol' => $symbol1]);
        $crypto2 = $this->em->getRepository(CryptoCurrency::class)->findOneBy(['symbol' => $symbol2]);

        if (!$crypto1 || !$crypto2) {
            return new JsonResponse(['error' => 'One or both cryptocurrencies not found'], 404);
        }

        $data = [
            'currency1' => $this->serializer->serialize($crypto1, 'json', ['groups' => ['crypto_currency']]),
            'currency2' => $this->serializer->serialize($crypto2, 'json', ['groups' => ['crypto_currency']]),
        ];

        return JsonResponse::fromJsonString(json_encode($data), 200);
    }

    //API for getting the cryptocurrency by symbol
    /**
     * @Route("/api/crypto-currency/{symbol}", name="crypto_currency_by_symbol", methods={"GET"})
     */
    public function getBySymbol(string $symbol): JsonResponse
    {
        $crypto = $this->em->getRepository(CryptoCurrency::class)->findOneBy(['symbol' => $symbol]);
        // If the symbol doesn't exist return error
        if (!$crypto) {
            return new JsonResponse(['error' => 'Cryptocurrency not found'], 404);
        }

        $data = $this->serializer->serialize($crypto, 'json', ['groups' => ['crypto_currency']]);

        return JsonResponse::fromJsonString($data, 200);
    }

}
