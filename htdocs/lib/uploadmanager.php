<?php

class upload_manager {

   /**
    * Array to hold local copy of stuff in $_FILES
    * @var array $file
    */
    var $file;

   /**
    * Name of the file input
    * @var string $inputname 
    */
    var $inputname;

   /**
    * Whether to try to rename files when they already exist
    * @var bool $handlecollisions
    */
    var $handlecollisions;

    /**
     * Constructor.
     *
     * @param string $inputname Name in $_FILES.
     */
    function upload_manager($inputname, $handlecollisions=false) {
        $this->inputname = $inputname;
        $this->handlecollisions = $handlecollisions;
    }

    /** 
     * Gets file information out of $_FILES and stores it locally in $files.
     * Checks file against max upload file size.
     * Scans file for viruses.
     * @return boolean
     */
    function preprocess_file() {

        $name = $this->inputname;
        if (!isset($_FILES[$name])) {
            return false;
        }
        $file = $_FILES[$name];

        if (!is_uploaded_file($file['tmp_name']) || $file['size'] == 0) {
            return false;
        }
        $maxsize = get_config('maxuploadsize');
        if ($maxsize && $file['size'] > $maxsize) {
            return false;
        }

        if (get_config('viruschecking') && !clam_scan_file($file)) {
            return false;
        }

        $this->file = $file;
        return true; 
    }

    /** 
     * Moves the file to the destination directory.
     *
     * @uses $CFG
     * @param string $destination The destination directory.
     * @param string $newname The filename
     * @return boolean status;
     */
    function save_file($destination, $newname) {

        if (!isset($this->file)) {
            return false;
        }

        $dataroot = get_config('dataroot');

        if (!(strpos($destination, $dataroot) === false)) {
            // take it out for giving to make_upload_directory
            $destination = substr($destination, strlen($dataroot)+1);
        }

        if ($destination{strlen($destination)-1} == '/') { // strip off a trailing / if we have one
            $destination = substr($destination, 0, -1);
        }

        if (empty($destination)) {
            $destination = $dataroot;
        } else {
            $destination = $dataroot . '/' . $destination;
        }

        if (!check_dir_exists($destination, true, true)) {
            throw new Exception('Unable to create upload directory');
        }

        if (file_exists($destination . '/' . $newname) && $this->handlecollisions) {
            $newname = $this->rename_duplicate_file($destination, $newname);
        }

        if (move_uploaded_file($this->file['tmp_name'], $destination . '/' . $newname)) {
            chmod($destination . '/' . $newname, 0700);
            return true;
        }
        return false;
    }
    

    /**
     * Wrapper function that calls {@link preprocess_files()} and {@link viruscheck_files()} and then {@link save_files()}
     * Modules that require the insert id in the filepath should not use this and call these functions seperately in the required order.
     * @parameter string $destination Where to save the uploaded files to.
     * @return boolean
     */ 
    function process_file_upload($destination, $newname) {
        if ($this->preprocess_file()) {
            return $this->save_file($destination, $newname);
        }
        return false;
    }

    /**
     * Handles filename collisions - if the desired filename exists it will rename it according to the pattern in $format
     * @param string $destination Destination directory (to check existing files against)
     * @param object $file Passed in by reference. The current file from $files we're processing.
     * @param string $format The printf style format to rename the file to (defaults to filename_number.extn)
     * @return string The new filename.
     */
    function rename_duplicate_file($destination, $filename, $format='%s_%d.%s') {
        // If there's no dot or more than one dot we get yucky stuff like 'foo_1.', 'foo_1.bar.baz'
        $bits = explode('.', $filename);  
        // check for collisions and append a nice numberydoo.
        for ($i = 1; true; $i++) {
            $try = sprintf($format, $bits[0], $i, $bits[1]);
            if (!file_exists($destination . '/' . $try)) {
                return $try;
            }
        }
    }

}

/**************************************************************************************
THESE FUNCTIONS ARE OUTSIDE THE CLASS BECAUSE THEY NEED TO BE CALLED FROM OTHER PLACES.
FOR EXAMPLE CLAM_HANDLE_INFECTED_FILE AND CLAM_REPLACE_INFECTED_FILE USED FROM CRON
UPLOAD_PRINT_FORM_FRAGMENT DOESN'T REALLY BELONG IN THE CLASS BUT CERTAINLY IN THIS FILE
***************************************************************************************/

function clam_scan_file(&$file) {
    return true;
}


?>
