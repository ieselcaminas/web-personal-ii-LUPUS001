<?php

namespace App\Controller;

use App\Form\ContactFormType; //Clase del formulario
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; //Clase de la petición
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Contact;

class PageController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('page/index.html.twig', []);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('page/about.html.twig', []);
    }

    #[Route('/practice', name: 'practice')]
    public function practice(): Response
    {
        // En tu plantilla original este archivo se llamaba service.html
        return $this->render('page/service.html.twig', []);
    }

    #[Route('/attorneys', name: 'attorneys')]
    public function attorneys(): Response
    {
        // En tu plantilla original este archivo se llamaba team.html
        return $this->render('page/team.html.twig', []);
    }

    /* #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('page/contact.html.twig', []);
    } */

    #[Route('/contact', name: 'contact')]
    public function contact(ManagerRegistry $doctrine, Request $request): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contacto = $form->getData();    
            $entityManager = $doctrine->getManager();    
            $entityManager->persist($contacto);
            $entityManager->flush();

            // Para confirmarle al usuario que se ha enviado el formulario
            $this->addFlash('success', '¡Gracias por contactarnos! Tu mensaje ha sido enviado correctamente.');

            //Para redigirir al index cuando se envie el formulario
            return $this->redirectToRoute('index', []);
        }
        return $this->render('page/contact.html.twig', array(
            'form' => $form->createView()    
        ));
    }
}