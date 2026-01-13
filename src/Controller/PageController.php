<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Contact;
use App\Form\ContactFormType; //Clase del formulario
use App\Repository\ContactRepository; 
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; //Clase de la petición
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType; // Para añadir botones extra

class PageController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        // 1. LÓGICA NUEVA (Punto 4.5): Obtener Categorías para la galería
        $categoryRepository = $doctrine->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        // 2. LÓGICA ANTIGUA -> RETO II: Lista de contactos en la portada
        // Solo enviamos la lista si hay alguien logueado.
        $contacts = [];
        $contactRepository = $doctrine->getRepository(Contact::class);
        
        // $this->getUser() devuelve el usuario actual o null si no está logueado
        if ($this->getUser()) {
            $contacts = $contactRepository->findAll();
        }

        // 3. Renderizar pasando ambas variables
        return $this->render('page/index.html.twig', [
            'categories' => $categories, //Para la galería
            'contacts' => $contacts //Para la lista de administración
        ]);
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

            // Cambiar la redirección a 'thankyou'
            return $this->redirectToRoute('thankyou');
        }
        return $this->render('page/contact.html.twig', array(
            'form' => $form->createView()    
        ));
    }

    #[Route('/thank-you', name: 'thankyou')]
    public function thankyou(): Response 
    {
        return $this->render('page/thankyou.html.twig', []);
    }

    /* PARA COMPLETAR LA PARTE DEL RETO II */
    #[Route('/contact/edit/{id}', name: 'contact_edit')]
    public function edit(Contact $contact, Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. CONTROL DE ACCESO: Si no está logueado, fuera.
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // 2. Crear el formulario reutilizando ContactFormType
        $form = $this->createForm(ContactFormType::class, $contact);

        // 3. GESTIÓN DE VARIOS BOTONES (Parte clave del Reto)
        // Modificamos el formulario "al vuelo" para añadir botones de Guardar y Borrar
        // Quitamos el botón 'Send' original si es necesario o añadimos nuevos.
        
        $form->remove('Send'); // Quitamos el botón de enviar original
        
        $form->add('save', SubmitType::class, ['label' => 'Guardar Cambios', 'attr' => ['class' => 'btn btn-primary']])
             ->add('delete', SubmitType::class, ['label' => 'Borrar Mensaje', 'attr' => ['class' => 'btn btn-danger']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // LÓGICA PARA SABER QUÉ BOTÓN SE PULSÓ
            // El método isClicked() nos dice cuál disparó el envío
            
            if ($form->get('delete')->isClicked()) {
                // Lógica de borrar
                $entityManager->remove($contact);
                $entityManager->flush();
                $this->addFlash('success', 'El contacto ha sido eliminado.');
            } else {
                // Lógica de guardar (por defecto si pulsó save)
                $entityManager->flush();
                $this->addFlash('success', 'El contacto ha sido actualizado.');
            }

            return $this->redirectToRoute('index');
        }

        return $this->render('page/edit_contact.html.twig', [
            'form' => $form->createView(),
            'contact' => $contact
        ]);
    }
}