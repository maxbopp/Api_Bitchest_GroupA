<?php

namespace App\Entity;

use App\Repository\CryptoCotationsRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CryptoCotationsRepository::class)]
class CryptoCotations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('user:cotations')]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups('user:cotations')]
    private ?float $Cotation = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: "create")]
    #[Groups('user:cotations')]
    private ?\DateTimeImmutable $CreatedAt = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: "update")]
    #[Groups('user:cotations')]
    private ?\DateTimeImmutable $UpdatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'Cotations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('user:cotations')]
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
