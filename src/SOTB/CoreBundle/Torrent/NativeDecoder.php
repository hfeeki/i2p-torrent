<?php

namespace SOTB\CoreBundle\Torrent;

use InvalidArgumentException;
use PHP\BitTorrent\DecoderInterface;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class NativeDecoder implements DecoderInterface
{
    /**
     * Decode a file
     *
     * This method can use a strict method that requires certain elements to be present in the
     * encoded file. The two required elements are:
     *
     * - announce
     * - info
     *
     * Pr. default the method does not check for these elements.
     *
     * @param string  $file   Path to the torrent file we want to decode
     * @param boolean $strict If set to true this method will check for certain elements in the
     *                        dictionary.
     * @return array Returns the decoded version of the file as an array
     * @throws InvalidArgumentException
     */
    function decodeFile($file, $strict = false)
    {
        if (!is_readable($file)) {
            throw new InvalidArgumentException('File ' . $file . ' does not exist or can not be read.');
        }

        $dictionary = $this->decodeDictionary(file_get_contents($file, true));

        if ($strict) {
            if (!isset($dictionary['announce']) || !is_string($dictionary['announce'])) {
                throw new InvalidArgumentException('Missing "announce" key.');
            } else if (!isset($dictionary['info']) || !is_array($dictionary['info'])) {
                throw new InvalidArgumentException('Missing "info" key.');
            }
        }

        return $dictionary;    }

    /**
     * Decode any bittorrent encoded string
     *
     * @param string $string The string to decode
     * @return int|string|array Returns the native PHP counterpart of the encoded string
     * @throws InvalidArgumentException
     */
    function decode($string)
    {
        return bdecode($string);
    }

    /**
     * Decode an encoded PHP integer
     *
     * @param string $integer The integer to decode
     * @return int Returns the decoded integer
     * @throws InvalidArgumentException
     */
    function decodeInteger($integer)
    {
        return bdecode($integer);
    }

    /**
     * Decode an encoded PHP string
     *
     * @param string $string The string to decode
     * @return string Returns the decoded string value
     * @throws InvalidArgumentException
     */
    function decodeString($string)
    {
        return bdecode($string);
    }

    /**
     * Decode an encoded PHP array
     *
     * @param string $list Encoded list
     * @return array Returns a numerical array
     * @throws InvalidArgumentException
     */
    function decodeList($list)
    {
        return bdecode($list);
    }

    /**
     * Decode an encoded PHP associative array
     *
     * @param string $dictionary Encoded dictionary
     * @return array Returns an associative array
     * @throws InvalidArgumentException
     */
    function decodeDictionary($dictionary)
    {
        return bdecode($dictionary);
    }


}
