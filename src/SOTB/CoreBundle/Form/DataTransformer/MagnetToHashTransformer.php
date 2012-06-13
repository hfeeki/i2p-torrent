<?php

namespace SOTB\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

use SOTB\CoreBundle\MagnetUri;
use SOTB\CoreBundle\Base32;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class MagnetToHashTransformer implements DataTransformerInterface
{
    // from db to form
    public function transform($value)
    {

        return $value;
    }

    // from form to db
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value) ) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $base32 = new Base32();

        // first lets see if they entered a raw 40-char sha1 hex hash
        preg_match('/^([[:alnum:]]{40})$/i', $value, $result);
        if (isset($result[1]) && 40 === strlen($value)) {
            return $result[1];
        }
        unset($result);

        // next check for a 32-char base32 hash
        preg_match('/^([[:alnum:]]{32})$/i', $value, $result);
        if (isset($result[1]) && 32 === strlen($value)) {
            return bin2hex($base32->base32_decode($result[1]));
        }
        unset($result);

        // next we check for valid magnet link
        $magnet = new MagnetUri($value);

        // btih
        preg_match('/urn:btih:([[:alnum:]]+)/i', $magnet->xt, $result);
        if (isset($result[1]) && 40 === strlen($result[1])) {
            return $result[1];
        }
        unset($result);

        // sha1
        preg_match('/urn:sha1:([[:alnum:]]+)/i', $magnet->xt, $result);
        if (isset($result[1]) && 32 === strlen($result[1])) {
            return bin2hex($base32->base32_decode($result[1]));
        }
        unset($result);

        return null;
    }
}
