<?php

namespace SOTB\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use SOTB\CoreBundle\MagnetUri;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class HashOrMagnetValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {

        // first lets see if they entered a raw 40-char sha1 hex hash
        preg_match('/^([[:alnum:]]{40})$/i', $value, $result);
        if (isset($result[1]) && 40 === strlen($value)) {
            return;
        }
        unset($result);

        // next check for a 32-char base32 hash
        preg_match('/^([[:alnum:]]{32})$/i', $value, $result);
        if (isset($result[1]) && 32 === strlen($value)) {
            return;
        }
        unset($result);

        // next we check for valid magnet link
        $magnet = new MagnetUri($value);

        // btih
        preg_match('/urn:btih:([[:alnum:]]+)/i', $magnet->xt, $result);
        if (isset($result[1]) && 40 === strlen($result[1])) {
            return;
        }
        unset($result);

        // sha1
        preg_match('/urn:sha1:([[:alnum:]]+)/i', $magnet->xt, $result);
        if (isset($result[1]) && 32 === strlen($result[1])) {
            return;
        }
        unset($result);

        $this->context->addViolation($constraint->message, array('%string%' => $value));
    }
}