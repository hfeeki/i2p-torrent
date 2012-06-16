<?php

namespace SOTB\CoreBundle\Twig\Extension;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class MiscExtension extends \Twig_Extension
{

    public function getFilters()
    {
        return array(
            'human_size'  => new \Twig_Filter_Method($this, 'humanSize'),
            'array_sort'  => new \Twig_Filter_Method($this, 'arraySort'),
        );
    }

    public function humanSize($bytes, $precision = 2)
    {
        $kilobyte = 1024;
        $megabyte = $kilobyte * 1024;
        $gigabyte = $megabyte * 1024;
        $terabyte = $gigabyte * 1024;

        if (($bytes >= 0) && ($bytes < $kilobyte)) {
            return $bytes . ' B';

        } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
            return round($bytes / $kilobyte, $precision) . ' KB';

        } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
            return round($bytes / $megabyte, $precision) . ' MB';

        } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
            return round($bytes / $gigabyte, $precision) . ' GB';

        } elseif ($bytes >= $terabyte) {
            return round($bytes / $terabyte, $precision) . ' TB';
        } else {
            return $bytes . ' B';
        }
    }

    public function arraySort($arr, $col, $dir = SORT_DESC)
    {
        $sort_col = array();
        foreach ($arr as $key=> $row) {
            $sort_col[$key] = $row[$col];
        }

        array_multisort($sort_col, $dir, $arr);

        return $arr;
    }


    public function getName()
    {
        return 'misc_extension';
    }

}
