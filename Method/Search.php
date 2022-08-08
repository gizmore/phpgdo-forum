<?php
namespace GDO\Forum\Method;

use GDO\Table\MethodQueryList;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;

/**
 * Forum search.
 * @author gizmore
 * @version 6.10.1
 * @since 6.7.0
 */
final class Search extends MethodQueryList
{
    public function isSearched() { return true; }
    
    public function beforeExecute() : void
    {
        Module_Forum::instance()->renderTabs();
    }
    
	#######################
	### MethodQueryList ###
	#######################
	public function gdoTable()
	{
		return GDO_ForumThread::table();
	}

	public function getMethodTitle() : string
	{
	    $term = $this->getSearchTerm();
		return t('list_forum_search', [$term ? html($term) : t('anything')]);
	}
	
}
