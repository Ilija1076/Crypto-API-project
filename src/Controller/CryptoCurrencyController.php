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
    /*since both are using the same url I had to make a function containing both min and max,
    get from the request the right one and return the values below or above them */

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



}
