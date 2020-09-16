<?php
/**
 * Created by PhpStorm.
 * User: hicham benkachoud
 * Date: 06/01/2020
 * Time: 20:39
 */

namespace App\Controller;


use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthController extends ApiController
{
    /**
     * @Route("/register", name="register", methods="POST")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->transformJsonBody($request);
        $username = $request->get('username');
        $password = $request->get('password');
        $email = $request->get('email');

        if (empty($username) || empty($password) || empty($email)) {
            return $this->respondValidationError("Invalid Username or Password or Email");
        }


        $user = new User($username);
        $user->setPassword($encoder->encodePassword($user, $password));
        $user->setEmail($email);
        $user->setUsername($username);
        $em->persist($user);
        $em->flush();
        return $this->respondWithSuccess(sprintf('User %s successfully created', $user->getUsername()));
    }

    /**
     * @Route("/api/login_check", name="api_login_check", methods="POST")
     * @param Request $request
     * @param JWTTokenManagerInterface $JWTManager
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function getTokenUser(Request $request, JWTTokenManagerInterface $JWTManager, UserPasswordEncoderInterface $encoder, UserRepository $userRepository)
    {
        $status = 200;
        $user = $userRepository->findOneBy([
            'username' => $request->get('username'),
        ]);
        if (!$user) {
            $response = [
                'message' => 'User not found!'
            ];
            $status = Response::HTTP_UNAUTHORIZED;
        } else if ($user && $encoder->isPasswordValid($user, $request->get('password'))) {
            $response = ['token' => $JWTManager->create($user)];
        } else {
            $response = [
                'message' => 'Invalid Password!'
            ];
            $status = Response::HTTP_UNAUTHORIZED;
        }
        return new JsonResponse($response, $status);
    }

}
