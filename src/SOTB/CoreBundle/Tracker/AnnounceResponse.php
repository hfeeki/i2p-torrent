<?php

namespace SOTB\CoreBundle\Tracker;

use Symfony\Component\HttpFoundation\Response;

use SOTB\CoreBundle\Tracker\Bencode;


/**
 * @author Matt Drollette <matt@drollette.com>
 */
class AnnounceResponse extends Response
{

    public function __construct(array $parameters)
    {
        parent::__construct(Bencode::encode($parameters));
    }
}
