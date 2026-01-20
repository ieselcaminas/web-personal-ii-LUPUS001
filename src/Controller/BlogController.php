<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

//Necesarios para el RETO 5.3
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class BlogController extends AbstractController
{
    #[Route('/blog/{page}', name: 'app_blog', requirements: ['page' => '\d+'], defaults: ['page' => 1])]
    public function index(ManagerRegistry $doctrine, int $page = 1): Response
    {
        $repository = $doctrine->getRepository(Post::class);

        // Obtenemos todos los posts ordenados por fecha
        $posts = $repository->findAll();

        return $this->render('blog/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/single_post/{slug}', name: 'single_post')]
    public function post(ManagerRegistry $doctrine, $slug): Response
    {
        $repositorio = $doctrine->getRepository(Post::class);
        $post = $repositorio->findOneBy(["slug"=>$slug]);
        return $this->render('blog/single_post.html.twig', [
            'post' => $post,
        ]);
    }
    
    #[Route('/blog/new', name: 'new_post')]
    #[IsGranted('ROLE_USER')] //Para que solo los usuarios logueados puedan entrar, con esto cumplimos "Comprobar si el usuario ha iniciado sesión" pero nos falta reenviarlo al login
    public function newPost(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();   
            
            // LÓGICA PARA LA SUBIDA DE IMAGEN (necesario para el RETO 5.3)
            $file = $form->get('image')->getData(); // Recuperamos el archivo del formulario (campo 'image')

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // Limpiamos el nombre del archivo
                $safeFilename = $slugger->slug($originalFilename);
                // Creamos un nombre único
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    // Movemos el archivo al directorio configurado en services.yaml
                    $file->move(
                        $this->getParameter('blog_directory'),
                        $newFilename
                    );
                } catch (FileException $e){
                    // Si falla la subida, lanzamos el siguiente error
                    throw new \Exception('Ha habido un problema al subir la imagen');
                }
                // Guardamos el nombre del archivo en la entidad Post
                $post->setImage($newFilename);
            }
            
            /* LÓGICA DE CÓDIGO ANTERIOR AL RETO 5.3 */
            // Quitamos los caracteres especiales del título para crear el slug
            $post->setSlug($slugger->slug($post->getTitle()));
            
            // Guardamos el usuario que crea el post y los contadores a 0
            $post->setPostUser($this->getUser());
            $post->setNumLikes(0);
            $post->setNumComments(0);

            $entityManager = $doctrine->getManager();    
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', '¡Entrada guardada correctamente!');
            
            return $this->redirectToRoute('single_post', ["slug" => $post->getSlug()]);
        }

        return $this->render('blog/new_post.html.twig', array(
            'form' => $form->createView()    
        ));
    }

}
