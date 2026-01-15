<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Título',
                'required' => true, //Hacer que los campos title, content e image sean obligatorios
                'attr' => ['class' => 'form-control', 'placeholder' => 'Escribe el título aquí..']
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenido',
                'required' => true,
                'attr' => ['class' => 'form-control', 'rows' => 5, 'placeholder' => 'Escribe tu historia']
            ])
            //5.3 RETO -> Crear un campo de tipo file para la imagen
            ->add('image', FileType::class, [
                'label' => 'Imagen de portada',
                'mapped' => false,
                'required' => true,
                'attr' => ['class' => 'form-control'],
                //Añadir las clases css a los campos del formulario (lo añadimos con 'attr' => ['class' => 'form-control'])
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Por favor sube una imagen válida (JPG, PNG, WEBP)'
                    ])
                ],
            ])
            ->add('Send', SubmitType::class, [
                'label' => 'Guardar Post',
                'attr' => ['class' => 'btn btn-primary mt-3'] //mt-3 es para dar margen arriba
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
