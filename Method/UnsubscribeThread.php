<?php
namespace GDO\Forum\Method;

use GDO\Core\GDT;
use GDO\Core\Method;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\GDO_ForumThreadSubscribe;
use GDO\Forum\GDT_ForumThread;
use GDO\UI\GDT_Redirect;
use GDO\User\GDO_User;

/**
 * Unsubscribe from a thread.
 *
 * @version 7.0.1
 * @author gizmore
 */
final class UnsubscribeThread extends Method
{

	public function getMethodTitle(): string
	{
		return t('btn_unsubscribe');
	}

	public function gdoParameters(): array
	{
		return [
			GDT_ForumThread::make('thread')->notNull(),
		];
	}

	public function execute(): GDT
	{
		$user = GDO_User::current();
		$thread = $this->getThread();
		GDO_ForumThreadSubscribe::table()->deleteWhere(
			"subscribe_user={$user->getID()} AND subscribe_thread={$thread->getID()}");
		$user->tempUnset('gdo_forum_thread_subsciptions');
		$user->recache();
		$href = href('Forum', 'Boards', '&boardid=' . $thread->getBoard()->getID());
		$href = GDT_Redirect::hrefBack($href);
		return $this->redirectMessage('msg_unsubscribed', null, $href);
	}

	public function getThread(): GDO_ForumThread
	{
		return $this->gdoParameterValue('thread');
	}

}
