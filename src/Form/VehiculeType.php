<?php

namespace App\Form;

use App\Entity\Vehicule;
use App\Enum\CategorieVehicule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class VehiculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque')
            ->add('modele')
            ->add('categorie', EnumType::class, [
                'class' => CategorieVehicule::class,
                'choice_label' => fn (CategorieVehicule $categorie) => match($categorie) {
                    CategorieVehicule::CITADINE => 'Citadine',
                    CategorieVehicule::SUV => 'SUV',
                    CategorieVehicule::LUXE_SPORTIVE => 'Luxe / Sportive',
                },
            ])
            ->add('prixJour')
            ->add('disponible')
            ->add('description')
            ->add('imageFile', FileType::class, [
                'label' => 'Photo du véhicule',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '4M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Veuillez déposer une image valide (jpg, png ou webp).',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}