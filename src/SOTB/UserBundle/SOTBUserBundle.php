<?php

namespace SOTB\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SOTBUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}