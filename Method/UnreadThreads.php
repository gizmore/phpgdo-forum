<?php
namespace GDO\Forum\Method;

use GDO\Core\GDO;
use GDO\Core\GDT;
use GDO\Core\GDT_Response;
use GDO\DB\Query;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\GDO_ForumUnread;
use GDO\Forum\Module_Forum;
use GDO\Table\MethodQueryList;
use GDO\UI\GDT_Button;
use GDO\UI\GDT_Container;
use GDO\User\GDO_User;

/**
 * List all new threads for a user.
 *
 * @version 6.10
 * @since 6.10
 * @author gizmore
 */
final class UnreadThreads extends MethodQueryList
{

	public function isOrdered(): bool { return false; }

	public function isSearched(): bool { return false; }

	public function onRenderTabs(): void
	{
		Module_Forum::instance()->renderTabs();
	}

	public function gdoTable(): GDO
	{
		return GDO_ForumThread::table();
	}

	public function execute(): GDT
	{
		$cont = GDT_Container::make();
		$cont->addField(GDT_Button::make()->label('mark_all_read')->icon('check')->href(href('Forum', 'MarkAllRead')));
		$response = GDT_Response::makeWith($cont);
		return $response->addField(parent::execute());
	}

	public function getCountQuery(): Query
	{
		return $this->getQuery()->selectOnly('COUNT(DISTINCT(thread_id))');
	}

	public function getQuery(): Query
	{
		$user = GDO_User::current();
		return GDO_ForumUnread::table()->
		select('DISTINCT(gdo_forumthread.thread_id), gdo_forumthread.*')->
		where("unread_user={$user->getID()}")->
		joinObject('unread_post')->
		join('JOIN gdo_forumthread ON post_thread=thread_id')->
		fetchTable(GDO_ForumThread::table());
	}

	public function getTableTitle(): string
	{
		$user = GDO_User::current();
//         $threadcount = GDO_ForumUnread::table()->countUnread($user)Thread::table()->coun$this->thtable->pagemenu->numItems;
		$postcount = GDO_ForumUnread::table()->countUnread($user);
		return t('list_forum_unreadthreads', [t('unknown'), $postcount]);
	}

}
