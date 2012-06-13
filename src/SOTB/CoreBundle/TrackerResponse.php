<?php

namespace SOTB\CoreBundle;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class TrackerResponse extends Response
{

    public function __construct(array $parameters)
    {
        parent::__construct(bencode($parameters), 200, array('Content-Type' => 'text/plain'));
    }
}
