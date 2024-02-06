<?php
namespace GDO\Forum\Method;

use GDO\Core\GDO;
use GDO\Core\GDT;
use GDO\Core\Website;
use GDO\DB\Query;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumPost;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\GDT_ForumPost;
use GDO\Forum\Module_Forum;
use GDO\Table\GDT_Table;
use GDO\Table\MethodQueryCards;
use GDO\User\GDO_User;

/**
 * Display a forum thread.
 *
 * @author gizmore
 */
final class Thread extends MethodQueryCards
{

    public function isPaginated(): bool { return true; }

	public function isOrdered(): bool { return false; }

	public function isSearched(): bool { return false; }

	public function getDefaultOrder(): ?string
	{
		return 'post_created';
	}

	public function getDefaultIPP(): int
	{
		return 10;
	}

	public function onRenderTabs(): void
	{
		Module_Forum::instance()->renderTabs();
	}

	public function gdoParameters(): array
	{
		return [
			GDT_ForumPost::make('post')->notNull(),
		];
	}

	public function hasPermission(GDO_User $user, string &$error, array &$args): bool
	{
//     	$this->onInitTable();
		return $this->getThread()->canView($user);
	}

	public function getThread(): GDO_ForumThread
	{
		return $this->getPost()->getThread();
	}

	public function getPost(): GDO_ForumPost
	{
		return $this->gdoParameterValue('post');
	}

	public function gdoTable(): GDO
	{
		return GDO_ForumPost::table();
	}

	public function getQuery(): Query
	{
		$thread = $this->getThread();
		$uid = GDO_User::current()->getID();
		return
			parent::getQuery()->
			join("LEFT JOIN gdo_forumpostlikes ON like_user={$uid} AND like_object=post_id")->
			where("post_thread={$thread->getID()}");
	}

	public function setupTitle(GDT_Table $table): void
	{
		$thread = $this->getThread();
		Website::setTitle($thread->getTitle());
		$table->titleRaw(t('list_title_thread_posts',
			[$thread->displayTitle(), $table->countItems()]));
	}

//	/**
//	 * Set board correctly on init.
//	 * Go to default page for a post.
//	 */
//	public function onMethodInit(): ?GDT
//	{
//		parent::onMethodInit();
//		return
////         $_REQUEST['id'] = $this->getThread()->getBoardID();
//	}

	protected function beforeCalculateTable(GDT_Table $table): void
	{
//     	parent::beforeCalculateTable($table);
//         $o = $this->table->headers->name;
		if (!isset($this->inputs['page']))
		{
			$defaultPage = $this->table->getPageFor($this->getPost());
//             $_REQUEST['page'] = "$defaultPage";
			$this->addInput('page', $defaultPage);
//             $this->table->pagemenu->page($defaultPage);
		}
	}

	public function getMethodTitle(): string
	{
		return $this->getThread()->getTitle();
	}

	public function getBoard(): GDO_ForumBoard
	{
		return $this->getThread()->getBoard();
	}

}
