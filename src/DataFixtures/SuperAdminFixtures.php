<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SuperAdminFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(
        UserPasswordHasherInterface $hasher,
    ) {
        $this->hasher = $hasher;
        // Dans cet objet je mets dans la propriété hasher ce que contient la variable $hasher
    }

    public function load(ObjectManager $manager): void
    {
        $superAdmin = $this->createSuperAdmin();

        $manager->persist($superAdmin);
        $manager->flush();
    }

    /**
     * Permet de créer le Super Administrateur.
     */
    private function createSuperAdmin(): User
    {
        $superAdmin = new User();

        $superAdmin->setFirstName('Zhaki');
        $superAdmin->setLastName('El ouarroudi');
        $superAdmin->setEmail('miftah-arabi@gmail.com');
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER']);

        $passwordHashed = $this->hasher->hashPassword($superAdmin, 'azerty1234A*');
        $superAdmin->setPassword($passwordHashed);

        $superAdmin->setIsVerified(true);

        $superAdmin->setCreatedAt(new \DateTimeImmutable());
        $superAdmin->setUpdatedAt(new \DateTimeImmutable());
        $superAdmin->setVerifiedAt(new \DateTimeImmutable());

        return $superAdmin;
    }
}
