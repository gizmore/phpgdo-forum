<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Core\Website;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumBoardSubscribe;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\GDO_ForumThreadSubscribe;
use GDO\User\GDO_User;
use GDO\Util\Common;
use GDO\Forum\Module_Forum;

/**
 * Subscribe boards or thread
 * Guest user is not allowed, because he does not have an email.
 * @author gizmore
 */
final class Subscribe extends Method
{
	public function isUserRequired() : bool { return true; }
	
    public function execute()
    {
        $user = GDO_User::current();
        
        if ($boardId = Common::getRequestString('board'))
        {
            if ($boardId == Module_Forum::instance()->cfgRootID())
            {
                return $this->error('err_please_use_subscribe_all');
            }
            $board = GDO_ForumBoard::findById($boardId);
            GDO_ForumBoardSubscribe::blank(array(
                'subscribe_user' => $user->getID(),
                'subscribe_board' => $boardId,
            ))->replace();
            $user->tempUnset('gdo_forum_board_subsciptions');
            $user->recache();
            $href = href('Forum', 'Boards', '&board='.$board->getParent()->getID());
            return Website::redirectMessage('msg_subscribed', null, $href);
        }
        
        elseif ($threadId = Common::getRequestString('thread'))
        {
            $thread = GDO_ForumThread::findById($threadId);
            GDO_ForumThreadSubscribe::blank(array(
                'subscribe_user' => $user->getID(),
                'subscribe_thread' => $threadId,
            ))->replace();
            $user->tempUnset('gdo_forum_thread_subsciptions');
            $user->recache();
            $href = Website::hrefBack(href('Forum', 'Boards', '&board='.$thread->getBoard()->getID()));
            return Website::redirectMessage('msg_subscribed', null, $href);
        }
    }

}
