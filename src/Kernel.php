<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}

/* 
Al hacer un git clone, instalar:
composer require symfony/maker-bundle --dev
composer require symfony/twig-bundle
composer require symfony/asset
composer require symfony/orm-pack
composer require symfony/profiler-pack
composer require form security validator

*/