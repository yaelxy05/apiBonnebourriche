<?php

namespace App\Controller\Api;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    /**
     * @Route("/api/reservations", name="api_reservation")
     */
    public function reservation(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        $reservations = $reservationRepository->findReservationForOneUser($user);

        return $this->json($reservations, 200, [], ['groups' => 'reservation_read']);
    }


    /**
     * @Route("/api/reservation/{id<\d+>}", name="api_reservation_read", methods="GET")
     */
    public function reservationRead(Reservation $reservation = null): Response
    {
        // 404 error page
        if ($reservation === null) {

            // Optional, message for the front
            $message = [
                'status' => Response::HTTP_NOT_FOUND,
                'error' => 'Désolé cette reservation n\'existe pas.',
            ];

            // We define a custom message and an HTTP 404 status code
            return $this->json($message, Response::HTTP_NOT_FOUND);
        }

        // The 4th argument represents the "context" which will be transmitted to the serializer
        return $this->json($reservation, 200, [], ['groups' => 'reservation_read']);
    }

    /**
     * @Route("/api/reservation/create", name="api_reservation_create", methods="POST")
     */
    public function reservationCreate(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        // Retrieve the content of the request, i.e. the JSON
        $jsonContent = $request->getContent();

        // We deserialize this JSON into a reservation entity, thanks to the Serializer
        // We transform the JSON into an object of type App\Entity\Reservation
        $reservation = $serializer->deserialize($jsonContent, Reservation::class, 'json');

        // If linked objects (Users) they will be validated if @Valid annotation
        // present on the $user property of the Reservation class
        $errors = $validator->validate($reservation);

        if (count($errors) > 0) {

            // The array of errors is returned as JSON with a status of 422
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $this->getUser();
        $reservation->setCreatedAt(new \DateTimeImmutable());
        $reservation->setUser($user);

        // We save the reservation
        $entityManager->persist($reservation);
        $entityManager->flush();

        // We redirect to reservation_read
        return $this->json($reservation, 200, [], ['groups' => 'reservation_read'], Response::HTTP_CREATED);

    }

    /**
     * @Route("/api/reservation/update/{id<\d+>}", name="api_reservation_update")
     */
    public function reservationUpdate(Reservation $reservation = null, EntityManagerInterface $em, SerializerInterface $serializer, Request $request, ValidatorInterface $validator): Response
    {
        // We want to modify the reservation whose id is transmitted via the URL

        // 404 ?
        if ($reservation === null) {
            // We return a JSON message + a 404 status
            return $this->json(['error' => 'La réservation n\'a pas été trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Our JSON which is in the body
        $jsonContent = $request->getContent();

        /* We will have to associate the JSON data received on the existing entity
        We deserialize the data received from the front ($ request-> getContent ()) ...
        ... in the reservation object to modify */
        $serializer->deserialize(
            $jsonContent,
            Reservation::class,
            'json',
            // We have this additional argument which tells the serializer which existing entity to modify
            [AbstractNormalizer::OBJECT_TO_POPULATE => $reservation]
        );

        // Validation of the deserialized entity
        $errors = $validator->validate($reservation);
        // Generating errors
        if (count($errors) > 0) {
            // We return the error table in Json to the front with a status code 422
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // On flush $reservation which has been modified by the Serializer
        $em->flush();

        // Condition the return message in case the entity is not modified
        return $this->json(['message' => 'La réservation a été modifié.'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/reservation/delete/{id<\d+>}", name="api_reservation_delete")
     */
    public function reservationDelete(Reservation $reservation = null, EntityManagerInterface $entityManager): Response
    {
        // 404
        if ($reservation === null) {

            $message = [
                'status' => Response::HTTP_NOT_FOUND,
                'error' => 'Réservation non trouvé.',
            ];

            // We define a custom message and an HTTP 404 status code
            return $this->json($message, Response::HTTP_NOT_FOUND);
        }

        // Otherwise we delete in base
        $entityManager->remove($reservation);
        $entityManager->flush();

        // The $task object still exists in PHP memory until the end of the script
        return $this->json(
            ['message' => 'La réservation ' . $reservation->getName() . ' a été supprimé !'],
            Response::HTTP_OK);
    }
}
