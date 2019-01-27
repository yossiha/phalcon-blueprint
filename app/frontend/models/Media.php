<?php
namespace Platform\Frontend\Models;

define("MEDIA_SUCCESS", 0);
define("MEDIA_ERROR_NOT_MEDIAFILE", 1);
define("MEDIA_ERROR_SIZE", 2);
define("MEDIA_ERROR_TYPE", 3);
define("MEDIA_NOT_UPLOADED_FILE", 4);
define("MEDIA_IMAGE_MAX_SIZE", 5);
define("MEDIA_PDF_MAX_SIZE", 6);
define("MEDIA_ERROR_FILESYSTEM", 7);
define("MEDIA_ERROR_VALIDATION", 8);
define("MEDIA_ERROR_DBSAVE", 9);

/**
 * Class Media
 * @package Platform\Frontend\Models
 * @requirements install extension=php_fileinfo.dll
 */

class Media extends \Phalcon\Mvc\Model {
    /**
     * Validations and business logic
     */

    /**
     * @param $sanitize either 'image' or 'pdf' depends on the file
     * @param $file Phalcon\Http\Request\File
     */
    public function saveFile($file, $sanitize=null) {
        if($file->getError()) //http://php.net/manual/en/features.file-upload.errors.php
            return false;

        if(!$file->isUploadedFile()) {
            return MEDIA_NOT_UPLOADED_FILE;
        }

        $type = $file->getRealType();
        $ext = mb_strtolower($file->getExtension());
        if(($ext=='jpg' || $ext=='jpeg' || $ext=='png' || $ext == 'gif')
                && ($sanitize==null || $sanitize=='image')) {
            if($type == 'image/png') //check for the server integrity check
                $ext = 'png';
            elseif($type == 'image/jpeg')
                $ext = 'jpg';
            elseif($type == 'image/gif')
                $ext = 'gif';
            else
                return MEDIA_ERROR_NOT_MEDIAFILE;
            if($file->getSize() > 3500000)
                return MEDIA_IMAGE_MAX_SIZE;
        }
        elseif(($ext=='pdf') && ($sanitize==null || $sanitize=='pdf')) {
            if($type == 'application/pdf')
                $ext = 'pdf';
            else return MEDIA_ERROR_NOT_MEDIAFILE;

            if($file->getSize() > 12000000)
                return MEDIA_PDF_MAX_SIZE;
        }
        else //Check for the extension in the user's computer
            return MEDIA_ERROR_NOT_MEDIAFILE;

        $media_dir = $this->_di->getConfig()->application->privateMediaDir;
        $media_salt = $this->_di->getConfig()->application->mediaSalt;
        $year = mb_strcut(sha1(date('Y') .$media_salt), 0, 10);
        $month = mb_strcut(sha1(date('m') .$media_salt), 0, 10);

        if(!file_exists ($media_dir .$year))
            if(!mkdir($media_dir .$year))
                return MEDIA_ERROR_FILESYSTEM;

        if(!file_exists ($media_dir .$year .'/' .$month))
            if(!mkdir($media_dir .$year .'/' .$month))
                return MEDIA_ERROR_FILESYSTEM;

        $media_path = $media_dir .$year .'/' .$month;
        $new_filename = mb_strcut(sha1(bin2hex(openssl_random_pseudo_bytes(10))), 0, 12) .'.' .$ext;

        if(!$file->moveTo($media_path  .'/' .$new_filename))
            return MEDIA_ERROR_FILESYSTEM;

        $this->file_ext = $ext;
        $this->directory = $year .'/' .$month;
        $this->filename = $new_filename;

        if($this->validation()) {
            if ($this->save()) {
                return MEDIA_SUCCESS;
            }
            else {
                foreach ($this->getMessages() as $message) {
                    echo $message, "\n";
                }
                unlink(\Phalcon\DI::getDefault()['config']['application']['privateMediaDir'] .'/' .$media_path  .'/' .$new_filename);
                return MEDIA_ERROR_DBSAVE;
            }
        }
        else {
            unlink(\Phalcon\DI::getDefault()['config']['application']['privateMediaDir'] .'/' .$media_path  .'/' .$new_filename);
            return MEDIA_ERROR_VALIDATION;
        }
    }

    /**
     * usage example:
     *  $this->object = 'Platform\Frontend\Models\\' .$this->model;
     *  $this->object = new $this->object();
     *  $this->object->deleteMedia(5);
     */

    public function deleteMedia($id=null) {
        if(empty($id) && empty($this->id))
            return false;

        if(empty($this->id))
            if(!$this->findFirstByid($id))
                return false;

        if($this->delete()) {
            unlink(\Phalcon\DI::getDefault()['config']['application']['privateMediaDir'] .'/' .$this->directory . '/' . $this->filename);
            return true;
        }
        else return false;
    }

    public function validation() {
        /*        $validator = new \Phalcon\Validation();
                return $this->validate($validator);*/
        return true;
    }
}
