<?php

namespace SOTB\CoreBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class TrackerController implements ContainerAwareInterface
{
    private $container;

    function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function announceAction(Request $request)
    {
        $tracker = $this->container->get('tracker');

        $params = $request->query;

        if (!$params->has('ip')) {
            $params->set('ip', $request->getClientIp());
        }

        $response = $tracker->announce($params);

        return $response;
    }

    public function scrapeAction(Request $request)
    {
        $tracker = $this->container->get('tracker');

        $qs = new ParameterBag($this->proper_parse_str($request->getQueryString()));

        $response = $tracker->scrape($qs);

        return $response;
    }

    protected function proper_parse_str($str)
    {
        # result array
        $arr = array();

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
