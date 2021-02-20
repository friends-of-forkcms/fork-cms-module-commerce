<?php

namespace Backend\Modules\Catalog\Domain\ForkCMS;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class OnKernelResponse
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        // We need the header in the backend but we don't want to crash everything if its not there
        if (!$this->container->has('header') || $event->getRequest()->attributes->get('_route') !== 'backend') {
            return;
        }

        $content = $event->getResponse()->getContent();

        if (strpos($content, '<!DOCTYPE html>') !== 0) {
            // We only want to edit html responses
            return;
        }

        // Inject CSS for our custom icon in the Catalog module
        $event->getResponse()->setContent(
            preg_replace(
                '|<head>|',
                '<head>' . PHP_EOL
                . '<style media="screen" type="text/css">.nav-item-catalog a:before {content: "\f07a";}</style>',
                $content,
                1
            )
        );
    }
}
