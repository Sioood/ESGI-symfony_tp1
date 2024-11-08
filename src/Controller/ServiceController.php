<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;


class ServiceController extends AbstractController
{
    #[Route('/service', name: 'service_index')]
    public function index(ServiceRepository $repository): Response
    {
        $services = $repository->findAll();
        return $this->render('service/index.html.twig', [
            'controller_name' => 'ServiceController',
            'services' => $services,
        ]);
    }

    #[Route('/service/{id<\d+>}', name: 'service_show')]
    public function show(ServiceRepository $repository, int $id): Response
    {
        $service = $repository->find($id);
        if (!$service) {
            throw $this->createNotFoundException('The service does not exist');
        }
        return $this->render('service/show.html.twig', [
            'controller_name' => 'ServiceController',
            'service' => $service,
        ]);
    }

    #[Route('/service/new', name: 'service_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($service);
            $entityManager->flush();
            $this->addFlash('success', 'Service crée avec succès.');
            return $this->redirectToRoute('service_index');
        }
        return $this->render('service/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/service/{id<\d+>}/edit', name: 'service_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $service = $entityManager->getRepository(Service::class)->find($id);
        if (!$service) {
            throw $this->createNotFoundException('The service does not exist');
        }
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Service modifié avec succès.');
            return $this->redirectToRoute('service_index');
        }
        return $this->render('service/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/service/{id<\d+>}/delete', name: 'service_delete')]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $service = $entityManager->getRepository(Service::class)->find($id);
        if (!$service) {
            throw $this->createNotFoundException('The service does not exist');
        }

        try {
            $entityManager->remove($service);
            $entityManager->flush();
            $this->addFlash('success', 'Service supprimé avec succès.');
        } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
            $this->addFlash('error', 'Impossible de supprimer le service car il est associé à des réservations existantes.');
            return $this->redirectToRoute('service_show', ['id' => $id]);
        }

        return $this->redirectToRoute('service_index');
    }
}
