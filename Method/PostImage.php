<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Forum\GDT_ForumPost;
use GDO\User\GDO_User;
use GDO\Forum\GDO_ForumPost;
use GDO\File\Method\GetFile;

/**
 * Downlad an image for a post.
 * 
 * @author gizmore
 */
final class PostImage extends Method
{
    public function isSavingLastUrl() : bool { return false; }
    
    public function getMethodTitle(): string
    {
    	return t('image');
    }
    
    public function gdoParameters() : array
    {
        return [
            GDT_ForumPost::make('id')->notNull(),
        ];
    }
    
    public function getPost() : GDO_ForumPost
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
        if (!($attachment = $post->getAttachment()))
        {
        	return $this->error('err_post_no_attachment');
        }
        if (!$attachment->isImageType())
        {
            return $this->error('err_no_image');
        }
        return GetFile::make()->executeWithId($attachment->getID());
    }
    
}
