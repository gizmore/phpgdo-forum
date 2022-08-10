<?php
namespace GDO\Forum\Method;

use GDO\Core\Website;
use GDO\DB\Query;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\Forum\GDO_ForumPost;
use GDO\Table\MethodQueryCards;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDT_ForumPost;
use GDO\Table\GDT_Table;
use GDO\User\GDO_User;

/**
 * Display a forum thread.
 * @author gizmore
 */
final class Thread extends MethodQueryCards
{
    public function isPaginated() { return true; }
    public function isOrdered() : bool { return false; }
    public function isSearched() { return false; }
    
    public function getDefaultOrder() : ?string { return 'IFNULL(post_edited, post_created)'; }
    
    public function getDefaultIPP() : int { return 10; }
    
    public function beforeExecute() : void
    {
        Module_Forum::instance()->renderTabs();
    }
    
    public function gdoParameters() : array
    {
        return [
            GDT_ForumPost::make('post')->notNull(),
        ];
    }
    
    /**
     * @return GDO_ForumThread
     */
    public function getThread()
    {
        return $this->getPost()->getThread();
    }
    
    /**
     * @return GDO_ForumPost
     */
    public function getPost()
    {
        return $this->gdoParameterValue('post');
    }
    
    /**
     * @return GDO_ForumBoard
     */
    public function getBoard()
    {
        return $this->getThread()->getBoard();   
    }
    
    public function hasPermission(GDO_User $user) : bool
    {
    	$this->init();
        return $this->getThread()->canView($user);
    }
    public function gdoTable()
    {
        return GDO_ForumPost::table();
    }
    
    public function getQuery() : Query
    {
        $thread = $this->getThread();
        $uid = GDO_User::current()->getID();
        return
            parent::getQuery()->
            join("LEFT JOIN gdo_forumpostlikes ON like_user={$uid} AND like_object=post_id")->
            where("post_thread={$thread->getID()}");
    }
    
    public function setupTitle(GDT_Table $table)
    {
        $thread = $this->getThread();
        Website::setTitle($thread->getTitle());
        $table->title(t('list_title_thread_posts', 
            [$thread->displayTitle(), $table->countItems()]));
    }

    /**
     * Set board correctly on init.
     * Go to default page for a post.
     */
    public function onInit()
    {
    	parent::onInit();
        $_REQUEST['board'] = $this->getThread()->getBoardID();
    }
    
    protected function beforeCalculateTable(GDT_Table $table)
    {
        $o = $this->table->headers->name;
        if (!isset($_REQUEST[$o]['page']))
        {
            $defaultPage = $this->table->getPageFor($this->getPost());
            $_REQUEST[$o]['page'] = "$defaultPage";
            $this->table->pagemenu->page($defaultPage);
        }
    }
    
    public function getMethodTitle() : string
    {
        return $this->getThread()->getTitle();
    }
    
}
