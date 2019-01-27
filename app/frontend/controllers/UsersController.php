<?php
namespace Platform\Frontend\Controllers;

class UsersController extends ControllerBase {
    protected $object = NULL;
    private $model = 'Profiles';

    public function profileAction($id=null) {
        $id = $this->session->auth['id'];

        $this->object = \Platform\Frontend\Models\Profiles::findFirst($id);
        if(empty($this->object)) {
            $this->flash->error("User not found");
            return $this->response->redirect($this->config->routes->dashboard);
        }

        $this->view->object_profile = $this->object;
        $this->view->privateMediaUri = $this->config['application']['privateMediaUri'];
    }

    public function typeAction() {
        $id = $this->session->auth['id'];
        $profile = \Platform\Frontend\Models\Profiles::findFirst($id);

        if(empty($profile)) {
            return $this->response->redirect($this->config['dashboard']['dashboard']);
        }

        if($profile->type=='on')
            $profile->type='off';
        else $profile->type='on';

        if(!$profile->save()) //add checkups here.. what happens if user successed? failed?
            return $this->response->redirect($this->config['dashboard']['dashboard']);
        return $this->response->redirect('/users/profile/' .$id);
    }

    private function saveRecord($action='save') {
        $pre_validation=true;
        $extra_validation=null;

        $this->object->first_name = $this->request->getPost("first_name", null, null, true);
        $this->object->last_name = $this->request->getPost("last_name", null, null, true);

        if($this->object->validation() && $pre_validation) {
            if ($this->object->$action()) {
                if($action=='create')
                    $this->flash->success('Account created successfully');
                else $this->flash->success('Information saved');
                return true;
            }
            else
                $this->flash->error("Error saving, please contact support");
        }
        else
            $this->flash->error("There is an error with your form");

        $this->setMessages($this->object, $extra_validation);
        return false;
    }

    public function bProfileAction() {
        $obj = '\Platform\Frontend\Models\\' .$this->model;
        $id = $this->session->auth['id'];

        if($this->object = $obj::findFirstByid($id)) {
            $this->view->object = $this->object;
            $this->view->action = 'update';
        }
        else {
            $this->object = new $obj();
            $this->object->id = $this->session->auth['id'];
            $this->view->action = 'create';
        }

        if($this->request->isPost()) {
            if($this->saveRecord($this->view->action)) { //success, from here we update or redirect
                $this->view->object = $this->object;
                $this->view->action = 'update';
            }
            else { //Validation error, or DB error -> show editing screen again!
                $this->view->object = $this->object;
            }
        }
        elseif(!$this->request->isPost() && $this->view->action=='create') { //empty object passed
            $this->view->object = $this->object;
        }
        else { //update varibles for the form
            //$this->object->adm_month_year = strtotime($this->object->adm_month_year);
            //$this->view->object->adm_month = date("m", $this->object->adm_month_year);
            //$this->view->object->adm_year = date("Y", $this->object->adm_month_year);
            $this->view->object = $this->object;
        }
    }

    public function dProfileAction($id) {
        $obj = '\Platform\Frontend\Models\\' .$this->model;
        $id = $this->session->auth['id'];

        if(empty($id) ) {
            $this->view->disable();
            $this->flash->error("Empty id"); 
            return $this->response->redirect('/');
        }

        if(!$this->object = $obj::findFirstByid($id)) {
            $this->view->disable();
            $this->flash->error("Record not found");
            return $this->response->redirect('/');
        }

        if (!$this->object->delete()) {
            $this->view->disable();
            $this->flash->error('Error, please contact support!');
            return $this->response->redirect('/');
        }

        $this->flash->success("Record successfully deleted");
        //return $this->response->redirect('/');
    }

    public function cMediaAction() {
        $previous_id = null;

        //make sure the user has a profile id sanitation
        $profile = \Platform\Frontend\Models\Profiles::findFirst($this->session->auth['id']);
        if(empty($profile)) { //NO PENDING!
            $this->flash->warning("Please fill your profile");
            return $this->response->redirect($this->redirect['profiles_basic']);
        }
        $user_media = \Platform\Frontend\Models\ProfilesMedia::getUserProfileMedia($this->session->auth['id']);

        if($this->request->isPost()) { //ispost?
            $type = $this->request->getPost("type", null, null, true);

            if($type!='profile' && $type!='usermedia') {
                $this->flash->warning("Incorrect type");
            }
            elseif(!$this->request->hasFiles()) {
                $this->flash->warning("No files chosen");
            }
            else {
                foreach ($this->request->getUploadedFiles() as $req_file)
                    $file = $req_file;

                $media = new \Platform\Frontend\Models\Media();
                $media->user_id = $this->auth['id'];
                $media->name = $this->request->getPost("title", null, null, true);

                if($type=='profile' && isset($user_media['profile']) && !empty($user_media['profile']))
                    $previous_id = $user_media['profile']['id'];

                $mediaRes = $media->saveFile($file, 'image');

                if($mediaRes === MEDIA_SUCCESS) {
                    $profiles_media = new \Platform\Frontend\Models\ProfilesMedia();

                    $profiles_media->user_id = $this->session->auth['id'];
                    $profiles_media->media_id = $media->id;
                    $profiles_media->type = $type;

                    if($profiles_media->save()) {
                        \Platform\Frontend\Models\ProfilesMedia::deleteMediaById($previous_id); //if it's a profile picture..!
                    }
                    else { //Validation error, or DB error -> show editing screen again!
                        $this->flash->error("Error saving media file, please contact support");
                        $media->deleteMedia();
                    }
                }
                else {
                    switch($mediaRes) {
                        case MEDIA_ERROR_NOT_MEDIAFILE: {
                            if($type=='profile')
                                $this->flash->error("Invalid image, please try again");
                            else $this->flash->error("Invalid PDF file, please try again");
                            break;
                        }
                        case MEDIA_ERROR_SIZE: {
                            $this->flash->error("Size must be a 3.5 MB for an image, or 12 MB for a PDF");
                            break;
                        }
                        case MEDIA_ERROR_TYPE: {
                            $this->flash->error("File is not a media file of type: JPEG, PNG, GIF or PDF");
                            break;
                        }
                        case MEDIA_NOT_UPLOADED_FILE: {
                            $this->flash->error("No file uploaded");
                            break;
                        }
                        case MEDIA_IMAGE_MAX_SIZE: {
                            $this->flash->error("Max filesize for an image is 12 MB");
                            break;
                        }
                        case MEDIA_PDF_MAX_SIZE: {
                            $this->flash->error("Max filesize for a PDF is 3.5 MB");
                            break;
                        }
                        case MEDIA_ERROR_FILESYSTEM: {
                            $this->flash->error("Server error, please contact support (7)");
                            break;
                        }
                        case MEDIA_ERROR_VALIDATION: {
                            $this->flash->error("Server error, please contact support (8)");
                            break;
                        }
                        case MEDIA_ERROR_DBSAVE: {
                            $this->flash->error("Server error, please contact support (9)");
                            break;
                        }
                    }
                }
            } //incorrect type end
        }
        elseif(!$this->request->isPost() && $this->view->action=='create') {//empty object passed
           //can be used to load a profile picture
        }
        else { //load an object
            
        }

        //no optimization here..
        $user_media = \Platform\Frontend\Models\ProfilesMedia::getUserProfileMedia($this->session->auth['id']);
        $this->view->user_media = $user_media;
    }

    /**
     * Deletes an object
     * Not in the ACL
     *
     * @param string $id
     */
    public function deleteAction($id) {
        $obj = $this->model;

        if(empty($id) || !$this->object = $obj::findFirstByid($id)) {
            $this->view->disable();
            $this->flash->error("Record not found");
            return $this->response->redirect('/');
        }

        if (!$this->object->delete()) {
            $this->view->disable();
            $this->flash->error('Error, please contact support!');
            return $this->response->redirect('/');
        }

        $this->flash->success("Record successfully deleted");
        return $this->response->redirect('/');
    }
}
