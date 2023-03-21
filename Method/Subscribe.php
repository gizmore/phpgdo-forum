<?php
namespace GDO\Forum\Method;

use GDO\Core\GDT_Object;
use GDO\Core\Method;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumBoardSubscribe;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\GDO_ForumThreadSubscribe;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;

/**
 * Subscribe boards or thread
 * Guest user is not allowed, because he does not have an email.
 *
 * @author gizmore
 */
final class Subscribe extends Method
{

	public function getMethodTitle(): string
	{
		return t('btn_subscribe');
	}

	public function isUserRequired(): bool { return true; }

	public function isGuestAllowed(): bool { return false; }

	public function gdoParameters(): array
	{
		return [
			GDT_Object::make('board')->table(GDO_ForumBoard::table()),
			GDT_Object::make('thread')->table(GDO_ForumThread::table()),
		];
	}

	public function execute()
	{
		$user = GDO_User::current();

		if ($boardId = $this->gdoParameterVar('board'))
		{
			if ($boardId == Module_Forum::instance()->cfgRootID())
			{
				return $this->error('err_please_use_subscribe_all');
			}
			GDO_ForumBoardSubscribe::blank([
				'subscribe_user' => $user->getID(),
				'subscribe_board' => $boardId,
			])->replace();
			$user->tempUnset('gdo_forum_board_subsciptions');
			$user->recache();
			return $this->redirectMessage('msg_subscribed');
		}

		elseif ($threadId = $this->gdoParameterVar('thread'))
		{
			GDO_ForumThreadSubscribe::blank([
				'subscribe_user' => $user->getID(),
				'subscribe_thread' => $threadId,
			])->replace();
			$user->tempUnset('gdo_forum_thread_subsciptions');
			$user->recache();
			return $this->redirectMessage('msg_subscribed');
		}
	}

}
