<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/guests')]
class GuestController extends AbstractController
{
    #[Route('/', name: 'admin_guest_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $guests = $em->getRepository(User::class)->findAllGuests();

        return $this->render('admin/guest/index.html.twig', [
            'guests' => $guests,
        ]);
    }

    #[Route('/add', name: 'admin_guest_add')]
    public function add(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => false, // Indicate that this is not an edit form
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_USER']);
            $user->setIsBlocked(false);

            $user->setPassword($hasher->hashPassword($user, 'password'));

            // $hashedPassword = $hasher->hashPassword($user, $user->getPassword());
            // $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('admin_guest_index');
        }

        return $this->render('admin/guest/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/update/{id}', name: 'admin_guest_update')]
    public function update(int $id, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user || in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createNotFoundException('Guest not found or not editable.');
        }

        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => true, // Indicate that this is an edit form
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // If the password is not empty, hash it
            // Otherwise, keep the existing password
            if ($user->getPassword()) {
                $user->setPassword($hasher->hashPassword($user, $user->getPassword()));
            }

            $em->flush();

            return $this->redirectToRoute('admin_guest_index');
        }

        return $this->render('admin/guest/update.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }


    #[Route('/toggle-block/{id}', name: 'admin_guest_toggle_block')]
    public function toggleBlock(User $user, EntityManagerInterface $em): Response
    {
        $user->setIsBlocked(!$user->isBlocked());
        $em->flush();

        return $this->redirectToRoute('admin_guest_index');
    }

    #[Route('/delete/{id}', name: 'admin_guest_delete')]
    public function delete(User $user, EntityManagerInterface $em): Response
    {
        // Supprimer les médias de l'utilisateur
        foreach ($user->getMedias() as $media) {
            $em->remove($media);
            // Supprimer aussi physiquement les fichiers si besoin :
            $filePath = $media->getPath();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Supprimer ensuite l'utilisateur
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('admin_guest_index');
    }
}
