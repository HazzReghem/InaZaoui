<?php

namespace App\Controller\Guest;

use App\Entity\Media;
use App\Form\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/guest/media')]
class MediaController extends AbstractController
{
    #[Route('/', name: 'guest_media_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $medias = $em->getRepository(Media::class)->findBy(
            ['user' => $user],
            ['id' => 'ASC'],
            $limit,
            $offset
        );

        // Media de l'utilisateur
        $total = $em->getRepository(Media::class)->count(['user' => $user]);

        return $this->render('guest/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page
        ]);
    }


    #[Route('/add', name: 'guest_media_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $media->setUser($this->getUser());
            $media->setPath('uploads/' . md5(uniqid()) . '.' . $media->getFile()->guessExtension());
            $media->getFile()->move('uploads/', $media->getPath());

            $em->persist($media);
            $em->flush();

            return $this->redirectToRoute('guest_media_index');
        }

        return $this->render('guest/media/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'guest_media_delete')]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $media = $em->getRepository(Media::class)->find($id);

        if (!$media || $media->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if (file_exists($media->getPath())) {
            unlink($media->getPath());
        }

        $em->remove($media);
        $em->flush();

        return $this->redirectToRoute('guest_media_index');
    }
}
