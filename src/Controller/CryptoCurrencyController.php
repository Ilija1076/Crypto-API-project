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

        // Serialize the data to JSON
        $data = $this->serializer->serialize($crypto, 'json', ['groups' => ['crypto_currency']]);

        return JsonResponse::fromJsonString($data, 200);
    }

    /**
     * @Route("/api/crypto-currency", name="crypto_currency_by_min_price", methods={"GET"})
     */
    public function getByMinPrice(Request $request): JsonResponse
    {
        // Get the min parameter from the query string
        $minPrice = $request->query->get('min', 0);

        // Find cryptocurrencies with price greater than minPrice
        $cryptos = $this->em->getRepository(CryptoCurrency::class)->createQueryBuilder('c')
            ->where('c.currentPrice > :minPrice')
            ->setParameter('minPrice', $minPrice)
            ->getQuery()
            ->getResult();

        // Serialize the data to JSON
        $data = $this->serializer->serialize($cryptos, 'json', ['groups' => ['crypto_currency']]);

        return JsonResponse::fromJsonString($data, 200);
    }


    /**
     * @Route("/api/crypto-currency", name="crypto_currency_by_max_price", methods={"GET"})
     */
    public function getByMaxPrice(Request $request): JsonResponse
    {
        // Get the max parameter from the query string
        $maxPrice = $request->query->get('max', PHP_INT_MAX);

        // Find cryptocurrencies with price less than maxPrice
        $cryptos = $this->em->getRepository(CryptoCurrency::class)->createQueryBuilder('c')
            ->where('c.currentPrice < :maxPrice')
            ->setParameter('maxPrice', $maxPrice)
            ->getQuery()
            ->getResult();

        // Serialize the data to JSON
        $data = $this->serializer->serialize($cryptos, 'json', ['groups' => ['crypto_currency']]);

        return JsonResponse::fromJsonString($data, 200);
    }

}
