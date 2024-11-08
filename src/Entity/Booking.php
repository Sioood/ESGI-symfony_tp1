<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use App\Entity\Service;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    // En lien avec ReservationStatus enum
    #[Assert\Choice(choices: ['Disponible', 'Réservé', 'Réservé (Confirmé)', 'Annulé'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $start_datetime = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[AppValidator\BookingAvalaible]
    private ?\DateTimeImmutable $end_datetime = null;

    #[Assert\IsTrue(message: 'L\'heure de fin doit être après l\'heure de début')]
    public function isEndDatetimeAfterStartDatetime(): bool
    {
        return $this->start_datetime && $this->end_datetime && $this->start_datetime < $this->end_datetime;
    }

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    private ?Service $service = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStartDatetime(): ?\DateTimeImmutable
    {
        return $this->start_datetime;
    }

    public function setStartDatetime(\DateTimeImmutable $start_datetime): static
    {
        $this->start_datetime = $start_datetime;

        return $this;
    }

    public function getEndDatetime(): ?\DateTimeImmutable
    {
        return $this->end_datetime;
    }

    public function setEndDatetime(\DateTimeImmutable $end_datetime): static
    {
        $this->end_datetime = $end_datetime;

        return $this;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;

        return $this;
    }
}
