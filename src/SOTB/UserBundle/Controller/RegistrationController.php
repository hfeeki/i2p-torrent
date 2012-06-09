<?php

namespace SOTB\UserBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\RegistrationController as BaseController;

class RegistrationController extends BaseController
{
    public function registerAction()
    {
        $form = $this->container->get('fos_user.registration.form');
        $formHandler = $this->container->get('fos_user.registration.form.handler');

        $process = $formHandler->process();
        if ($process) {
            $user = $form->getData();

            $this->authenticateUser($user);
            $route = 'fos_user_registration_confirmed';

            $this->setFlash('success', 'Account successfully created.');
            $url = $this->container->get('router')->generate($route);

            return new RedirectResponse($url);
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.' . $this->getEngine(), array(
            'form' => $form->createView(),
        ));
    }
}
