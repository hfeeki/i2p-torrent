<?php

namespace SOTB\UserBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('fos_user.user_manager');

        // admin user
        $user = $userManager->createUser();
        $user->setEnabled(true);
        $user->setEmail('admin@example.com');
        $user->setFirstName('Admin');
        $user->setLastName('User');
        $user->setUsername('admin');
        $user->setPlainPassword('pass');

        // make him an admin
        $user->addRole('ROLE_ADMIN');

        $userManager->updateUser($user);
    }
}