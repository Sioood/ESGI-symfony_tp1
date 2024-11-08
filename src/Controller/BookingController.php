<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use App\Enum\ReservationStatus;

class BookingController extends AbstractController
{
    #[Route('/booking', name: 'booking_index')]
    public function index(BookingRepository $repository): Response
    {
        $bookings = $repository->findAll();
        return $this->render('booking/index.html.twig', [
            'controller_name' => 'BookingController',
            'bookings' => $bookings,
            'reservationStatus' => ReservationStatus::cases()
        ]);
    }

    #[Route('/booking/{id<\d+>}', name: 'booking_show')]
    public function show(BookingRepository $repository, int $id): Response
    {
        $booking = $repository->find($id);
        if (!$booking) {
            throw $this->createNotFoundException('The booking does not exist');
        }
        return $this->render('booking/show.html.twig', [
            'controller_name' => 'BookingController',
            'booking' => $booking,
        ]);
    }

    private function isAvailable(EntityManagerInterface $entityManager, \DateTimeImmutable $startDatetime, \DateTimeImmutable $endDatetime, ?int $excludedBookingId = null): bool
    {
        $repository = $entityManager->getRepository(Booking::class);
        $qb = $repository->createQueryBuilder('b')
            ->where('b.start_datetime < :end_datetime AND b.end_datetime > :start_datetime')
            ->setParameter('start_datetime', $startDatetime)
            ->setParameter('end_datetime', $endDatetime);

        if ($excludedBookingId !== null) {
            $qb->andWhere('b.id != :excludedId')
                ->setParameter('excludedId', $excludedBookingId);
        }

        $bookings = $qb->getQuery()->getResult();

        return empty($bookings);
    }

    #[Route('/booking/new', name: 'booking_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $startDatetime = $booking->getStartDatetime();
            $endDatetime = $booking->getEndDatetime();

            if (!$this->isAvailable($entityManager, $startDatetime, $endDatetime)) {
                $this->addFlash('error', 'Le créneau horaire est déjà réservé.');
                return $this->redirectToRoute('booking_new');
            }

            $entityManager->persist($booking);
            $entityManager->flush();
            $this->addFlash('success', 'Réservation crée avec succès.');
            return $this->redirectToRoute('booking_index');
        }
        return $this->render('booking/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/booking/{id<\d+>}/edit', name: 'booking_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $booking = $entityManager->getRepository(Booking::class)->find($id);
        if (!$booking) {
            throw $this->createNotFoundException('The booking does not exist');
        }
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $id = $booking->getId();
            $startDatetime = $booking->getStartDatetime();
            $endDatetime = $booking->getEndDatetime();

            if (!$this->isAvailable($entityManager, $startDatetime, $endDatetime, $id)) {
                $this->addFlash('error', 'Le créneau horaire est déjà réservé.');
                return $this->redirectToRoute('booking_new');
            }

            $entityManager->flush();
            $this->addFlash('success', 'Réservation modifiée avec succès.');
            return $this->redirectToRoute('booking_index');
        }
        return $this->render('booking/edit.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/booking/{id<\d+>}/cancel', name: 'booking_cancel', methods: ['POST'])]
    public function cancel(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $booking = $entityManager->getRepository(Booking::class)->find($id);
        if (!$booking) {
            throw $this->createNotFoundException('The booking does not exist');
        }
        $booking->setStatus(ReservationStatus::ANNULE->value);
        $entityManager->flush();
        $this->addFlash('success', 'Réservation annulée avec succès.');
        return $this->redirectToRoute('booking_show', ['id' => $booking->getId()]);
    }

    #[Route('/booking/{id<\d+>}/confirm', name: 'booking_confirm', methods: ['POST'])]
    public function confirm(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $booking = $entityManager->getRepository(Booking::class)->find($id);
        if (!$booking) {
            throw $this->createNotFoundException('The booking does not exist');
        }
        $booking->setStatus(ReservationStatus::CONFIRME->value);
        $entityManager->flush();
        $this->addFlash('success', 'Réservation confirmée avec succès.');
        return $this->redirectToRoute('booking_show', ['id' => $booking->getId()]);
    }

    #[Route('/booking/{id<\d+>}/delete', name: 'booking_delete')]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $booking = $entityManager->getRepository(Booking::class)->find($id);
        if (!$booking) {
            throw $this->createNotFoundException('The booking does not exist');
        }
        $entityManager->remove($booking);
        $entityManager->flush();
        $this->addFlash('success', 'Réservation supprimée avec succès.');
        return $this->redirectToRoute('booking_index');
    }
}
