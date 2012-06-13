<?php

namespace SOTB\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use SOTB\CoreBundle\MagnetUri;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class TorrentFileValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /** @var $value \Symfony\Component\HttpFoundation\File\UploadedFile */

        if ('torrent' !== pathinfo($value->getClientOriginalName(), PATHINFO_EXTENSION)) {
            $this->context->addViolation('The file must end with .torrent');

            return;
        }

        $contents = bdecode(file_get_contents($value->getPathname()));

        if (!is_array($contents) || !array_key_exists('info', $contents)) {
            $this->context->addViolation('The file does not appear to be a valid torrent file.');

            return;
        }
    }
}