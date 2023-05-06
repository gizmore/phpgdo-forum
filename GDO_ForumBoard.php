<?php
declare(strict_types=1);
namespace GDO\Forum;

use GDO\Category\GDO_Tree;
use GDO\Core\GDO;
use GDO\Core\GDO_DBException;
use GDO\Core\GDT_AutoInc;
use GDO\Core\GDT_Checkbox;
use GDO\Core\GDT_CreatedAt;
use GDO\Core\GDT_CreatedBy;
use GDO\Core\GDT_String;
use GDO\Core\GDT_Template;
use GDO\Core\GDT_UInt;
use GDO\File\GDO_File;
use GDO\File\GDT_ImageFile;
use GDO\Table\GDT_PageMenu;
use GDO\Table\GDT_Sort;
use GDO\UI\GDT_Title;
use GDO\User\GDO_Permission;
use GDO\User\GDO_User;
use GDO\User\GDT_Permission;

/**
 * A board inherits from GDO_Tree.
 *
 * @author gizmore
 * @see GDO_Tree
 * @see GDO_ForumThread
 * @see GDO_ForumPost
 */
final class GDO_ForumBoard extends GDO_Tree
{

	public static bool $BUILD_TREE_UPON_SAVE = true;

	# set to false for faster board creation during import.

	public static function getRoot(): self
	{
		return Module_Forum::instance()->cfgRoot();
	}

	# ###########
	# ## Root ###
	# ###########

	public function isTestable(): bool
	{
		return false;
	}

	# ###########
	# ## Tree ###
	# ###########

	public function gdoTreePrefix(): string
	{
		return 'board';
	}

	# ##########
	# ## GDO ###
	# ##########
	public function gdoCached(): bool
	{
		return true;
	}

	public function gdoColumns(): array
	{
		return array_merge(
			[
				GDT_AutoInc::make('board_id'),
				GDT_Title::make('board_title')->notNull()
					->utf8()
					->caseI()
					->label('title')
					->max(64),
				GDT_String::make('board_description')->utf8()
					->caseI()
					->label('description')
					->icon('message')
					->max(256),
				GDT_Permission::make('board_permission'),
				GDT_Checkbox::make('board_allow_threads')->initial('0'),
				GDT_Checkbox::make('board_allow_guests')->initial('0'),
				GDT_Checkbox::make('board_sticky')->initial('0'),
				GDT_ForumBoardThreadcount::make('board_user_count_')->gdtType(GDT_UInt::make()), # thread- and postcount via an
				# ugly hack @see
				# GDT_ForumBoardThreadcount
				GDT_ForumPost::make('board_lastpost'),
				GDT_ImageFile::make('board_image')->scaledVersion('thumb', 48, 48),
				GDT_Sort::make('board_sort'),
				GDT_CreatedAt::make('board_created'),
				GDT_CreatedBy::make('board_creator'),
			], parent::gdoColumns());
	}

	# #############
	# ## Getter ###
	# #############
	public function allowsThreads(): string
	{
		return $this->gdoVar('board_allow_threads');
	}

	public function displayTitle(): string
	{
		return $this->gdoDisplay('board_title');
	}

	public function getUserThreadCount(): int
	{
		return $this->gdoColumn('board_user_count_')->getThreadCount();
	}

	public function getUserPostCount(): int
	{
		return $this->gdoColumn('board_user_count_')->getPostCount();
	}

	public function getTitle(): string
	{
		return $this->gdoVar('board_title');
	}

	public function getPermission(): ?GDO_Permission
	{
		return $this->gdoValue('board_permission');
	}

	public function isRoot(): bool
	{
		return $this->getID() === Module_Forum::instance()->cfgRootID();
	}

	public function getLastThread(): ?GDO_ForumThread
	{
		$post = $this->getLastPost();
		return $post?->getThread();
	}

	public function getLastPost(): ?GDO_ForumPost
	{
		return $this->gdoValue('board_lastpost');
	}

	public function getImage(): ?GDO_File
	{
		if ($image = $this->gdoValue('board_image'))
		{
			$image->tempHref(href('Forum', 'BoardImage', '&board=' . $this->getID() . '&file=' . $this->getImageId()));
			return $image;
		}
		return null;
	}

	public function getImageId(): ?string
	{
		return $this->gdoVar('board_image');
	}

	public function hasImage(): bool
	{
		return !!$this->gdoVar('board_image');
	}

	public function hrefView(): string
	{
		return href('Forum', 'Boards', "&id={$this->getID()}");
	}

	public function canView(GDO_User $user): bool
	{
		return !$this->needsPermission() || $user->hasPermissionID($this->getPermissionID());
	}

	public function needsPermission(): bool
	{
		return $this->getPermissionID() !== null;
	}

	public function getPermissionID(): ?string
	{
		return $this->gdoVar('board_permission');
	}

	public function displayDescription(): string
	{
		return html($this->getDescription());
	}

	# ###########
	# ## HREF ###
	# ###########

	public function getDescription(): ?string
	{
		return $this->gdoVar('board_description');
	}

	# #################
	# ## Permission ###
	# #################

	public function getPageCount(): int
	{
		$count = GDO_ForumThread::table()->countWhere('thread_board=' . $this->getID());
		$ipp = Module_Forum::instance()->cfgThreadsPerPage();
		return GDT_PageMenu::getPageCountS($count, $ipp);
	}

	public function hasUnreadPosts(GDO_User $user): bool
	{
		if ($user->isGhost())
		{
			return false;
		}
		return (bool) GDO_ForumUnread::isBoardUnread($user, $this);
	}

	# #############
	# ## Render ###
	# #############

	public function hasSubscribed(GDO_User $user): bool
	{
		if ($user->isGhost())
		{
			return false;
		}

		if (Module_Forum::instance()->userSettingVar($user, 'forum_subscription') === GDT_ForumSubscribe::ALL)
		{
			return true;
		}

		return str_contains($this->getForumSubscriptions($user), ",{$this->getID()},");
	}

	/**
	 * @throws GDO_DBException
	 */
	public function getForumSubscriptions(GDO_User $user): string
	{
		if (null === ($cache = $user->tempGet('gdo_forum_board_subsciptions')))
		{
			$cache = GDO_ForumBoardSubscribe::table()->select('GROUP_CONCAT(subscribe_board)')
				->where("subscribe_user={$user->getID()}")
				->exec()
				->fetchVar();
			$cache = empty($cache) ? '' : ",$cache,";
			$user->tempSet('gdo_forum_board_subsciptions', $cache);
			$user->recache();
		}
		return $cache;
	}

	public function renderName(): string
	{
		return html($this->getTitle());
	}


	public function renderList(): string
	{
		return GDT_Template::php('Forum', 'listitem/board.php', [
			'board' => $this,
		]);
	}


	public function renderOption(): string
	{
		return sprintf('%s - %s', $this->getID(), $this->renderName());
	}

	# ############
	# ## Cache ###
	# ############
	public function gdoAfterCreate(GDO $gdo): void
	{
		$this->clearCache();
		if (self::$BUILD_TREE_UPON_SAVE)
		{
			parent::gdoAfterCreate($gdo);
		}
	}


}
