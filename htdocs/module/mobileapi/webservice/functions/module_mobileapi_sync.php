<?php
/**
 *
 * @package    mahara
 * @subpackage module-mobileapi
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */
if (!defined('INTERNAL')) {
    die();
}
require_once(get_config('docroot') . 'webservice/lib.php');

/**
 * Functions needed by the Mahara Mobile app. The functions in this class fetch similar data
 * to the legacy api/mobile/sync.php script.
 */
class module_mobileapi_sync extends external_api {

    /**
     * Batch processing function, to sync all the available fields in one request.
     * Leave a param empty or null to skip retrieving that field. Values for
     * each field are passed on as params to their underlying function; if you
     * only need the defaults, just send an empty array.
     *
     * @param array $blogs Params for get_blogs()
     * @param array $folders Params for get_folders()
     * @param array $notifications Params for get_notifications()
     * @param array $tags Params for get_tags()
     * @param boolean $userprofile True to retrieve user profile info (no params)
     * @param array $userprofileicon Params for get_user_profileicon()
     * @return array An array with one item for each requested data type.
     */
    public static function sync($blogs = null, $folders = null, $notifications = null, $tags = null, $userprofile = null, $userprofileicon = null) {
        $returndata = array();
        if ($blogs !== null) {
            $returndata['blogs'] = forward_static_call_array(
                array('static', 'get_blogs'),
                $blogs
            );
        }

        if ($folders !== null) {
            $returndata['folders'] = forward_static_call_array(
                array('static', 'get_folders'),
                $folders
            );
        }

        if ($notifications !== null) {
            $returndata['notifications'] = forward_static_call_array(
                array('static', 'get_notifications'),
                $notifications
            );
        }

        if ($tags !== null) {
            $returndata['tags'] = forward_static_call_array(
                array('static', 'get_tags'),
                $tags
            );
        }

        if ($userprofile !== null) {
            // No params on this one
            $returndata['userprofile'] = forward_static_call(
                array('static', 'get_user_profile')
            );
        }

        if ($userprofileicon !== null) {
            $returndata['userprofileicon'] = forward_static_call(
                array('static', 'get_user_profileicon')
            );
        }
        return $returndata;
    }

    /**
     * Auto-generate the params for the sync method by combining the params for
     * all its constituent methods.
     */
    public static function sync_parameters() {
        $blogparams = self::get_blogs_parameters();
        $folderparams = self::get_folders_parameters();
        $notifparams = self::get_notifications_parameters();
        $tagsparams = self::get_tags_parameters();
        $userprofileparams = self::get_user_profile_parameters();
        $iconparams = self::get_user_profileicon_parameters();
        return new external_function_parameters(
            array(
                'blogs' => new external_single_structure($blogparams->keys, "Retrieve blogs", VALUE_DEFAULT, null),
                'folders' => new external_single_structure($folderparams->keys, "Retrieve folders", VALUE_DEFAULT, null),
                'notifications' => new external_single_structure($notifparams->keys, "Retrieve notifications", VALUE_DEFAULT, null),
                'tags' => new external_single_structure($tagsparams->keys, "Retrieve tags", VALUE_DEFAULT, null),
                'userprofile' => new external_single_structure(array(), "Retrieve user profile", VALUE_DEFAULT, null),
                'userprofileicon' => new external_single_structure($iconparams->keys, "Retrieve info about the user's profile icon", VALUE_DEFAULT, null),
            )
        );
    }

    /**
     * Auto-generate the return values for the sync method by combining the return
     * values of all its constituent methods.
     */
    public static function sync_returns() {
        $keys = array(
            'blogs' => self::get_blogs_returns(),
            'folders' => self::get_folders_returns(),
            'notifications' => self::get_notifications_returns(),
            'tags' => self::get_tags_returns(),
            'userprofile' => self::get_user_profile_returns(),
            'userprofileicon' => self::get_user_profileicon_returns(),
        );

        // Modify to indicate that each of these return
        // fields is optional in the ouput. (Only present
        // if asked for.)
        foreach ($keys as $item) {
            $item->required = VALUE_OPTIONAL;
        }

        return new external_single_structure($keys);
    }

    /**
     * Parameters for get_user_profile method.
     */
    public static function get_user_profile_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Return values of get_user_profile method
     */
    public static function get_user_profile_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'ID of the user the auth token belongs to'),
                'myname' => new external_value(PARAM_RAW, "User's human-readable name"),
                'username' => new external_value(PARAM_RAW, "User's Mahara username"),
                'profileurl' => new external_value(PARAM_RAW, "URL of the user's profile page"),
                'quota' => new external_value(PARAM_RAW, "User's maximum file storage allowed in Mahara (in bytes)"),
                'quotaused' => new external_value(PARAM_RAW, "User's current file storage usage in Mahara (in bytes)")
            )
        );
    }

    /**
     * Get user profile information about the user the webservice token
     * belongs to.
     */
    public static function get_user_profile() {
        global $USER;
        return array(
            'id' => $USER->get('id'),
            'myname' => display_name($USER, null, true),
            'username' => $USER->get('username'),
            'profileurl' => profile_url($USER),
            'quota' => $USER->get('quota'),
            'quotaused' => $USER->get('quotaused'),
        );
    }


    /**
     * Parameters for get_user_profileicon method
     */
    public static function get_user_profileicon_parameters() {
        return new external_function_parameters(
            array(
                'maxdimension' => new external_value(PARAM_INT, "Scale icon so that height or width is this size (in px)", VALUE_DEFAULT, 0)
            )
        );
    }

    /**
     * Return values of get_user_profileicon method
     */
    public static function get_user_profileicon_returns() {
        return new external_single_structure(
            array(
                'name' => new external_value(PARAM_RAW, "Original filename of the profile icon"),
                'desc' => new external_value(PARAM_RAW, "Descripion of the icon (usually same as filename)"),
                'mimetype' => new external_value(PARAM_RAW, "Mimetype of the file"),
                'bytes' => new external_value(PARAM_INT, "Size of the file, in bytes"),
            ),
            "Metadata about the user's current profile icon",
            VALUE_OPTIONAL
        );
    }

    /**
     * Retrieves metadata about the user's profile icon.
     * (Use module/mobileapi/download.php to download the actual file)
     * @param number $maxdimension Max width and/or height (Not actually
     * used in the metadata function, but it's here to support the
     * function when used in download.php).
     */
    public static function get_user_profileicon($maxdimension = 0) {
        global $USER;

        // Convert ID of user to the ID of a profileicon
        $data = get_record_sql('
            SELECT f.size, a.title, a.note, f.filetype
            FROM
                {usr} u
                JOIN {artefact_file_files} f
                    ON u.profileicon = f.artefact
                JOIN {artefact} a
                    ON a.id = u.profileicon
                    AND a.artefacttype=\'profileicon\'
            WHERE u.id = ?',
            array($USER->get('id'))
        );

        // TODO: Gravatar support
        if (!$data) {
            // No profile icon selected.
            return null;
        }

        return array(
            'name' => $data->note,
            'desc' => $data->title,
            'mimetype' => $data->filetype,
            'bytes' => (int) $data->size,
        );
    }

    /**
     * Description of parameters for get_notifications method
     */
    public static function get_notifications_parameters() {
        return new external_function_parameters(
            array(
                'lastsync' => new external_value(PARAM_INT, "Only retrieve notifications newer than this timestamp"),
                'limit' => new external_value(PARAM_INT, "Max number of notifications to retrieve", VALUE_DEFAULT, 50),
                'offset' => new external_value(PARAM_INT, "Skip this many notifications when retrieving (for paging)", VALUE_DEFAULT, 0),
                'unreadonly' => new external_value(PARAM_BOOL, "Whether to only retrieve unread notifications", VALUE_DEFAULT, true),
                'types' => new external_multiple_structure(
                    new external_value(PARAM_ALPHA),
                    "Only retrieve notifications of these types",
                    VALUE_DEFAULT,
                    array()
                )
            )
        );
    }

    /**
     * Description of return values for get_notifications method
     */
    public static function get_notifications_returns() {
        return new external_single_structure(
            array(
                'synctime' => new external_value(PARAM_INT, "Current timestamp on server"),
                'numnotifications' => new external_value(PARAM_INT, "Total number of unread notifications available"),
                'notifications' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, "notification record id"),
                            'subject' => new external_value(PARAM_RAW, "Notification's subject line"),
                            'message' => new external_value(PARAM_RAW, "Notification's body"),
                        )
                    )
                )
            )
        );
    }


    /**
     * Get information about user's inbox notifications.
     * @param int $lastsync Unix epoch for last sync (only get messages newer than this)
     * @param number $limit (Default 50) Max number to retrieve
     * @param number $offset (Default 0) Offset (for paging)
     * @param boolean $unreadonly (Default true) Only retrieve unread notifications.
     * (Note this function does NOT mark them as read.)
     * @param array $types (Default: null) Only retrieve these notification types
     * @return array
     */
    public static function get_notifications($lastsync, $limit = 50, $offset = 0, $unreadonly = true, $types = array()) {
        global $USER;

        $notification_types_sql = '';
        if ($types) {
            $notification_types_sql = ' a.name IN (' . join(',', array_map('db_quote',$types)) . ')';
        }
        $unreadsql = '';
        if ($unreadonly) {
            $unreadsql = 'AND n.read = 0';
        }

        $now = time();
        if ($lastsync > $now) {
            throw new WebserviceInvalidParameterException("requested lastsync time is in the future");
        }

        $sql = " FROM {notification_internal_activity} n
        INNER JOIN {activity_type} a ON n.type=a.id
        WHERE $notification_types_sql
        " . db_format_tsfield('ctime', false) . " BETWEEN ? AND ?
        AND n.usr= ? "
        . $unreadsql;

        $sqlparams = array($lastsync, $now, $USER->id);

        $total = count_records_sql(
            'SELECT count(*)' . $sql,
            $sqlparams
        );
        $activity_arr = get_records_sql_array(
            "SELECT n.id, n.subject, n.message"
            . $sql,
            $sqlparams,
            $offset,
            $limit
        );

        if (!$activity_arr) {
            $activity_arr = array();
        }
        return array(
            'synctime' => $now,
            'numnotifications' => $total,
            'notifications' => $activity_arr
        );
    }


    /**
     * Description of parameters for get_tags method
     */
    public static function get_tags_parameters() {
        return new external_function_parameters(
            array(
                'sort' => new external_value(
                    PARAM_ALPHA,
                    "Sort alphabetically (alpha) or by usage (freq).",
                    VALUE_DEFAULT,
                    'alpha'
                ),
                'limit' => new external_value(
                    PARAM_INT,
                    "Upper limit of how many tags to return.",
                    VALUE_DEFAULT,
                    50
                ),
                'offset' => new external_value(PARAM_INT, "Skip this many tags", VALUE_DEFAULT, 0)
            )
        );
    }

    /**
     * Description of return values for get_tags method
     */
    public static function get_tags_returns() {
        return new external_single_structure(
            array(
                'numtags' => new external_value(PARAM_INT, "Total number of tags the user has"),
                'tags' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'tag' => new external_value(PARAM_RAW, "Text of the tag"),
                            'usage' => new external_value(PARAM_INT, "Number of times the tag has been used")
                        )
                    )
                )
            )
        );
    }


    /**
     * Retrieve the user's tags
     * @param string $sort (Default 'alpha') How to sort: "alpha" or "freq"
     * @param number $limit (Default 50) Upper limit on how many to return
     * @param number $offset (Default 0) Skip this many initial tags
     * @return array
     */
    public static function get_tags($sort = 'alpha', $limit = 50, $offset = 0) {
        if ($sort !== 'alpha' && $sort !== 'freq') {
            $sort = 'alpha';
        }

        // No $offset argument to get_my_tags, so we'll simulate it afterward.
        $rawtags = get_my_tags(($limit + $offset), false, $sort);
        if (!is_array($rawtags)) {
            return array(
                'numtags' => 0,
                'tags' => array(),
            );
        }
        $total = count($rawtags);
        $rawtags = array_slice($rawtags, $offset, $limit);

        // Transform the tags into the format we need
        $rawtags = array_map(
            function($item) {
                return array(
                    'tag' => $item->tag,
                    'usage' => $item->count
                );
            },
            $rawtags
        );

        return array(
            'numtags' => $total,
            'tags' => $rawtags
        );
    }


    /**
     * Description of parameters for get_blogs method
     */
    public static function get_blogs_parameters() {
        return new external_function_parameters(
            array(
                'blogslimit' => new external_value(PARAM_INT, 'Max number of blogs to return', VALUE_DEFAULT, 10),
                'blogsoffset' => new external_value(PARAM_INT, 'DB offset of returned blogs', VALUE_DEFAULT, 0),
                'includeblogposts' => new external_value(PARAM_BOOL, 'Whether to include data about individual blog posts', VALUE_DEFAULT, false),
                'blogpostslimit' => new external_value(PARAM_INT, 'Max number of blog posts to return for each blog', VALUE_DEFAULT, 10),
                'blogpostsoffset' => new external_value(PARAM_INT, 'DB offset for returned blog posts', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Description of return values for get_blogs method
     */
    public static function get_blogs_returns() {
        return new external_single_structure(
            array(
                'numblogs' => new external_value(PARAM_INT, "Total number of blogs the user has"),
                'blogs' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, "ID of blog"),
                            'title' => new external_value(PARAM_RAW, "Title of blog"),
                            'description' => new external_value(PARAM_RAW, "Blog's description"),
                            'locked' => new external_value(PARAM_BOOL, "Whether the blog is currently locked"),
                            'numblogposts' => new external_value(PARAM_INT, "Number of posts in the blog"),
                            'blogposts' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'id' => new external_value(PARAM_INT, "ID of blog post"),
                                        'title' => new external_value(PARAM_RAW, "Title of blog post"),
                                        'locked' => new external_value(PARAM_BOOL, "Whether the blog post is currently locked"),
                                    )
                                ),
                                "List of posts in the blog",
                                VALUE_OPTIONAL
                            )
                        )
                    )
                ),
            )
        );
    }

    /**
     * Retrieve the user's blogs
     * @param number $blogslimit (Default 10) Max number of blogs to return
     * @param number $blogsoffset (Default 0) Offset (for paging through blogs)
     * @param boolean $includeblogposts (Default false) Whether to include data
     * about individual blog posts.
     * @param number $blogpostslimit (Default 10) Max number of blog posts per blog to return
     * @param number $blogpostsoffset (Default 0) Offset (for paging through blogposts)...
     * Although probably if you want to page through blogposts you should do it
     * for one blog at a time.
     * @return array
     */
    public static function get_blogs($blogslimit = 10, $blogsoffset = 0, $includeblogposts = false, $blogpostslimit = 10, $blogpostsoffset = 0) {
        safe_require('artefact', 'blog');
        list($count, $blogrecords) = ArtefactTypeBlog::get_blog_list($blogslimit, $blogsoffset);
        if (!$blogrecords) {
            return array(
                'numblogs' => $count,
                'blogs' => array()
            );
        }

        $blogreturn = array();
        foreach ($blogrecords as $blogrecord) {
            // TODO: Let the client deal with locked posts
            if (!$blogrecord->locked) {
                $blog = array(
                    'id' => $blogrecord->id,
                    'title' => $blogrecord->title,
                    'description' => $blogrecord->description,
                    'locked' => $blogrecord->locked,
                    'numblogposts' => $blogrecord->postcount,
                );

                if ($includeblogposts) {
                    $blogpostrecords = ArtefactTypeBlogpost::get_posts($blogrecord->id, $blogpostslimit, $blogpostsoffset, null);
                    if ($blogpostrecords) {
                        foreach ($blogpostrecords['data'] as $blogpost) {
                            // TODO: Let the client deal with locked posts
                            if (!$blogpost->locked) {
                                $blog['blogposts'][] = array(
                                    "id" => $blogpost->id,
                                    "title" => $blogpost->title,
                                    "locked" => $blogpost->locked
                                );
                            }
                        }
                    }
                }
            }
            $blogreturn[] = $blog;
        }

        return array(
            'numblogs' => $count,
            'blogs' => $blogreturn
        );
    }


    /**
     * Description of parameters for get_folders method
     */
    public static function get_folders_parameters() {
        return new external_function_parameters(
            array(
                'limit' => new external_value(PARAM_INT, "Max number of folders to return", VALUE_DEFAULT, 50),
                'offset' => new external_value(PARAM_INT, "Skip this many initial folders", VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Description of return values for get_folders method
     */
    public static function get_folders_returns() {
        return new external_single_structure(
            array(
                'numfolders' => new external_value(PARAM_INT, "Number of folders the user has"),
                'folders' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Id of folder'),
                            'title' => new external_value(PARAM_RAW, 'Title of folder'),
                        )
                    )
                )
            )
        );
    }

    /**
     * Gets the user's top-level folders.
     * TODO: Support the full folder hierarchy.
     * @param number $limit
     * @param number $offset
     * @return array
     */
    public static function get_folders($limit = 50, $offset = 0) {
        global $USER;
        safe_require('artefact', 'file');

        // No limit & offset params to get_my_files_data, so we'll just retrieve all of them
        // and slice if we need to.
        $folders = ArtefactTypeFile::get_my_files_data(0, $USER->id, null, null, array("artefacttype" => array("folder")));
        $count = count($folders);
        $folders = array_slice($folders, $offset, $limit);
        $folders = array_map(
            function($folder) {
                return array("id" => $folder->id, "title" => $folder->title);
            },
            $folders
        );
        return array(
            'numfolders' => $count,
            'folders' => $folders
        );
    }
}