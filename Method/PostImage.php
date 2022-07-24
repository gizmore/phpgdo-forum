<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Forum\GDT_ForumPost;
use GDO\User\GDO_User;
use GDO\Forum\GDO_ForumPost;
use GDO\File\Method\GetFile;

final class PostImage extends Method
{
    public function saveLastUrl() : bool { return false; }
    
    public function gdoParameters() : array
    {
        return array(
            GDT_ForumPost::make('id')->notNull(),
        );
    }
    
    /**
     * @return GDO_ForumPost
     */
    public function getPost()
    {
        return $this->gdoParameterValue('id');
    }
    
    public function hasPermission(GDO_User $user) : bool
    {
        if ($post = $this->getPost())
        {
            return $post->canView($user);
        }
        return false;
    }
    
    public function execute()
    {
        $post = $this->getPost();
        $attachment = $post->getAttachment();
        if (!$attachment->isImageType())
        {
            return $this->error('err_no_image');
        }
        return GetFile::make()->executeWithId($attachment->getID());
    }
    
}
