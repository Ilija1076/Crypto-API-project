<?php

namespace App\Entity;

use App\Repository\CryptoCurrencyRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CryptoCurrencyRepository::class)
 */
class CryptoCurrency
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("crypto_currency")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("crypto_currency")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("crypto_currency")
     */
    private $symbol;

    /**
     * @ORM\Column(type="float")
     * @Groups("crypto_currency")
     */
    private $currentPrice;

    /**
     * @ORM\Column(type="float")
     * @Groups("crypto_currency")
     */
    private $totalVolume;

    /**
     * @ORM\Column(type="float")
     * @Groups("crypto_currency")
     */
    private $ath;


    /**
     * @ORM\Column(type="datetime")
     * @Groups("crypto_currency")
     */
    private $athDate;

    /**
     * @ORM\Column(type="float")
     * @Groups("crypto_currency")
     */
    private $atl;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("crypto_currency")
     */
    private $atlDate;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("crypto_currency")
     */
    private $updatedAt;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;
        return $this;
    }

    public function getCurrentPrice(): ?float
    {
        return $this->currentPrice;
    }

    public function setCurrentPrice(float $currentPrice): self
    {
        $this->currentPrice = $currentPrice;
        return $this;
    }

    public function getTotalVolume(): ?float
    {
        return $this->totalVolume;
    }

    public function setTotalVolume(float $totalVolume): self
    {
        $this->totalVolume = $totalVolume;
        return $this;
    }

    public function getAth(): ?float
    {
        return $this->ath;
    }

    public function setAth(float $ath): self
    {
        $this->ath = $ath;
        return $this;
    }

    public function getAthDate(): ?DateTimeInterface
    {
        return $this->athDate;
    }

    public function setAthDate(DateTimeInterface $athDate): self
    {
        $this->athDate = $athDate;
        return $this;
    }

    public function getAtl(): ?float
    {
        return $this->atl;
    }

    public function setAtl(float $atl): self
    {
        $this->atl = $atl;
        return $this;
    }

    public function getAtlDate(): ?DateTimeInterface
    {
        return $this->atlDate;
    }

    public function setAtlDate(DateTimeInterface $atlDate): self
    {
        $this->atlDate = $atlDate;
        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
