<?php

namespace App\Controller;

use App\Entity\Photo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MyController
 * @package App\Controller
 * @IsGranted("ROLE_USER")
 */
class MyController extends AbstractController
{
    /**
     * @Route("/my/photos", name="my_photos")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $myPhotos = $em->getRepository(Photo::class)->findBy(['user' => $this->getUser()]);

        return $this->render('my/index.html.twig', [
            'myPhotos' => $myPhotos
        ]);
    }

    /**
     * @Route("/my/photos/set_private/{id}", name="my_photos_set_as_private")
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function myPhotoSetAsPrivate(int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $myPhoto = $em->getRepository(Photo::class)->find($id);

        if ($this->getUser() == $myPhoto->getUser())
        {
            try {
                $myPhoto->setIsPublic(0);
                $em->persist($myPhoto);
                $em->flush();
                $this->addFlash('success', 'Ustawiono jako prywatne');
            } catch (\Exception $e){
                $this->addFlash('error', 'Występił problem przy ustawianiu jako prywatne');
            }
        } else {
            $this->addFlash('error', 'Nie jesteś właścicilem tego zdjęcia');
        }
        return $this->redirectToRoute('my_photos');

    }

    /**
     * @Route("/my/photos/set_public/{id}", name="my_photos_set_as_public")
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function myPhotoSetAsPublic(int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $myPhoto = $em->getRepository(Photo::class)->find($id);

        if ($this->getUser() == $myPhoto->getUser())
        {
            try {
                $myPhoto->setIsPublic(1);
                $em->persist($myPhoto);
                $em->flush();
                $this->addFlash('success', 'Ustawiono jako publiczne');
            } catch (\Exception $e){
                $this->addFlash('error', 'Występił problem przy ustawianiu jako publiczne');
            }
        } else {
            $this->addFlash('error', 'Nie jesteś właścicilem tego zdjęcia');
        }
        return $this->redirectToRoute('my_photos');

    }

    /**
     * @Route("/my/photos/remove/{id}", name="my_photos_remove")
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function myPhotoRemove(int $id)
    {
         $em = $this->getDoctrine()->getManager();
         $myPhoto = $em->getRepository(Photo::class)->find($id);

         if ($this->getUser() == $myPhoto->getUser())
         {
             $fileManager = new Filesystem();
             $fileManager->remove('images/hosting/'.$myPhoto->getFilename());
             if ($fileManager->exists('images/hosting/'.$myPhoto->getFilename()))
             {
                 $this->addFlash('error', 'Nie udało się usunąć zdjęcia');
             } else {
                 $em->remove($myPhoto);
                 $em->flush();
                 $this->addFlash('success', 'Usunięto zdjęcie');
             }
         }
         else {
             $this->addFlash('error', 'Aby usunąć zdjęcie musisz się zalogować');
         }

         return $this->redirectToRoute('my_photos');
    }
}