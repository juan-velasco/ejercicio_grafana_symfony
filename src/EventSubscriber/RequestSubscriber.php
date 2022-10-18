<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event)
    {
        \Prometheus\Storage\Redis::setDefaultOptions(
            [
                'host' => 'redis',
                'port' => 6379,
                'password' => null,
                'timeout' => 0.1, // in seconds
                'read_timeout' => '10', // in seconds
                'persistent_connections' => false
            ]
        );

        $registry = \Prometheus\CollectorRegistry::getDefault();

        $uri = $event->getRequest()->getRequestUri();

        if (str_contains($uri, '/_wdt/')) {
            return;
        }

        if  ($event->getRequestType() === HttpKernelInterface::MAIN_REQUEST) {
            $counter = $registry->registerCounter('blog', 'http_requests_total', 'Http Requests', ['path']);
            $counter->incBy(1, [$uri]);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }
}
