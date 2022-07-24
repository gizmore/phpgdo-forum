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

final class Unsubscribe extends Method
{
    public function getTitleLangKey() { return 'btn_unsubscribe'; }
    
    public function execute()
    {
        $user = GDO_User::current();
        $uid = $user->getID();
        
        if ($boardId = Common::getRequestInt('board'))
        {
            if ($boardId === 1)
            {
                return $this->error('err_please_use_subscribe_all');
            }
            $board = GDO_ForumBoard::findById($boardId);
            GDO_ForumBoardSubscribe::table()->deleteWhere("subscribe_user=$uid AND subscribe_board=$boardId");
            $user->tempUnset('gdo_forum_board_subsciptions');
            $user->recache();
            $href = href('Forum', 'Boards', '&board='.$board->getParent()->getID());
        }

        elseif ($threadId = Common::getRequestInt('thread'))
        {
            $thread = GDO_ForumThread::findById($threadId);
            GDO_ForumThreadSubscribe::table()->deleteWhere("subscribe_user=$uid AND subscribe_thread=$threadId");
            $user->tempUnset('gdo_forum_thread_subsciptions');
            $user->recache();
            $href = href('Forum', 'Boards', '&boardid='.$thread->getBoard()->getID());
        }
        
        $href = Website::hrefBack($href);
        return Website::redirectMessage('msg_unsubscribed', null, $href);
    }

}
