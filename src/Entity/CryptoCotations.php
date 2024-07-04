<?php

namespace App\Entity;

use App\Repository\CryptoCotationsRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: CryptoCotationsRepository::class)]
class CryptoCotations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $Cotation = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: "create")]
    private ?\DateTimeImmutable $CreatedAt = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: "update")]
    private ?\DateTimeImmutable $UpdatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'Cotations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cryptos $cryptos = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCotation(): ?float
    {
        return $this->Cotation;
    }

    public function setCotation(float $Cotation): static
    {
        $this->Cotation = $Cotation;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $CreatedAt): static
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $UpdatedAt): static
    {
        $this->UpdatedAt = $UpdatedAt;

        return $this;
    }

    public function getCryptos(): ?Cryptos
    {
        return $this->cryptos;
    }

    public function setCryptos(?Cryptos $cryptos): static
    {
        $this->cryptos = $cryptos;

        return $this;
    }
}
