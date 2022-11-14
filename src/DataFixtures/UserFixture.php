<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture implements FixtureGroupInterface
{
    private $passwordHasher;
    /**
     * @link https://symfony.com/doc/current/security/passwords.html#hashing-the-password
     * 
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->passwordHasher = $hasher;
    }
    
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail("user@user.com");
        $plaintextPassword = "user";
        // hash the password (based on the security.yaml config for the $user class)
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);
        // $2y$13$g7Z1qnQUBr/3CbvxrusVLeoPtvSTXqAAVi7sU7bqFs2w1EAlKVUqq
        
        $user->setRoles(["ROLE_USER"]);
        $manager->persist($user);

        // ------------userAdmin-----------

        $newUserAdmin = new User();
        $plaintextPassword = "admin";
        $hashedPassword = $this->passwordHasher->hashPassword(
            $newUserAdmin,
            $plaintextPassword
        );
        $newUserAdmin->setEmail('admin@admin.com')
            ->setPassword($hashedPassword)
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($newUserAdmin);

        // ------------userManager-----------

        $newUserManager = new User();
        $plaintextPassword = "manager";
        $hashedPassword = $this->passwordHasher->hashPassword(
            $newUserManager,
            $plaintextPassword
        );
        $newUserManager->setEmail('manager@manager.com')
            ->setPassword($hashedPassword)
            ->setRoles(['ROLE_MANAGER']);
        $manager->persist($newUserManager);
        
        $manager->flush();

    }

    /**
     *
     * @return array
     */
    public static function getGroups(): array
    {
        return ['userGroup'];
    }
}
