<?php

namespace SOTB\CoreBundle\Torrent;

use PHP\BitTorrent\EncoderInterface;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class NativeEncoder implements EncoderInterface
{
    /**
     * Encode any encodable variable
     *
     * @param int|string|array $var The variable to encode
     * @return string Returns the encoded string
     * @throws \InvalidArgumentException
     */
    function encode($var)
    {
        return bencode($var);
    }

    /**
     * Encode an integer
     *
     * @param int $integer The integer to encoded
     * @return string Returns the encoded string
     * @throws \InvalidArgumentException
     */
    function encodeInteger($integer)
    {
        return bencode($integer);
    }

    /**
     * Encode a string
     *
     * @param string $string The string to encode
     * @return string Returns the encoded string
     * @throws \InvalidArgumentException
     */
    function encodeString($string)
    {
        return bencode($string);
    }

    /**
     * Encode a list (numerically indexed array)
     *
     * @param array $list The array to encode
     * @return string Returns the encoded string
     * @throws \InvalidArgumentException
     */
    function encodeList($list)
    {
        return bencode($list);
    }

    /**
     * Encode a dictionary (associative PHP array)
     *
     * @param array $dictionary The array to encode
     * @return string Returns the encoded string
     * @throws \InvalidArgumentException
     */
    function encodeDictionary($dictionary)
    {
        return bencode($dictionary);
    }

}
