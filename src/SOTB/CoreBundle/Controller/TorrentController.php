<?php

namespace SOTB\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use SOTB\CoreBundle\TorrentResponse;

class TorrentController extends Controller
{
    /**
     * @Template()
     */
    public function listAction(Request $request)
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $torrents = $dm->getRepository('SOTBCoreBundle:Torrent')->findAll();

        return array(
            'torrents' => $torrents
        );
    }

    /**
     * @Template()
     */
    public function uploadAction(Request $request)
    {
        $torrent = new \SOTB\CoreBundle\Document\Torrent();
        $torrent->setUploader($this->getUser());

        $form = $this->createForm(new \SOTB\CoreBundle\Form\Type\TorrentFormType(), $torrent);

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

                $uploader = new \SOTB\CoreBundle\TorrentUploader($this->container->getParameter('torrent_data_dir'));
                $uploader->upload($torrent);

                $dm->persist($torrent);
                $dm->flush();

                return $this->redirect($this->generateUrl('torrent_list'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    public function downloadAction($slug)
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $torrent = $dm->getRepository('SOTBCoreBundle:Torrent')->findOneBy(array('slug' => $slug));

        return new TorrentResponse($torrent, $this->container->getParameter('torrent_data_dir'));
    }

}
