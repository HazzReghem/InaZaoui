<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    // #[Route('/guests', name: 'guests')]
    // public function guests(EntityManagerInterface $em): Response
    // {
    //     $guests = $em->getRepository(User::class)->findGuests();

    //     return $this->render('front/guests.html.twig', [
    //         'guests' => $guests,
    //     ]);
    // }
    #[Route('/guests', name: 'guests')]
    public function guests(Request $request, EntityManagerInterface $em): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $guests = $em->getRepository(User::class)->findGuests($limit, $offset);
        $total = $em->getRepository(User::class)->countGuests(); // Méthode à créer pour compter

        return $this->render('front/guests.html.twig', [
            'guests' => $guests,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    #[Route('/guest/{id}', name: 'guest')]
    public function guest(int $id, EntityManagerInterface $em): Response
    {
        $guest = $em->getRepository(User::class)->find($id);

        // dd($guest);

        if (!$guest) {
            throw $this->createNotFoundException('Guest not found.');
        }

        return $this->render('front/guest.html.twig', [
            'guest' => $guest,
        ]);
    }

    #[Route('/portfolio/{id}', name: 'portfolio')]
    public function portfolio(EntityManagerInterface $em, Request $request, ?int $id = null): Response
    {
        
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $albums = $em->getRepository(Album::class)->findAll();

        $album = $id ? $em->getRepository(Album::class)->find($id) : null;

        if ($album) {
            // Si un album est précisé, on récupère uniquement les médias liés à cet album
            $medias = $em->getRepository(Media::class)->findBy(['album' => $album, 'user' => $user]);
        } else {
            // Sinon, on affiche tous les médias de l'utilisateur
            $medias = $em->getRepository(Media::class)->findBy(['user' => $user]);
        }

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias,
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
}
