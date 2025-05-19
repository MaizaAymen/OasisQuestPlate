<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $samples = [
            ['name' => 'Nefta Premium Dates', 'description' => 'Finest quality dates from Nefta.', 'price' => 15.00, 'image' => 'images/dates1.jpg'],
            ['name' => 'Nefta Deluxe Dates', 'description' => 'Delicious and sweet deluxe dates.', 'price' => 20.00, 'image' => 'images/dates2.jpg'],
            ['name' => 'Nefta Organic Dates', 'description' => 'Organic dates grown in Nefta oasis.', 'price' => 18.50, 'image' => 'images/dates3.jpg'],
        ];

        foreach ($samples as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setDescription($data['description']);
            $product->setPrice($data['price']);
            $product->setImage($data['image']);
            $manager->persist($product);
        }

        $manager->flush();
    }
}