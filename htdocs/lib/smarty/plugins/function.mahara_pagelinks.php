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
 *
 * THIS IS DEPRECATED. The build_pagelinks function in lib/mahara.php should be 
 * used instead.
 * One place this is being used is the admin user search. Hopefully that can be 
 * converted away soon.
 */

function smarty_function_mahara_pagelinks($params, &$smarty) {

    $id = substr(md5(microtime()), 0, 4);
    $output = '<div class="pagination" id="' . $id . '">';

    $params['offsetname'] = (isset($params['offsetname'])) ? $params['offsetname'] : 'offset';
    $params['offset'] = param_integer($params['offsetname'], 0);

    $params['firsttext'] = (isset($params['firsttext'])) ? $params['firsttext'] : get_string('first');
    $params['previoustext'] = (isset($params['previoustext'])) ? $params['previoustext'] : get_string('previous');
    $params['nexttext']  = (isset($params['nexttext']))  ? $params['nexttext'] : get_string('next');
    $params['lasttext']  = (isset($params['lasttext']))  ? $params['lasttext'] : get_string('last');

    if (!isset($params['numbersincludefirstlast'])) {
        $params['numbersincludefirstlast'] = true;
    }
    if (!isset($params['numbersincludeprevnext'])) {
        $params['numbersincludeprevnext'] = true;
    }

    if ($params['limit'] <= $params['count']) {
        $pages = ceil($params['count'] / $params['limit']);
        $page = $params['offset'] / $params['limit'];

        $last = $pages - 1;
        $next = min($last, $page + 1);
        $prev = max(0, $page - 1);

        // Build a list of what pagenumbers will be put between the previous/next links
        $pagenumbers = array();
        if ($params['numbersincludefirstlast']) {
            $pagenumbers[] = 0;
        }
        if ($params['numbersincludeprevnext']) {
            $pagenumbers[] = $prev;
        }
        $pagenumbers[] = $page;
        if ($params['numbersincludeprevnext']) {
            $pagenumbers[] = $next;
        }
        if ($params['numbersincludefirstlast']) {
            $pagenumbers[] = $last;
        }
        $pagenumbers = array_unique($pagenumbers);

        // Build the first/previous links
        $isfirst = $page == 0;
        $output .= mahara_pagelink('first', $params['url'], 0, '&laquo; ' . $params['firsttext'], get_string('firstpage'), $isfirst, $params['offsetname']);
        $output .= mahara_pagelink('prev', $params['url'], $params['limit'] * $prev, 
            '&larr; ' . $params['previoustext'], get_string('prevpage'), $isfirst, $params['offsetname']);

        // Build the pagenumbers in the middle
        foreach ($pagenumbers as $k => $i) {
            if ($k != 0 && $prevpagenum < $i - 1) {
                $output .= '…';
            }
            if ($i == $page) {
                $output .= '<span class="selected">' . ($i + 1) . '</span>';
            }
            else {
                $output .= mahara_pagelink('', $params['url'],
                    $params['limit'] * $i, $i + 1, '', false, $params['offsetname']);
            }
            $prevpagenum = $i;
        }

        // Build the next/last links
        $islast = $page == $last;
        $output .= mahara_pagelink('next', $params['url'], $params['limit'] * $next,
            $params['nexttext'] . ' &rarr;', get_string('nextpage'), $islast, $params['offsetname']);
        $output .= mahara_pagelink('last', $params['url'], $params['limit'] * $last,
            $params['lasttext'] . ' &raquo;', get_string('lastpage'), $islast, $params['offsetname']);
    }

    if (isset($params['json_script']) && isset($params['datatable'])) {
        $paginator_js = hsc(get_config('wwwroot') . 'js/paginator.js');
        $datatable    = json_encode($params['datatable']);
        $json_script  = json_encode($params['json_script']);
        $output .= <<<EOF
<script type="text/javascript" src="$paginator_js"></script>
<script type="text/javascript">
new Paginator("$id", $datatable, $json_script);
</script>
EOF;
    }

    // Close the container div
    $output .= '</div>';

    if (!empty($params['assign'])) {
        $smarty->assign($params['assign'], $output);
        return;
    }

    return $output;

}

function mahara_pagelink($class, $url, $offset, $text, $title, $disabled=false, $offsetname='offset') {
    $return = '<span class="pagination';
    $return .= ($class) ? " $class" : '';

    if ($disabled) {
        $return .= ' disabled">' . $text . '</span>';
    }
    else {
        $return .= '">' 
            . '<a href="' . $url . '&amp;' . $offsetname . '=' . $offset
            . '" title="' . $title . '">' . $text . '</a></span>';
    }

    return $return;
}


?>
