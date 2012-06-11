<?php

namespace SOTB\CoreBundle;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class TorrentFile
{
    /**
     * @var array List of error occured
     */
    protected static $_errors = array();

    /** Read and decode torrent file/data OR build a torrent from source folder/file(s)
     * Supported signatures:
     * - Torrent(); // get an instance (usefull to scrape and check errors)
     * - Torrent( string $torrent ); // analyse a torrent file
     * - Torrent( string $torrent, string $announce );
     * - Torrent( string $torrent, array $meta );
     * - Torrent( string $file_or_folder ); // create a torrent file
     * - Torrent( string $file_or_folder, string $announce_url, [int $piece_length] );
     * - Torrent( string $file_or_folder, array $meta, [int $piece_length] );
     * - Torrent( array $files_list );
     * - Torrent( array $files_list, string $announce_url, [int $piece_length] );
     * - Torrent( array $files_list, array $meta, [int $piece_length] );
     * @param string|array torrent  to read or source folder/file(s) (optional, to get an instance)
     * @param string|array announce url or meta informations (optional)
     * @param              int      piece length (optional)
     */
    public function __construct($data = null, $meta = array(), $piece_length = 256)
    {
        if (is_null($data)) {

            return false;
        }

        if ($piece_length < 32 || $piece_length > 4096) {

            return self::set_error(new Exception('Invalid piece length, must be between 32 and 4096'));
        }

        if (is_string($meta)) {
            $meta = array('announce' => $meta);
        }

        if ($this->build($data, $piece_length * 1024)) {
            $this->touch();
        } else {
            $meta = array_merge($meta, $this->decode($data));
        }

        foreach ($meta as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function getInfo()
    {
        return $this->info;
    }

    /** Return last error message
     * @return string|boolean last error message or false if none
     */
    public function error()
    {
        return empty(self::$_errors) ?
            false :
            self::$_errors[0]->getMessage();
    }

    /** Return Errors
     * @return array|boolean error list or false if none
     */
    public function errors()
    {
        return empty(self::$_errors) ?
            false :
            self::$_errors;
    }

    /**** Getters and setters ****/

    /** Getter and setter of torrent announce url / list
     * If the argument is a string, announce url is added to announce list (or set as announce if announce is not set)
     * If the argument is an array/object, set announce url (with first url) and list (if array has more than one url), tiered list supported
     * If the argument is false announce url & list are unset
     * @param null|false|string|array announce url / list, reset all if false (optional, if omitted it's a getter)
     * @return string|array|null announce url / list or null if not set
     */
    public function announce($announce = null)
    {
        if (is_null($announce)) {

            return !isset($this->{'announce-list'}) ?
                isset($this->announce) ? $this->announce : null :
                $this->{'announce-list'};
        }

        $this->touch();

        if (is_string($announce) && isset($this->announce)) {

            return $this->{'announce-list'} = self::announce_list(isset($this->{'announce-list'}) ? $this->{'announce-list'} : $this->announce, $announce);
        }

        unset($this->{'announce-list'});

        if (is_array($announce) || is_object($announce)) {
            if (($this->announce = self::first_announce($announce)) && count($announce) > 1) {

                return $this->{'announce-list'} = self::announce_list($announce);

            } else {

                return $this->announce;
            }
        }
        if (!isset($this->announce) && $announce) {

            return $this->announce = (string)$announce;
        }

        unset($this->announce);
    }

    /** Getter and setter of torrent comment
     * @param null|string comment (optional, if omitted it's a getter)
     * @return string|null comment or null if not set
     */
    public function comment($comment = null)
    {
        return is_null($comment) ?
            isset($this->comment) ? $this->comment : null :
            $this->touch($this->comment = (string)$comment);
    }

    /** Getter and setter of torrent created_by
     * @param null|string created_by (optional, if omitted it's a getter)
     * @return string|null created_by or null if not set
     */
    public function created_by($created_by = null)
    {
        return is_null($created_by) ?
            isset($this->{'created by'}) ? $this->{'created by'} : null :
            $this->touch($this->{'created by'} = (string)$created_by);
    }

    public function creation_date($creation_date = null)
    {
        return is_null($creation_date) ?
            isset($this->{'creation date'}) ? $this->{'creation date'} : null :
            $this->touch($this->{'creation date'} = intval($creation_date));
    }

    /** Getter and setter of torrent name
     * @param null|string name (optional, if omitted it's a getter)
     * @return string|null name or null if not set
     */
    public function name($name = null)
    {
        return is_null($name) ?
            isset($this->info['name']) ? $this->info['name'] : null :
            $this->touch($this->info['name'] = (string)$name);
    }

    /** Getter and setter of private flag
     * @param null|boolean is private or not (optional, if omitted it's a getter)
     * @return boolean private flag
     */
    public function is_private($private = null)
    {
        return is_null($private) ?
            !empty($this->info['private']) :
            $this->touch($this->info['private'] = $private ? 1 : 0);
    }

    /** Getter and setter of webseed(s) url list ( GetRight implementation )
     * @param null|string|array webseed or webseeds mirror list (optional, if omitted it's a getter)
     * @return string|array|null webseed(s) or null if not set
     */
    public function url_list($urls = null)
    {
        return is_null($urls) ?
            isset($this->{'url-list'}) ? $this->{'url-list'} : null :
            $this->touch($this->{'url-list'} = is_string($urls) ? $urls : (array)$urls);
    }

    /** Getter and setter of httpseed(s) url list ( Bittornado implementation )
     * @param null|string|array httpseed or httpseeds mirror list (optional, if omitted it's a getter)
     * @return array|null httpseed(s) or null if not set
     */
    public function httpseeds($urls = null)
    {
        return is_null($urls) ?
            isset($this->httpseeds) ? $this->httpseeds : null :
            $this->touch($this->httpseeds = (array)$urls);
    }

    /**** Analyze BitTorrent ****/

    /** Get piece length
     * @return integer piece length or null if not set
     */
    public function piece_length()
    {
        return isset($this->info['piece length']) ?
            $this->info['piece length'] :
            null;
    }

    public function getPieces()
    {
        return isset($this->info['pieces']) ?
            $this->info['pieces'] :
            null;
    }

    /** Compute hash info
     * @return string hash info or null if info not set
     */
    public function hash_info()
    {
        return isset($this->info) ?
            sha1(self::encode($this->info)) :
            null;
    }

    /** List torrent content
     * @param integer|null size precision (optional, if omitted returns sizes in bytes)
     * @return array file(s) and size(s) list, files as keys and sizes as values
     */
    public function content($precision = null)
    {
        $files = array();
        if (isset($this->info['files']) && is_array($this->info['files'])) {
            foreach ($this->info['files'] as $file)
                $files[self::path($file['path'], $this->info['name'])] = $precision ?
                    self::format($file['length'], $precision) :
                    $file['length'];
        } elseif (isset($this->info['name'])) {
            $files[$this->info['name']] = $precision ?
                self::format($this->info['length'], $precision) :
                $this->info['length'];
        }

        return $files;
    }

    /** List torrent content pieces and offset(s)
     * @return array file(s) and pieces/offset(s) list, file(s) as keys and pieces/offset(s) as values
     */
    public function offset()
    {
        $files = array();
        $size = 0;
        if (isset($this->info['files']) && is_array($this->info['files'])) {
            foreach ($this->info['files'] as $file)
                $files[self::path($file['path'], $this->info['name'])] = array(
                    'startpiece'    => floor($size / $this->info['piece length']),
                    'offset'        => fmod($size, $this->info['piece length']),
                    'size'          => $size += $file['length'],
                    'endpiece'      => floor($size / $this->info['piece length'])
                );
        } elseif (isset($this->info['name'])) {
            $files[$this->info['name']] = array(
                'startpiece'    => 0,
                'offset'        => 0,
                'size'          => $this->info['length'],
                'endpiece'      => floor($this->info['length'] / $this->info['piece length'])
            );
        }

        return $files;
    }

    /** Sum torrent content size
     * @param integer|null size precision (optional, if omitted returns size in bytes)
     * @return integer|string file(s) size
     */
    public function size($precision = null)
    {
        $size = 0;
        if (isset($this->info['files']) && is_array($this->info['files'])) {
            foreach ($this->info['files'] as $file)
                $size += $file['length'];
        } elseif (isset($this->info['name'])) {
            $size = $this->info['length'];
        }

        return is_null($precision) ?
            $size :
            self::format($size, $precision);
    }

    /** Get magnet link
     * @param boolean html encode ampersand, default true (optional)
     * @return string magnet link
     */
    public function magnet($html = true)
    {
        $ampersand = $html ? '&amp;' : '&';

        return sprintf('magnet:?xt=urn:btih:%2$s%1$sdn=%3$s%1$sxl=%4$d%1$str=%5$s', $ampersand, $this->hash_info(), urlencode($this->name()), $this->size(), implode($ampersand . 'tr=', self::untier($this->announce())));
    }

    /**** Encode BitTorrent ****/

    /** Encode torrent data
     * @param mixed data to encode
     * @return string torrent encoded data
     */
    public static function encode($mixed)
    {
        return bencode($mixed);
    }

    /**** Decode BitTorrent ****/

    /** Decode torrent data or file
     * @param string data or file path to decode
     * @return array decoded torrent data
     */
    protected static function decode($string)
    {
        $data = is_file($string) ? file_get_contents($string) : $string;

        return bdecode($data);
    }

    /**** Internal Helpers ****/

    /** Build torrent info
     * @param string|array source  folder/file(s) path
     * @param              integer piece length
     * @return array|boolean torrent info or false if data isn't folder/file(s)
     */
    protected function build($data, $piece_length)
    {
        if (is_null($data)) {

            return false;
        } elseif (is_array($data) && self::is_list($data)) {

            return $this->info = $this->files($data, $piece_length);
        } elseif (is_dir($data)) {

            return $this->info = $this->folder($data, $piece_length);
        } elseif ((is_file($data)) && !self::is_torrent($data)) {

            return $this->info = $this->file($data, $piece_length);
        }

        return false;
    }

    /** Set torrent creator and creation date
     * @param any param
     * @return any param
     */
    protected function touch($void = null)
    {
        if (!isset($this->{'created by'})) {
            $this->{'created by'} = 'Anonymous';
        }

        $this->{'creation date'} = time();

        return $void;
    }

    /** Add an error to errors stack
     * @param Exception error to add
     * @param boolean   return error message or not (optional, default to false)
     * @return boolean|string return false or error message if requested
     */
    protected static function set_error($exception, $message = false)
    {
        return (array_unshift(self::$_errors, $exception) && $message) ? $exception->getMessage() : false;
    }

    /** Build announce list
     * @param string|array announce url / list
     * @param string|array announce url / list to add (optionnal)
     * @return array announce list (array of arrays)
     */
    protected static function announce_list($announce, $merge = array())
    {
        return array_map(create_function('$a', 'return (array) $a;'), array_merge((array)$announce, (array)$merge));
    }

    /** Get the first announce url in a list
     * @param array announce list (array of arrays if tiered trackers)
     * @return string first announce url
     */
    protected static function first_announce($announce)
    {
        while (is_array($announce)) {
            $announce = reset($announce);
        }

        return $announce;
    }

    /** Helper to pack data hash
     * @param string data
     * @return string packed data hash
     */
    protected static function pack(& $data)
    {
        return pack('H*', sha1($data)) . ($data = null);
    }

    /** Helper to build file path
     * @param array  file path
     * @param string base folder
     * @return string real file path
     */
    protected static function path($path, $folder)
    {
        array_unshift($path, $folder);

        return join(DIRECTORY_SEPARATOR, $path);
    }

    /** Helper to test if an array is a list
     * @param array array to test
     * @return boolean is the array a list or not
     */
    protected static function is_list($array)
    {
        foreach (array_keys($array) as $key) {
            if (!is_int($key)) {

                return false;
            }
        }

        return true;
    }

    /** Build pieces depending on piece length from a file handler
     * @param ressource file handle
     * @param integer   piece length
     * @param boolean   is last piece
     * @return string pieces
     */
    private function pieces($handle, $piece_length, $last = true)
    {
        static $piece, $length;
        if (empty($length)) {
            $length = $piece_length;
        }

        $pieces = null;
        while (!feof($handle)) {
            if (($length = strlen($piece .= fread($handle, $length))) == $piece_length) {
                $pieces .= self::pack($piece);
            } elseif (($length = $piece_length - $length) < 0) {

                return self::set_error(new Exception('Invalid piece length!'));
            }
        }
        fclose($handle);

        return $pieces . ($last && $piece ? self::pack($piece) : null);
    }

    /** Build torrent info from single file
     * @param string  file path
     * @param integer piece length
     * @return array torrent info
     */
    private function file($file, $piece_length)
    {
        if (!$handle = fopen($file, 'r')) {

            return self::set_error(new Exception('Failed to open file: "' . $file . '"'));
        }

        $path = explode(DIRECTORY_SEPARATOR, $file);

        return array(
            'length'          => filesize($file),
            'name'            => end($path),
            'piece length'    => $piece_length,
            'pieces'          => $this->pieces($handle, $piece_length)
        );
    }

    /** Build torrent info from files
     * @param array   file list
     * @param integer piece length
     * @return array torrent info
     */
    private function files($files, $piece_length)
    {
        $files = array_map('realpath', $files);
        sort($files);
        usort($files, create_function('$a,$b', 'return strrpos($a,DIRECTORY_SEPARATOR)-strrpos($b,DIRECTORY_SEPARATOR);'));
        $path = explode(DIRECTORY_SEPARATOR, dirname(realpath(current($files))));
        $pieces = null;
        $info_files = array();
        $count = count($files) - 1;
        foreach ($files as $i => $file) {
            if ($path != array_intersect_assoc($file_path = explode(DIRECTORY_SEPARATOR, $file), $path)) {
                self::set_error(new Exception('Files must be in the same folder: "' . $file . '" discarded'));
                continue;
            }
            if (!$handle = fopen($file, 'r')) {
                self::set_error(new Exception('Failed to open file: "' . $file . '" discarded'));
                continue;
            }
            $pieces .= $this->pieces($handle, $piece_length, $count == $i);
            $info_files[] = array(
                'length'      => filesize($file),
                'path'        => array_diff($file_path, $path)
            );
        }

        return array(
            'files'           => $info_files,
            'name'            => end($path),
            'piece length'    => $piece_length,
            'pieces'          => $pieces
        );

    }

    /** Build torrent info from folder content
     * @param string  folder path
     * @param integer piece length
     * @return array torrent info
     */
    private function folder($dir, $piece_length)
    {
        return $this->files(self::scandir($dir), $piece_length);
    }

    /** Helper to return the first char of encoded data
     * @param string encoded data
     * @return string|boolean first char of encoded data or false if empty data
     */
    private static function char($data)
    {
        return empty($data) ?
            false :
            substr($data, 0, 1);
    }

    /**** Public Helpers ****/

    /** Helper to format size in bytes to human readable
     * @param integer size in bytes
     * @param integer precision after coma
     * @return string formated size in appropriate unit
     */
    public static function format($size, $precision = 2)
    {
        $units = array('octets', 'Ko', 'Mo', 'Go', 'To');
        while (($next = next($units)) && $size > 1024) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . ($next ? prev($units) : end($units));
    }

    /** Helper to return filesize (even bigger than 2Gb -linux only- and distant files size)
     * @param string file path
     * @return double|boolean filesize or false if error
     */
    public static function filesize($file)
    {
        if (is_file($file)) {

            return (double)sprintf('%u', @filesize($file));
        } else if ($content_length = preg_grep($pattern = '#^Content-Length:\s+(\d+)$#i', (array)@get_headers($file))) {

            return (int)preg_replace($pattern, '$1', reset($content_length));
        }
    }

    /** Helper to scan directories files and sub directories recursivly
     * @param string directory path
     * @return array directory content list
     */
    public static function scandir($dir)
    {
        $paths = array();
        foreach (scandir($dir) as $item) {
            if ($item != '.' && $item != '..') {
                if (is_dir($path = realpath($dir . DIRECTORY_SEPARATOR . $item))) {
                    $paths = array_merge(self::scandir($path), $paths);
                } else {
                    $paths[] = $path;
                }
            }
        }

        return $paths;
    }

    /** Helper to check if a file is a torrent
     * @param string file location
     * @return boolean is the file a torrent or not
     */
    public static function is_torrent($file)
    {
        $content = file_get_contents($file);

        return substr($content, 0, 11) === 'd8:announce' || substr($content, 0, 14) === 'd10:created by';
    }

    /** Flatten announces list
     * @param array announces list
     * @return array flattened annonces list
     */
    public static function untier($announces)
    {
        $list = array();
        foreach ((array)$announces as $tier) {
            is_array($tier) ?
                $list = array_merge($list, self::untier($tier)) :
                array_push($list, $tier);
        }

        return $list;
    }

    public function getFile()
    {
        return bencode(get_object_vars($this));
    }

}
