<?php
namespace Platform\Frontend\Models;

class ProfilesMedia extends \Phalcon\Mvc\Model {

    /**
     * @param null $object
     *      This object is of type media, which has:
     *          id=ProfilesMedia.id,
     *          media_id=ProfilesMedia.media_id,
     *          user_id=ProfilesMedia.user_id
     * @return bool
     */
    static public function deleteMediaById($id=null) {
        if(empty($id))
            return false;

        $id = (int)$id;
        if(!$object = \Platform\Frontend\Models\ProfilesMedia::findFirst("id=$id"))
            return false;

        if(!$media = \Platform\Frontend\Models\Media::findFirst('id=' .$object->media_id))
            return false;

        if(!$media->deleteMedia())
            return false;

        if(!$object->delete())
            return false;

        return true;
    }

    /*
     * make more efficient, check only for the right media files..
     */
    static public function getUserProfileMedia($id=null) {
        $id = (int)$id;
        if($id==null)
            return false;
        $query = new \Phalcon\Mvc\Model\Query("SELECT * FROM \Platform\Frontend\Models\ProfilesMedia as ProfilesMedia
JOIN \Platform\Frontend\Models\Users as Users JOIN \Platform\Frontend\Models\Media as Media WHERE ProfilesMedia.user_id=$id
AND ProfilesMedia.user_id=Users.id
AND ProfilesMedia.media_id=\Media.id",
            \Phalcon\DI::getDefault() );

        $profiles_media = $query->execute();

        $media_result['profile']=null;
        $media_result['usermedia']['counter']=0;

        foreach($profiles_media as $media) {
            if($media['platform\Frontend\Models\ProfilesMedia']->type=='profile') {
                $media_result[$media['platform\Frontend\Models\ProfilesMedia']->type]['id']=$media['platform\Frontend\Models\ProfilesMedia']->id;
                $media_result[$media['platform\Frontend\Models\ProfilesMedia']->type]['media_id']=$media['platform\Frontend\Models\ProfilesMedia']->media_id;
                $media_result[$media['platform\Frontend\Models\ProfilesMedia']->type]['user_id']=$media['platform\Frontend\Models\ProfilesMedia']->user_id;
                $media_result[$media['platform\Frontend\Models\ProfilesMedia']->type]['type']=$media['platform\Frontend\Models\ProfilesMedia']->type;
                $media_result[$media['platform\Frontend\Models\ProfilesMedia']->type]['name']=$media['platform\Frontend\Models\Media']->name;
                $media_result[$media['platform\Frontend\Models\ProfilesMedia']->type]['file_ext']=$media['platform\Frontend\Models\Media']->file_ext;
                $media_result[$media['platform\Frontend\Models\ProfilesMedia']->type]['filename']=$media['platform\Frontend\Models\Media']->filename;
                $media_result[$media['platform\Frontend\Models\ProfilesMedia']->type]['directory']=$media['platform\Frontend\Models\Media']->directory;
            }
            elseif($media['platform\Frontend\Models\ProfilesMedia']->type=='usermedia') {
                $media_result['usermedia'][$media_result['usermedia']['counter']]['id']=$media['platform\Frontend\Models\ProfilesMedia']->id;
                $media_result['usermedia'][$media_result['usermedia']['counter']]['media_id']=$media['platform\Frontend\Models\ProfilesMedia']->media_id;
                $media_result['usermedia'][$media_result['usermedia']['counter']]['user_id']=$media['platform\Frontend\Models\ProfilesMedia']->user_id;
                $media_result['usermedia'][$media_result['usermedia']['counter']]['type']=$media['platform\Frontend\Models\ProfilesMedia']->type;
                $media_result['usermedia'][$media_result['usermedia']['counter']]['name']=$media['platform\Frontend\Models\Media']->name;
                $media_result['usermedia'][$media_result['usermedia']['counter']]['file_ext']=$media['platform\Frontend\Models\Media']->file_ext;
                $media_result['usermedia'][$media_result['usermedia']['counter']]['filename']=$media['platform\Frontend\Models\Media']->filename;
                $media_result['usermedia'][$media_result['usermedia']['counter']]['directory']=$media['platform\Frontend\Models\Media']->directory;
                $media_result['usermedia']['counter']++;
            }
        }
        return $media_result;
    }
}
