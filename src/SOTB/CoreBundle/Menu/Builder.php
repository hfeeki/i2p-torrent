<?php

namespace SOTB\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($this->container->get('request')->getRequestUri());

        $menu->setChildrenAttribute('class', 'nav');

        $menu->addChild('Home', array('route' => 'homepage'));

        $menu->addChild('Torrents', array('route' => 'torrent_list'));

        $menu->addChild('Upload', array('route' => 'torrent_upload'));

        if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $menuItem2 = $menu->addChild('Members', array('uri' => '#'))
                ->setAttribute('class', 'dropdown')
                ->setLinkattribute('data-toggle', 'dropdown')
                ->setLinkattribute('class', 'dropdown-toggle')
                ->setChildrenAttribute('class', 'dropdown-menu');

            $menuItem2->addChild('Member stuff', array(
                'route'           => 'members',
                'routeParameters' => array()
            ));
        } else {
            $menuItem2 = $menu->addChild('Account', array('uri' => '#'))
                ->setAttribute('class', 'dropdown')
                ->setLinkattribute('data-toggle', 'dropdown')
                ->setLinkattribute('class', 'dropdown-toggle')
                ->setChildrenAttribute('class', 'dropdown-menu');

            $menuItem2->addChild('Login', array(
                'route'           => 'fos_user_security_login',
                'routeParameters' => array()
            ));
            $menuItem2->addChild('Register', array(
                'route'           => 'fos_user_registration_register',
                'routeParameters' => array()
            ));
        }

        return $menu;
    }
}