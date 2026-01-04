<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Animator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AdalenFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Activités
        $activities = [
            [
                'name' => 'Piano & Formation musicale',
                'icon' => '🎹',
                'ageRange' => '3–4 ans',
                'price' => 56.99,
                'numberOfClasses' => 10,
                'duration' => '2 semaines',
                'description' => 'Initiation à la musique et au piano pour les tout-petits.',
                'image' => 'image1.jpg',
            ],
            [
                'name' => 'Jardinage',
                'icon' => '🌱',
                'ageRange' => '2–10 ans',
                'price' => 35.00,
                'numberOfClasses' => 4,
                'duration' => '1–2h',
                'description' => 'Nourrir la créativité, l\'autonomie et le lien à la nature à travers des expériences concrètes et joyeuses.',
                'image' => 'image2.jpg',
            ],
            [
                'name' => 'Théâtre',
                'icon' => '🎭',
                'ageRange' => '4–5 ans',
                'price' => 45.00,
                'numberOfClasses' => 20,
                'duration' => '4 semaines',
                'description' => 'Développer l\'expression orale et la confiance en soi.',
                'image' => 'image3.jpg',
            ],
            [
                'name' => 'Baby Yoga',
                'icon' => '🧘',
                'ageRange' => '0–6 ans',
                'price' => 30.00,
                'numberOfClasses' => 4,
                'duration' => '1–2h',
                'description' => 'Yoga adapté aux tout-petits pour le bien-être et la détente.',
                'image' => 'image4.jpg',
            ],
            [
                'name' => 'Arts plastiques',
                'icon' => '🎨',
                'ageRange' => '3–12 ans',
                'price' => 40.00,
                'numberOfClasses' => 8,
                'duration' => '1h30',
                'description' => 'Stimuler l\'imagination et l\'expression artistique des enfants.',
                'image' => 'image5.jpg',
            ],
            [
                'name' => 'English Bites',
                'icon' => '🍴',
                'ageRange' => '4–10 ans',
                'price' => 50.00,
                'numberOfClasses' => 12,
                'duration' => '1h',
                'description' => 'Apprendre l\'anglais de façon ludique et interactive.',
                'image' => 'IMG_20251216_130007.jpg',
            ],
            [
                'name' => 'Archéologie',
                'icon' => '🏺',
                'ageRange' => '6–12 ans',
                'price' => 55.00,
                'numberOfClasses' => 6,
                'duration' => '2h',
                'description' => 'Éveiller la curiosité et la découverte du passé.',
                'image' => 'IMG_20251216_142928.jpg',
            ],
            [
                'name' => 'Géologie',
                'icon' => '🪨',
                'ageRange' => '5–12 ans',
                'price' => 50.00,
                'numberOfClasses' => 6,
                'duration' => '2h',
                'description' => 'Explorer la terre et ses merveilles naturelles.',
                'image' => 'IMG_20251216_151936.jpg',
            ],
            [
                'name' => 'Sorties Culturelles',
                'icon' => '🏛',
                'ageRange' => '4–12 ans',
                'price' => 25.00,
                'numberOfClasses' => 4,
                'duration' => '3–4h',
                'description' => 'Découvrir le monde à travers des expériences enrichissantes.',
                'image' => 'IMG_20251216_151941.jpg',
            ],
        ];

        foreach ($activities as $data) {
            $activity = new Activity();
            $activity->setName($data['name']);
            $activity->setIcon($data['icon']);
            $activity->setAgeRange($data['ageRange']);
            $activity->setPrice($data['price']);
            $activity->setNumberOfClasses($data['numberOfClasses']);
            $activity->setDuration($data['duration']);
            $activity->setDescription($data['description']);
            $activity->setImage($data['image'] ?? null);
            $activity->setIsActive(true);
            $manager->persist($activity);
        }

        // Animateurs
        $animators = [
            [
                'name' => 'Sophie Martin',
                'title' => 'Infants',
                'category' => 'Infants',
                'description' => 'Spécialisée dans l\'accueil des tout-petits, Sophie apporte douceur et bienveillance à chaque activité.',
                'image' => 'IMG_20251118_134552.jpg',
            ],
            [
                'name' => 'Lucas Dubois',
                'title' => 'Toddler',
                'category' => 'Toddler',
                'description' => 'Passionné par l\'éveil des enfants, Lucas crée des activités ludiques et éducatives.',
                'image' => 'IMG_20251118_135305.jpg',
            ],
            [
                'name' => 'Emma Rousseau',
                'title' => 'Preschool',
                'category' => 'Preschool',
                'description' => 'Experte en pédagogie Montessori, Emma guide les enfants vers l\'autonomie.',
                'image' => 'IMG_20251118_135319.jpg',
            ],
            [
                'name' => 'Thomas Bernard',
                'title' => 'Animateur Nature',
                'category' => 'Preschool',
                'description' => 'Amoureux de la nature, Thomas partage sa passion pour le jardinage et l\'environnement.',
                'image' => 'IMG_20251202_144310.jpg',
            ],
        ];

        foreach ($animators as $data) {
            $animator = new Animator();
            $animator->setName($data['name']);
            $animator->setTitle($data['title']);
            $animator->setCategory($data['category']);
            $animator->setDescription($data['description']);
            $animator->setImage($data['image'] ?? null);
            $animator->setIsActive(true);
            $manager->persist($animator);
        }

        $manager->flush();
    }
}

