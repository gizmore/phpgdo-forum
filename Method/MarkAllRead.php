<?php
namespace GDO\Forum\Method;

use GDO\Forum\GDO_ForumUnread;
use GDO\Core\Website;
use GDO\User\GDO_User;
use GDO\Core\Method;

/**
 * Mark all posts as read.
 * @author gizmore
 */
final class MarkAllRead extends Method
{
    public function execute()
    {
        $user = GDO_User::current();
        GDO_ForumUnread::table()->deleteWhere("unread_user={$user->getID()}");
        return $this->redirectMessage('msg_forum_marked_all_unread', null, href('Forum', 'Boards'));
    }

}
