<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\User;
use App\Entity\Phone;
use App\Entity\Brand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const DEFAULT_FIXTURES_PASS = 'azerty';

    private const PHONES_COUNT = 150;

    private const PHONE_MODEL_MAX_LENGTH = 4;

    private const PHONE_MAX_PRICE = 600;

    private const PHONE_MIN_PRICE = 16;

    private const PHONE_MAX_WEIGHT = 1000;

    private const PHONE_MIN_WEIGHT = 100;

    public function __construct(private UserPasswordHasherInterface $passwordHasher, private array $models = [])
    {
        $this->generateModelsNames();
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadClient($manager);
        $this->loadUser($manager);
        $this->loadBrand($manager);
        $this->loadPhone($manager);
    }

    private function loadClient(ObjectManager $manager): void
    {
        foreach ($this->getClientData() as [$ref, $name]) {
            $client = new Client();
            $client->setName($name);
            $client->setCreatedAt(new \DateTimeImmutable('now - '.$ref.'days'));

            $this->addReference('cli-'.$ref, $client);
            $manager->persist($client);
        }
        $manager->flush();
    }

    private function getClientData(): array
    {
        return [
            ['1', 'Phone Shop'],
            ['2', 'Cheap & Smart Phone'],
            ['3', 'Get My Phone'],
            ['4', 'Phone 4 U'],
            ['5', 'QualityPhone'],
        ];
    }

    private function loadUser(ObjectManager $manager): void
    {
        $admin = (new User())
            ->setEmail('admin@bilemo.com')
            ->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, self::DEFAULT_FIXTURES_PASS));

        $manager->persist($admin);

        foreach ($this->getUserData() as [$ref, $client, $email]) {
            $user = (new User())
                ->setEmail($email)
                ->setClient($this->getReference('cli-'.$client));

            $user->setPassword($admin->getPassword()); // same password for performance reasons
            
            $manager->persist($user);
            $this->addReference('usr-'.$ref, $user);
        }
        $manager->flush();
    }

    private function getUserData(): array
    {
        return [
            ['1', '1', 'beverly@phoneshop.com', []],
            ['2', '1','david@phoneshop.com', []],
            ['3', '1', 'paul@phoneshop.com', []],
            ['4', '2', 'james@cheapandsmartphone.com', []],
            ['5', '2','kathy@cheapandsmartphone.com', []],
            ['6', '2', 'ruth@cheapandsmartphone.com', []],
            ['7', '3', 'ashley@getmyphone.com', []],
            ['8', '3','doris@getmyphone.com', []],
            ['9', '3', 'jhonny@getmyphone.com', []],
            ['10', '4', 'russell@phoneforyou.com', []],
            ['11', '4','gerald@phoneforyou.com', []],
            ['12', '4', 'bonnie@phoneforyou.com', []],
            ['13', '5', 'emily@qualityphone.com', []],
            ['14', '5','randy@qualityphone.com', []],
            ['15', '5', 'andrew@qualityphone.com', []],
        ];
    }

    private function loadBrand(ObjectManager $manager): void
    {
        foreach ($this->getBrandData() as [$ref, $name]) {
            $brand = new Brand();
            $brand->setName($name);
            $brand->setCreatedAt(new \DateTimeImmutable('now - '.$ref.'0days')); // now - $var*10 days

            $this->addReference('bra-'.$ref, $brand);
            $manager->persist($brand);
        }
        $manager->flush();
    }

    private function getBrandData(): array
    {
        return [
            ['1', 'COT'],
            ['2', 'Alcatal'],
            ['3', 'Facam'],
            ['4', 'Gougle'],
            ['5', 'Huaouay'],
            ['6', 'Wiplo'],
            ['7', 'Mapple'],
            ['8', 'Levono'],
            ['9', 'Mobistal'],
            ['10', 'GL'],
            ['11', 'Motorolo'],
            ['12', 'Siemince'],
            ['13', 'Axer'],
            ['14', 'Azus'],
            ['15', 'AOD'],
            ['16', 'WhiteBerry'],
            ['17', 'Cubut'],
            ['18', 'Syno'],
            ['19', 'Jifutsu'],
            ['20', 'Hyoundaye'],
        ];
    }

    private function getRandomBrand(): Brand
    {
        return $this->getReference('bra-'.rand(1, count($this->getBrandData())));
    }

    private function loadPhone(ObjectManager $manager): void
    {
        for ($i = 0; $i <= self::PHONES_COUNT; $i++) {
            $phone = $this->getRandomPhone($i);
            
            $manager->persist($phone);
        }
        $manager->flush();
    }

    private function getRandomPhone(int $loopIndex): Phone
    {
        $brand = $this->getRandomBrand();
        $phone = (new Phone())
            ->setModel($this->getRandomModel($loopIndex))
            ->setPrice(rand(self::PHONE_MIN_PRICE, self::PHONE_MAX_PRICE))
            ->setWeight(rand(self::PHONE_MIN_WEIGHT, self::PHONE_MAX_WEIGHT))
            ->setBrand($brand)
            ->setReleasedAt($brand->getCreatedAt())
            ->setCreatedAt($brand->getCreatedAt());

        return $phone;
    }

    private function getRandomModel(int $loopIndex): string
    {
        return $this->getModels()[$loopIndex];
    }

    private function generateModelsNames()
    {
        $models = [];
        while (count($models) <= self::PHONES_COUNT) {
            for ($i = 0; $i <= self::PHONES_COUNT; $i++) {
                $models[] = $this->getRandomWord();
            }
            $models = array_unique($models);
            sort($models); // reset keys
            shuffle($models);
        }
        return $this->setModels($models);
    }

    private function getRandomWord(): string
    {
        $word = array_merge(range(0, 9), range('A', 'Z'));
        shuffle($word);
        return substr(implode($word), 0, rand(1, self::PHONE_MODEL_MAX_LENGTH));
    }

    private function getModels()
    {
        return $this->models;
    }

    private function setModels(array $models)
    {
        $this->models = $models;
    }
}
