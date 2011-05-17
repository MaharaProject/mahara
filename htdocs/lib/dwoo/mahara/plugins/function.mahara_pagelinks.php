<?php

/**
 * Dwoo {mahara_pagelinks} function pluging
 *
 * appends an 'offset=n' parameter to the url to get the url for a different page
 * @param integer $limit  number of items per page
 * @param integer $offset offset of first data item on this page
 * @param integer $count  total number of items
 * @param string  $url    where to get results from
 * @param string  $assign the template var to assign the result to
 * @return string
 *
 * THIS IS DEPRECATED. Pagination is done differently almost everywhere else
 * (e.g. find friends). One place this is being used is the admin user search.
 * Hopefully that can be converted away soon.
 */

function Dwoo_Plugin_mahara_pagelinks(Dwoo $dwoo, $offset, $limit, $count, $url, $assign='') {
    $offset = param_integer('offset', 0);
    $limit  = intval($limit);
    $count  = intval($count);
    $url    = hsc($url);

    $id = substr(md5(microtime()), 0, 4);
    $output = '<div class="pagination" id="' . $id . '">';

    if ($limit <= $count) {
        $pages = ceil($count / $limit);
        $page = $offset / $limit;

        $last = $pages - 1;
        $next = min($last, $page + 1);
        $prev = max(0, $page - 1);

        // Build a list of what pagenumbers will be put between the previous/next links
        $pagenumbers = array(0, $prev, $page, $next, $last);
        $pagenumbers = array_unique($pagenumbers);

        // Build the first/previous links
        $isfirst = $page == 0;
        $output .= mahara_pagelink('first', $url, 0, '&laquo; ' . get_string('first'), get_string('firstpage'), $isfirst);
        $output .= mahara_pagelink('prev', $url, $limit * $prev, '&larr; ' . get_string('previous'), get_string('prevpage'), $isfirst);

        // Build the pagenumbers in the middle
        foreach ($pagenumbers as $k => $i) {
            if ($k != 0 && $prevpagenum < $i - 1) {
                $output .= '…';
            }
            if ($i == $page) {
                $output .= '<span class="selected">' . ($i + 1) . '</span>';
            }
            else {
                $output .= mahara_pagelink('', $url, $limit * $i, $i + 1, '', false);
            }
            $prevpagenum = $i;
        }

        // Build the next/last links
        $islast = $page == $last;
        $output .= mahara_pagelink('next', $url, $limit * $next, get_string('next') . ' &rarr;', get_string('nextpage'), $islast);
        $output .= mahara_pagelink('last', $url, $limit * $last, get_string('last') . ' &raquo;', get_string('lastpage'), $islast);
    }

    // Close the container div
    $output .= '</div>';

    if (!empty($assign)) {
        $dwoo->assignInScope($output, $assign);
        return;
    }

    return $output;

}

function mahara_pagelink($class, $url, $offset, $text, $title, $disabled=false) {
    $return = '<span class="pagination';
    $return .= ($class) ? " $class" : '';

    if ($disabled) {
        $return .= ' disabled">' . $text . '</span>';
    }
    else {
        $return .= '">' 
            . '<a href="' . $url . '&offset=' . $offset
            . '" title="' . $title . '">' . $text . '</a></span>';
    }

    return $return;
}
