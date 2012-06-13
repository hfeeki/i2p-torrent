<?php

namespace SOTB\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;


/**
 * @author Matt Drollette <matt@drollette.com>
 */
class TorrentFile extends Constraint
{
    public $message = 'The file uploaded it not a valid torrent file.';
}