<?php

namespace App\Controller;

use App\Entity\CryptoCurrency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

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
     * @throws ExceptionInterface
     */
    public function getByPrice(Request $request): Response
    {
        $minPrice = $request->query->get('min');
        $maxPrice = $request->query->get('max');

        $queryBuilder = $this->em->getRepository(CryptoCurrency::class)->createQueryBuilder('c');

        if ($minPrice !== null && $minPrice !== '') {
            $queryBuilder->andWhere('c.currentPrice >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice !== null && $maxPrice !== '') {
            $queryBuilder->andWhere('c.currentPrice <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        $cryptos = $queryBuilder->getQuery()->getResult();

        $data = $this->serializer->normalize($cryptos, null, [
            'groups' => ['crypto_currency'],
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'
        ]);

        if ($request->headers->get('Content-Type') === 'application/json') {
            return new JsonResponse(['data' => $data, 'status' => 'success'], Response::HTTP_OK);
        }

        return $this->render('crypto_currency/index.html.twig', [
            'cryptos' => $cryptos,
        ]);
    }

    //API showing top 10 cryptocurrencies by current price

    /**
     * @Route("/api/crypto-currency/top-10-current", name="crypto_currency_top_10_current_price", methods={"GET"})
     * @throws ExceptionInterface
     */
    public function getTop10ByCurrentPrice(Request $request): Response
    {
        $cryptos = $this->em->getRepository(CryptoCurrency::class)->createQueryBuilder('c')
            ->orderBy('c.currentPrice', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        if ($request->headers->get('Content-Type') === 'application/json') {
            $data = $this->serializer->normalize($cryptos, null, [
                'groups' => ['crypto_currency'],
                DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'
            ]);
            return new JsonResponse($data);
        }

        return $this->render('crypto_currency/top10current.html.twig', [
            'cryptos' => $cryptos,
        ]);
    }


    //API showing top 10 all-time high cryptocurrencies

    /**
     * @Route("/api/crypto-currency/top-10-ath", name="crypto_currency_top_10_ath", methods={"GET"})
     * @throws ExceptionInterface
     */
    public function getTop10ByATH(Request $request): Response
    {
        $cryptos = $this->em->getRepository(CryptoCurrency::class)->createQueryBuilder('c')
            ->orderBy('c.ath', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        if ($request->headers->get('Content-Type') === 'application/json') {
            $data = $this->serializer->normalize($cryptos, null, [
                'groups' => ['crypto_currency'],
                DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'
            ]);
            return new JsonResponse($data);
        }

        return $this->render('crypto_currency/top10ath.html.twig', [
            'cryptos' => $cryptos,
        ]);
    }

    //API for comparing two cryptocurrencies by their symbols

    /**
     * @Route("/api/crypto-currency/compare", name="crypto_currency_compare", methods={"GET"})
     * @throws ExceptionInterface
     */
    public function compareCryptocurrencies(Request $request): Response
    {
        $symbol1 = $request->query->get('symbol1');
        $symbol2 = $request->query->get('symbol2');

        $crypto1 = $this->em->getRepository(CryptoCurrency::class)->findOneBy(['symbol' => $symbol1]);
        $crypto2 = $this->em->getRepository(CryptoCurrency::class)->findOneBy(['symbol' => $symbol2]);

        if (!$crypto1 || !$crypto2) {
            return new JsonResponse(['error' => 'One or both cryptocurrencies not found'], 404);
        }

        $data = [
            'currency1' => $this->serializer->normalize($crypto1, null, [
                'groups' => ['crypto_currency'],
                DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'
            ]),
            'currency2' => $this->serializer->normalize($crypto2, null, [
                'groups' => ['crypto_currency'],
                DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'
            ]),
        ];

        if ($request->headers->get('Content-Type') === 'application/json') {
            return new JsonResponse($data);
        }

        return $this->render('crypto_currency/compare.html.twig', [
            'currency1' => $crypto1,
            'currency2' => $crypto2,
        ]);
    }

    //API for getting the cryptocurrency by symbol
    /**
     * @Route("/api/crypto-currency/{symbol}", name="crypto_currency_by_symbol", methods={"GET"})
     * @throws ExceptionInterface
     */
    public function getBySymbol(string $symbol, Request $request): Response
    {

        $crypto = $this->em->getRepository(CryptoCurrency::class)->findOneBy(['symbol' => $symbol]);

        if (!$crypto) {
            return new JsonResponse(['error' => 'Cryptocurrency not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->normalize($crypto, null, [
            'groups' => ['crypto_currency'],
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'
        ]);

        if ($request->headers->get('Content-Type') === 'application/json') {
            return new JsonResponse($data);
        }

        return $this->render('crypto_currency/symbol.html.twig', [
            'crypto' => $crypto,
        ]);
    }

}
