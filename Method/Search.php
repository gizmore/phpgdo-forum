<?php
namespace GDO\Forum\Method;

use GDO\Core\GDO;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\Table\MethodQueryList;

/**
 * Forum search.
 *
 * @version 6.10.1
 * @since 6.7.0
 * @author gizmore
 */
final class Search extends MethodQueryList
{

	public function isSearched(): bool { return true; }

	public function onRenderTabs(): void
	{
		Module_Forum::instance()->renderTabs();
	}

	#######################
	### MethodQueryList ###
	#######################
	public function gdoTable(): GDO
	{
		return GDO_ForumThread::table();
	}

	public function getMethodTitle(): string
	{
		$term = $this->getSearchTerm();
		return t('list_forum_search', [$term ? html($term) : t('anything')]);
	}

}
