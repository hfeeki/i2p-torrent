<?php

namespace SOTB\CoreBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class TrackerController implements ContainerAwareInterface
{
    private $container;

    function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function announceAction(Request $request)
    {
        $tracker = $this->container->get('tracker');

        $params = $request->query;

        if (!$params->has('ip')) {
            $params->set('ip', $request->getClientIp());
        }

        $response = $tracker->announce($params);

        return $response;
    }
}
