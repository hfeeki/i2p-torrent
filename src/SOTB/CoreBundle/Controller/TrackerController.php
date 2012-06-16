<?php

namespace SOTB\CoreBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

use SOTB\CoreBundle\TrackerResponse;

class TrackerController implements ContainerAwareInterface
{
    private $container;

    function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function announceAction(Request $request)
    {
        $params = new ParameterBag();

        // use the IP from the query string, or the clientIP, even though thats always localhost
        $params->set('ip', $request->query->get('ip', $request->getClientIp()));

        // These parameters are always required
        if (
            !$request->query->has('info_hash') ||
            !$request->query->has('peer_id') ||
            !$request->query->has('uploaded') ||
            !$request->query->has('downloaded') ||
            !$request->query->has('left')
        ) {
            return $this->announceFailure("Invalid get parameters.");
        }

        // Parse and validate the info hash
        $info_hash = $this->parseInfoHash($request->query->get('info_hash'));
        if (null === $info_hash) {
            return $this->announceFailure("Invalid length of info_hash. " . $info_hash);
        }

        // Parse and validate the peer id
        $peer_id = urldecode($request->query->get('peer_id'));
        if (!preg_match('/^[\x20-\x7f]*$/D', $peer_id)) {
            $peer_id = bin2hex($peer_id);
        }
        // todo: this isn't an accurate check, but it's a start
        if (strlen($peer_id) < 5 || strlen($peer_id) > 128) {
            return $this->announceFailure("Invalid length of peer_id. " . $peer_id);
        }

        // validate uploaded
        if (!(is_numeric($request->query->getInt('uploaded')) && is_int($request->query->getInt('uploaded') + 0))) {
            return $this->announceFailure("Invalid uploaded value.");
        }
        $params->set('uploaded', $request->query->getInt('uploaded'));

        // validate downloaded
        if (!(is_numeric($request->query->getInt('downloaded')) && is_int($request->query->getInt('downloaded') + 0))) {
            return $this->announceFailure("Invalid downloaded value.");
        }
        $params->set('downloaded', $request->query->getInt('downloaded'));

        // validate left
        if (!(is_numeric($request->query->getInt('left')) && is_int($request->query->getInt('left') + 0))) {
            return $this->announceFailure("Invalid left value.");
        }
        $params->set('left', $request->query->getInt('left'));

        // validate numwant
        if ($request->query->has('numwant') && !(is_numeric($request->query->getInt('left')) && is_int($request->query->getInt('left') + 0))) {
            return $this->announceFailure("Invalid numwant value.");
        }
        $params->set('numwant', $request->query->getInt('numwant', 50));

        // validate the event, if it's set
        if ($request->query->has('event') && !in_array($request->query->get('event'), array('started', 'stopped', 'completed'))) {
            return $this->announceFailure('Invalid event.');
        }
        $params->set('event', $request->query->get('event'));

        // Optional
        $params->set('port', $request->query->get('port'));
        $params->set('compact', $request->query->get('compact', false));
        $params->set('no_peer_id', $request->query->get('no_peer_id', false));

        // Process the announce
        $tracker = $this->container->get('tracker');
        $response = $tracker->announce($info_hash, $peer_id, $params);

        return $response;
    }

    protected function parseInfoHash($hash)
    {
        $info_hash = urldecode($hash);
        if (!ctype_xdigit($info_hash)) {
            $info_hash = bin2hex($info_hash);
        }
        if (40 != strlen($info_hash) || !ctype_xdigit($info_hash)) {
            return;
        }

        return $info_hash;
    }

    public function scrapeAction(Request $request)
    {
        $qs = $this->proper_parse_str($request->getQueryString());

        if (!array_key_exists('info_hash', $qs)) {
            return $this->announceFailure('Invalid info hash.');
        }

        $info_hash = $qs['info_hash'];

        if (!is_array($info_hash)) {
            $info_hash = array($info_hash);
        }

        // parse the hashes for each item
        $obj = $this;
        $info_hash = array_map(function($v) use ($obj)
        {
            return $obj->parseInfoHash($v);
        }, $info_hash);


        // strip out any null values from the parse infohash array
        $info_hash = array_filter($info_hash, function ($val)
        {
            return null !== $val;
        });

        $tracker = $this->container->get('tracker');
        $response = $tracker->scrape(array_unique($info_hash));

        return $response;
    }

    protected function announceFailure($msg)
    {
        return new TrackerResponse(array('failure reason' => $msg));
    }

    protected function proper_parse_str($str)
    {
        # result array
        $arr = array();

        if (null === $str || '' === $str) {
            return $arr;
        }

        # split on outer delimiter
        $pairs = explode('&', $str);

        # loop through each pair
        foreach ($pairs as $i) {
            # split into name and value
            list($name, $value) = explode('=', $i, 2);

            // TODO: strip out (or index) any [] brackets in the name
            $name = urldecode($name);
            $value = urldecode($value);

            # if name already exists
            if (isset($arr[$name]) || 'info_hash' === $name) {
                if (!array_key_exists($name, $arr)) {
                    $arr[$name] = $value;
                }
                # stick multiple values into an array
                if (is_array($arr[$name])) {
                    $arr[$name][] = $value;
                } else {
                    $arr[$name] = array($arr[$name], $value);
                }
            } # otherwise, simply stick it in a scalar
            else {
                $arr[$name] = $value;
            }
        }

        # return result array
        return $arr;
    }
}
