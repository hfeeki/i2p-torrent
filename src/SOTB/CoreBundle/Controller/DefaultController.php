<?php

namespace SOTB\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use PHPTracker_Config_Simple;
use PHPTracker_Core;

class DefaultController extends Controller
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Template()
     */
    public function memberAction(Request $request)
    {

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
