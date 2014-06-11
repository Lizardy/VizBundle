<?php

namespace AltSTU\VizBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use AltSTU\VizBundle\Services\VizService;
use AltSTU\VizBundle\Services\VizPdfService;


class AltSTUVizBundle extends Bundle
{
	private static $containerInstance = null; 

	/*public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new FormPass());
    }*/
	
   public function setContainer(ContainerInterface $container = null) 
   { 
    parent::setContainer($container); 
    self::$containerInstance = $container; 
   }

   public static function getContainer() 
   { 
    return self::$containerInstance; 
   }
}
