<?php

namespace App\Services;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;

class TemplateService
{
    /**
     * @var EntityManager $em
     */
    protected $em;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var ContainerInterface $container
     */
    protected $container;

    public function __construct(ContainerInterface $container,LoggerInterface $logger)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
    }


}