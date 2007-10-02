<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Creates pagination links for a table
 * appends an 'offset=n' parameter to the url to get the url for a different page
 * @param integer $limit  number of items per page
 * @param integer $offset offset of first data item on this page
 * @param integer $count  total number of items
 * @param string  $url    where to get results from
 * @return string
 */

function smarty_function_mahara_pagelinks($params, &$smarty) {

    $output = '';

    if ($params['limit'] <= $params['count']) {
        $pages = ceil($params['count'] / $params['limit']);
        $page = $params['offset'] / $params['limit'];

        $last = $pages - 1;
        $next = min($last, $page + 1);
        $prev = max(0, $page - 1);

        $pagenumbers = array_values(array(0 => 0,
                                          $prev => $prev,
                                          $page => $page,
                                          $next => $next,
                                          $last => $last));

        if ($page != 0) {
            $output .= mahara_pagelink('prev', $params['url'], $params['limit'] * $prev, 
                                       get_string('prevpage'));
        }

        foreach ($pagenumbers as $k => $i) {
            if ($k != 0 && $prevpagenum < $i - 1) {
                $output .= '...';
            }
            $output .= mahara_pagelink($i == $page ? ' selected' : '', $params['url'],
                                       $params['limit'] * $i, $i+1);
            $prevpagenum = $i;
        }

        if ($page < $pages - 1) {
            $output .= mahara_pagelink('next', $params['url'], $params['limit'] * $next,
                                       get_string('nextpage'));
        }

    }

    if (!empty($params['assign'])) {
        $smarty->assign($params['assign'], $output);
        return;
    }

    return $output;

}

function mahara_pagelink($class, $url, $offset, $text) {
    return '<span class="search-results-page' . (!empty($class) ? " $class" : '') . '"><a href="'
        . $url . '&amp;offset=' . $offset . '">' . $text . '</a></span>' . "\n";
}


?>