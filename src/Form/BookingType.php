<?php

namespace App\Form;

use App\Entity\Booking;
use App\Entity\Service;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Enum\ReservationStatus;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Disponible' => ReservationStatus::DISPONIBLE->value,
                    'Réservé' => ReservationStatus::RESERVE->value,
                    'Réservé (Confirmé)' => ReservationStatus::RESERVE->value,
                    'Annulé' => ReservationStatus::ANNULE->value,
                ],
                'required' => true,
            ])
            ->add('start_datetime', null, [
                'label' => 'Horaire de début',
                'widget' => 'single_text'
            ])
            ->add('end_datetime', null, [
                'label' => 'Horaire de fin',
                'widget' => 'single_text'
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'label' => 'Utilisateur',
                'choice_label' => function (User $user) {
                    return $user->getFirstname() . ' ' . $user->getLastname();
                },
                'choice_value' => 'id',
                'required' => false,
                'placeholder' => 'Aucun utilisateur',

            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'label' => 'Service',
                'choice_label' => function (Service $service) {
                    return $service->getName();
                },
                'choice_value' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}

