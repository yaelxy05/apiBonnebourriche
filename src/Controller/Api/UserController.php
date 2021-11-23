<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/api/register1", name="api_register", methods="POST")
     */
    public function register(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, Request $request, ValidatorInterface $validator, SerializerInterface $serializer): Response
    {
        $jsonContent = $request->getContent();
        
        $user = $serializer->deserialize($jsonContent, User::class, 'json');

        $errors = $validator->validate($user);

        if (count($errors) > 0) {

            // The array of errors is returned as JSON with an error status 422
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $password = $user->getPassword();
        // This is where we encode the User password (found in $ user)
        $encodedPassword = $passwordEncoder->encodePassword($user, $password);
        // We reassign the password encoded in the User
        $user->setPassword($encodedPassword);
        $user->setRoles(['ROLE_USER']);

        // We save the user
        $entityManager->persist($user);
        $entityManager->flush();
          
        return $this->json([
                'user' => $user
            ], Response::HTTP_CREATED);
    }
}
