<?php

namespace SOTB\CoreBundle\Tracker;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class AnnounceResponse extends Response
{

    public function __construct(array $parameters)
    {
        parent::__construct(bencode($parameters));
    }
}
