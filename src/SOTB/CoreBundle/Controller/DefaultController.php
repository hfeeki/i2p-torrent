<?php

namespace SOTB\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @Template()
     */
    public function myUploadsAction()
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $query = $dm->getRepository('SOTBCoreBundle:Torrent')->getForUser($this->getUser());

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            10/*limit per page*/
        );

        return compact('pagination');
    }
}
