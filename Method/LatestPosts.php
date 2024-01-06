<?php
declare(strict_types=1);
namespace GDO\Forum\Method;

use GDO\Core\GDO;
use GDO\DB\Query;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\Table\GDT_Table;
use GDO\Table\MethodQueryList;
use GDO\User\GDO_User;

/**
 * Display a list of latest threads.
 *
 * @version 7.0.3
 * @since 3.0.0
 * @author gizmore
 */
final class LatestPosts extends MethodQueryList
{

	public function isPaginated(): bool { return false; }

	public function isSearched(): bool { return false; }

	public function isOrdered(): bool { return false; }

	public function gdoTable(): GDO { return GDO_ForumThread::table(); }

	public function numLatestThreads(): int
	{
		return Module_Forum::instance()->cfgNumLatestThreads();
	}

	protected function setupTitle(GDT_Table $table): void
	{
		$table->title('forum_list_latest_threads');
	}

	public function getQuery(): Query
	{
		$user = GDO_User::current();
		return
			$this->gdoTable()->select()->
                where("thread_level <= {$user->getLevel()}")->
                joinObject('thread_board')->
                join("LEFT JOIN gdo_userpermission ON perm_user_id={$user->getID()} AND perm_perm_id=board_permission")->
                order('thread_lastposted DESC')->
                where('board_permission IS NULL OR perm_user_id IS NOT NULL')->
                limit($this->numLatestThreads());
	}

}
