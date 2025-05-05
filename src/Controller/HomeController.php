<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    #[Route('/guests', name: 'guests')]
    public function guests(EntityManagerInterface $em): Response
    {
        $guests = $em->getRepository(User::class)->findGuests();

        return $this->render('front/guests.html.twig', [
            'guests' => $guests,
        ]);
    }

    #[Route('/guest/{id}', name: 'guest')]
    public function guest(int $id, EntityManagerInterface $em): Response
    {
        $guest = $em->getRepository(User::class)->find($id);

        if (!$guest) {
            throw $this->createNotFoundException('Guest not found.');
        }

        return $this->render('front/guest.html.twig', [
            'guest' => $guest,
        ]);
    }

    #[Route('/portfolio/{id}', name: 'portfolio')]
    public function portfolio(EntityManagerInterface $em, ?int $id = null): Response
    {
        $albums = $em->getRepository(Album::class)->findAll();
        $album = $id ? $em->getRepository(Album::class)->find($id) : null;

        // Récupère le premier admin trouvé
        $admins = $em->getRepository(User::class)->findAdmins();
        
        if (empty($admins)) {
            $user = new User();
            $user->setEmail('admin@default.com');
            $user->setRoles(['ROLE_ADMIN']);
        }
        
        // $user = $admins[0];

        $medias = $album
            ? $em->getRepository(Media::class)->findBy(['album' => $album])
            : $em->getRepository(Media::class)->findBy(['user' => $user]);

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
