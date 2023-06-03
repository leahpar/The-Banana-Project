<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Minishlink\WebPush\VAPID;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PushController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index()
    {
        return $this->render('push/index.html.twig', [
        ]);
    }

    #[Route('/vapidPublicKey', name: 'vapidPublicKey')]
    public function vapidPublicKey(string $vapidPublicKey)
    {
        return new Response($vapidPublicKey);
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request, EntityManagerInterface $em)
    {
        $req = json_decode($request->getContent(), true);
        $subscription = $req['subscription'];
        $user = $em->getRepository(User::class)->findOneBy(['authToken' => $subscription['keys']['auth']]);
        if ($user === null) {
            $user = new User($subscription);
            $em->persist($user);
            $em->flush();
        }
        dump($req, $user);
        return new Response();
    }
}
