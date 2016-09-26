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
 * to the legacy api/mobile/upload.php script.
 */
class module_mobileapi_upload extends external_api {

    /**
     * Description of parameters used by upload_file() method
     */
    public static function upload_file_parameters() {
        return new external_function_parameters(
            array(
                'filetoupload' => new external_value(PARAM_FILE, "The file to upload"),
                'foldername' => new external_value(PARAM_RAW, "Name of (top-level) folder to upload it into"),
                'title' => new external_value(PARAM_RAW, "Title for the file (defaults to filename)", VALUE_DEFAULT, null),
                'description' => new external_value(PARAM_RAW, "Description for file", VALUE_DEFAULT, null),
                'tags' => new external_multiple_structure(
                    new external_value(PARAM_RAW, "Text of tag"),
                    "List of tags to apply to the file",
                    VALUE_DEFAULT,
                    array()
                )
            )
        );
    }

    /**
     * Description of return values for upload_file() method
     */
    public static function upload_file_returns() {
        return new external_single_structure(
            array(
                'file' => new external_value(PARAM_INT, 'ID of the newly created file artefact')
            )
        );
    }

    /**
     * Upload a file by itself
     *
     * @param string $filetoupload Should correspond to a form-encoded file param (i.e. as if
     * from an <input type="file" name="filetoupload">)
     * @param string $foldername Name of (top-level) folder to upload to. Will create
     * the folder if it doesn't exist yet.
     * @param string $title (Default: filename) Title for the file
     * @param string $description (Default: null) Description for the title
     * @param array $tags (Default: null) Tags for the file
     */
    public static function upload_file($filetoupload, $foldername, $title=null, $description=null, $tags=array()) {
        // Most of the work is done in this internal function, so the same logic can
        // be used by upload_blog_post
        return array(
            'file' => self::handle_file_upload('filetoupload', null, $foldername, $title, $description, $tags)
        );
    }


    /**
     * Description of parameters for upload_blog_post() method
     */
    public static function upload_blog_post_parameters() {
        return new external_function_parameters(
            array(
                'blogid' => new external_value(PARAM_INT, "The blog to post it to"),
                'title' => new external_value(PARAM_RAW, "Title of the post"),
                'body' => new external_value(PARAM_RAW, "Body of the post"),
                'isdraft' => new external_value(PARAM_BOOL, "Put the new post in draft status", VALUE_DEFAULT, true),
                'allowcomments' => new external_value(PARAM_BOOL, "Allow comments on the post", VALUE_DEFAULT, false),
                'tags' => new external_multiple_structure(
                    new external_value(PARAM_RAW, "Text of tag"),
                    "Tags to apply to the blog post",
                    VALUE_DEFAULT,
                    array()
                ),
                'fileattachments' => new external_multiple_structure(
                    new external_value(PARAM_FILE, "Uploaded file"),
                    "Files to attach to the blog post",
                    VALUE_DEFAULT,
                    array()
                )
            )
        );
    }

    /**
     * Description of return values for upload_blog_post() method.
     */
    public static function upload_blog_post_returns() {
        return new external_single_structure(
            array(
                'blogpost' => new external_value(PARAM_INT, "ID of the blog post created"),
                'files' => new external_multiple_structure(
                    new external_value(PARAM_INT),
                    "IDs of file artefacts created",
                    VALUE_OPTIONAL
                )
            )
        );
    }

    /**
     * Upload a blog post (optionally with file attachments)
     *
     * @param int $blogid ID of the blog to upload to
     * @param string $title Title of the blog post
     * @param string $body Body of the blog post
     * @param boolean $isdraft (Default: true) Make new post in draft status
     * @param boolean $allowcomments (Default: false) Allow comments on new post
     * @param array $tags Tags to place on post
     * @param array $fileattachments Files to attach to post. These should be
     * uploaded as if they were in an <input type="file" name="fileattachments[]"> tag.
     * Files will be uploaded into a top-level folder that has the same name as the
     * blog. They'll also have the same tags as the blog post.
     * @return array
     * @throws WebserviceInvalidParameterException
     */
    public static function upload_blog_post(
        $blogid,
        $title,
        $body,
        $isdraft = true,
        $allowcomments = false,
        $tags = array(),
        $fileattachments = array()
    ) {
        global $USER;

        $blogrec = get_record('artefact', 'id', $blogid, 'owner', $USER->get('id'));
        if (!$blogrec) {
            throw new WebserviceInvalidParameterException("Invalid blog id");
        }

        safe_require('artefact', 'blog');
        $postobj = new ArtefactTypeBlogPost(null, null);
        $postobj->set('title', $title);
        $postobj->set('description', $body);
        $postobj->set('tags', $tags);
        $postobj->set('published', !$isdraft);
        $postobj->set('allowcomments', ($allowcomments ? 1 : 0));
        $postobj->set('parent', $blogid);
        $postobj->set('owner', $USER->id);
        $postobj->commit();
        $blogpost = $postobj->get('id');

        $returndata = array();
        $returndata['blogpost'] = $blogpost;

        // Finally attach the files to the blog post once uploaded and validated
        if ($fileattachments) {
            $returndata['files'] = array();
            foreach ($fileattachments as $k => $v) {
                // Store and validate the file
                $fileid = self::handle_file_upload(
                    'fileattachments',
                    $k,
                    $blog->title,
                    $v,
                    null,
                    $tags
                );
                // Attach it to the blogpost
                $postobj->attach($fileid);
                // Return a list of the ids of the file artefacts
                $returndata['files'][] = $fileid;
            }
        }

        return $returndata;
    }


    /**
     * Internal function to upload a file using the same logic whether
     * it's a standalone file or an attachment to a blog post.
     *
     * This function can deal with files that are in an array param,
     * but it will only do one of them at a time.
     *
     * @param string $inputname Name of the parameter the file is in
     * @param int $inputindex NULL if there's just one file; index of particular file if it's an array
     * @param string $foldername Folder to put the files in (or create if it doesn't exist yet.)
     * @param string $title
     * @param string $description
     * @param array $tags
     * @return ID of newly created file
     * @throws WebserviceInvalidParameterException
     */
    private static function handle_file_upload($inputname, $inputindex = null, $foldername = null, $title = null, $description = null, $tags = array()) {
        global $USER;
        if (!$_FILES[$inputname]) {
            throw new WebserviceInvalidParameterException('No uploaded files found in request');
        }
        safe_require('artefact', 'file');

        $data = new stdClass();
        $data->owner = $USER->get('id'); // id of owner

        // See if a folder by this name already exists.
        // Create a folder by this name if it doesn't exist yet.
        $artefact = ArtefactTypeFolder::get_folder_by_name($foldername, null, $data->owner);
        if ($artefact) {
            $data->parent = $artefact->id;
            if ($data->parent == 0) {
                $data->parent = null;
            }
        }
        else {
            $fd = (object) array(
                'owner' => $data->owner,
                'title' => $foldername,
                'parent' => null,
            );
            $f = new ArtefactTypeFolder(0, $fd);
            $f->commit();
            $data->parent = $f->get('id');
        }

        if (!$title) {
            if ($inputindex) {
                $rawname = $_FILES[$inputname]['name'][$inputindex];
            }
            else {
                $rawname = $_FILES[$inputname]['name'];
            }
            $title = basename($rawname);
        }
        $data->title = ArtefactTypeFileBase::get_new_file_title($title, $data->parent, $data->owner);
        if ($description) {
            $data->description = $description;
        }
        if ($tags) {
            $data->tags = $tags;
        }

        // This will throw a QuotaExceededException or UploadExceptoin if there's
        // a problem.
        $artefact_id = ArtefactTypeFile::save_uploaded_file($inputname, $data, $inputindex);

        return $artefact_id;
    }
}
