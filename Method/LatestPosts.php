<?php
namespace GDO\Forum\Method;

use GDO\Table\MethodQueryList;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\Table\GDT_Table;
use GDO\User\GDO_User;

/**
 * Display a list of latest threads.
 * @author gizmore
 * @version 6.10.4
 * @since 3.0.0
 */
final class LatestPosts extends MethodQueryList
{
    public function isPaginated() { return false; }
    public function isSearched() { return false; }
    public function isOrdered() { return false; }

	public function gdoTable() { return GDO_ForumThread::table(); }
	
	public function numLatestThreads()
	{
	    return Module_Forum::instance()->cfgNumLatestThreads();
	}
	
	protected function setupTitle(GDT_Table $table)
	{
	    $table->title('forum_list_latest_threads');
	}
	
	public function getQuery()
	{
	    $user = GDO_User::current();
	    return
	       $this->gdoTable()->select()->
	           where("thread_level <= {$user->getLevel()}")->
	           joinObject('thread_board')->
	           join("JOIN gdo_userpermission ON perm_user_id={$user->getID()} AND perm_perm_id=board_permission")->
    	       order('thread_lastposted DESC')->
    	       limit($this->numLatestThreads());
	}
	
}
