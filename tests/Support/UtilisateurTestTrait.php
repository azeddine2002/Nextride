<?php

namespace App\Tests\Support;

use App\Entity\Utilisateur;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait UtilisateurTestTrait
{
    private function creerUtilisateur(array $roles = ['ROLE_USER']): Utilisateur
    {
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $utilisateur = new Utilisateur();
        $utilisateur->setEmail(uniqid('test_', true).'@nextride.fr');
        $utilisateur->setNom('Test');
        $utilisateur->setPrenom('Utilisateur');
        $utilisateur->setRoles($roles);
        $utilisateur->setDateInscription(new \DateTimeImmutable());
        $utilisateur->setPassword($hasher->hashPassword($utilisateur, 'Test1234!'));

        $em->persist($utilisateur);
        $em->flush();

        return $utilisateur;
    }
}
