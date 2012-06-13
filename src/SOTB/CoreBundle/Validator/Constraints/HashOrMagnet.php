<?php

namespace SOTB\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;


/**
 * @author Matt Drollette <matt@drollette.com>
 */
class HashOrMagnet extends Constraint
{
    public $message = 'The string "%string%" is not a valid hash or a magnet link.';
}