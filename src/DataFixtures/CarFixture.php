<?php

namespace App\DataFixtures;

use App\Entity\Car;
use App\Entity\Garage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CarFixture extends Fixture implements DependentFixtureInterface
{
    public const car = 'car-reference';

    public function load(ObjectManager $manager)
    {
        /** @var Garage $garage */
        $garage = $this->getReference(GarageFixture::GARAGE);
        /** @var Garage $garage2 */
        $garage2 = $this->getReference(GarageFixture::GARAGE.'2');


        /** @var \App\Entity\Car $car */
        $car = new Car('Bugatti Veyron', 'bugatti_veyron.jpg', 100000, 20);

        $this->setReference(self::car, $car);
        $garage->addCar($car);
        $manager->persist($car);

        /** @var \App\Entity\Car $car */
        $car = new Car('Bugatti Veyron', 'bugatti_veyron.jpg', 100000, 40);

        $this->setReference(self::car.'2', $car);
        $garage2->addCar($car);
        $manager->persist($car);

        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            GarageFixture::class,
        ];
    }
}
