<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * L'encodeur de mots de passe
     *
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($c = 0; $c < 30; $c++) {
            $customer = new User();

            $hash = $this->encoder->encodePassword($customer, "password");

            $customer->setEmail($faker->email())
                     ->setPassword($hash)
                     ->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($customer);
            
            
        }
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
