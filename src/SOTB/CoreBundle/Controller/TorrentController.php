<?php

namespace SOTB\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use SOTB\CoreBundle\TorrentResponse;
use SOTB\CoreBundle\Document\Search;

class TorrentController extends Controller
{
    /**
     * @Template()
     */
    public function listAction(Request $request)
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $query = $dm->getRepository('SOTBCoreBundle:Torrent')->getAll();

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            30/*limit per page*/
        );

        return compact('pagination');
    }

    /**
     * @Template()
     */
    public function showAction(Request $request, $slug)
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $torrent = $dm->getRepository('SOTBCoreBundle:Torrent')->findOneBy(array('slug' => $slug));

        if (null === $torrent) {
            throw $this->createNotFoundException();
        }

        $categories = $dm->getRepository('SOTBCoreBundle:Category')->findAll();

        $thread = $this->container->get('fos_comment.manager.thread')->findThreadById($torrent->getId());
        if (null === $thread) {
            $thread = $this->container->get('fos_comment.manager.thread')->createThread();
            $thread->setId($torrent->getId());
            $thread->setPermalink($request->getUri());

            // Add the thread
            $this->container->get('fos_comment.manager.thread')->saveThread($thread);
        }

        $comments = $this->container->get('fos_comment.manager.comment')->findCommentTreeByThread($thread);

        return array(
            'comments' => $comments,
            'thread' => $thread,
            'torrent' => $torrent,
            'categories' => $categories
        );
    }

    /**
     * @Template()
     */
    public function categoryAction($slug)
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $category = $dm->getRepository('SOTBCoreBundle:Category')->findOneBy(array('slug' => $slug));

        if (null === $category) {
            throw $this->createNotFoundException();
        }

        $query = $dm->getRepository('SOTBCoreBundle:Torrent')->getInCategory($category);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            30/*limit per page*/
        );

        return array('category' => $category, 'pagination' => $pagination);
    }

    /**
     * @Template()
     */
    public function requestAction(Request $request)
    {
        $torrent = new \SOTB\CoreBundle\Document\Torrent();
        $torrent->setUploader($this->getUser());

        $form = $this->createForm(new \SOTB\CoreBundle\Form\Type\TorrentFormType(), $torrent);

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

                $torrentManager = $this->container->get('torrent_manager');
                $torrentManager->process($torrent);

                $torrentManager->upload($torrent);

                $dm->persist($torrent);
                $dm->flush();

                $this->container->get('session')->setFlash('success', 'This torrent has been requested.');

                return $this->redirect($this->generateUrl('torrent', array('slug' => $torrent->getSlug())));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    public function voteAction($slug)
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $torrent = $dm->getRepository('SOTBCoreBundle:Torrent')->findOneBy(array('slug' => $slug));

        if (null === $torrent) {
            throw $this->createNotFoundException();
        }

        $torrentRequest = new \SOTB\CoreBundle\Document\Request();
        $torrentRequest->setUser($this->getUser());

        $torrent->addRequest($torrentRequest);

        $dm->persist($torrent);
        $dm->flush();

        $this->container->get('session')->setFlash('success', 'You have added your request for this torrent.');

        return $this->redirect($this->generateUrl('torrent', array('slug' => $torrent->getSlug())));
    }

    /**
     * @Template()
     */
    public function searchAction(Request $request)
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $query = $dm
            ->getRepository('SOTBCoreBundle:Torrent')
            ->getSearch($request->query->getAlnum('q'));

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            30/*limit per page*/
        );

        $search = new Search();
        $search->setQuery($request->get('q'));
        $search->setNumResults($pagination->getTotalItemCount());
        $search->setUser($this->getUser());

        $dm->persist($search);
        $dm->flush();

        // if only one result, send them to it
        if (1 === $pagination->getTotalItemCount()) {
            return $this->redirect($this->generateUrl('torrent', array('slug' => $pagination->current()->getSlug())));
        }

        return compact('pagination');
    }

    public function downloadAction($slug)
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $torrent = $dm->getRepository('SOTBCoreBundle:Torrent')->findOneBy(array('slug' => $slug));

        if (null === $torrent) {
            throw $this->createNotFoundException();
        }

        $torrentManager = $this->container->get('torrent_manager');

        return new TorrentResponse($torrentManager->getFileData($torrent), $torrent->getSlug());
    }

}
