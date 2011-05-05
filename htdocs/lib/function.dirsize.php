<?php
/**
 * Calculate the size of a directory by iterating its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.2.0
 * @link        http://aidanlister.com/repos/v/function.dirsize.php
 * @param       string   $directory    Path to directory
 * @copyright   Resides in the Public Domain, you are free to modify,
 *              distribute or resell this code however you wish.
 */
function dirsize($path)
{
    // Init
    $size = 0;

    // Trailing slash
    if (substr($path, -1, 1) !== '/') {
        $path .= '/';
    }

    // Sanity check
    if (is_file($path)) {
        return filesize($path);
    } elseif (!is_dir($path)) {
        return false;
    }

    // Iterate queue
    $queue = array($path);
    for ($i = 0, $j = count($queue); $i < $j; ++$i)
    {
        // Open directory
        $parent = $i;
        if (is_dir($queue[$i]) && $dir = @dir($queue[$i])) {
            $subdirs = array();
            while (false !== ($entry = $dir->read())) {
                // Skip pointers
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                // Get list of directories or filesizes
                $path = $queue[$i] . $entry;
                if (is_dir($path)) {
                    $path .= '/';
                    $subdirs[] = $path;
                } elseif (is_file($path)) {
                    $size += filesize($path);
                }
            }

            // Add subdirectories to start of queue
            unset($queue[0]);
            $queue = array_merge($subdirs, $queue);

            // Recalculate stack size
            $i = -1;
            $j = count($queue);

            // Clean up
            $dir->close();
            unset($dir);
        }
    }

    return $size;
}
?>