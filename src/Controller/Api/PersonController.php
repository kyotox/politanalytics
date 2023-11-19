<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Person;

#[Route('/api', name: 'api_')]
class PersonController extends AbstractController
{
    #[Route('/persons', name: 'person_index', methods:['get'] )]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {
        $persons = $doctrine
            ->getRepository(Person::class)
            ->findAll();

        $data = [];

        foreach ($persons as $person) {
            $data[] = $person->getApiData();
        }

        return $this->json($data);
    }


    #[Route('/persons/{id}', name: 'person_show', methods:['get'] )]
    public function show(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $person = $doctrine->getRepository(Person::class)->find($id);

        if (!$person) {

            return $this->json('No person found for id ' . $id, 404);
        }

        return $this->json($person->getApiData());
    }

}