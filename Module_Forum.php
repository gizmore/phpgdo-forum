<?php
declare(strict_types=1);
namespace GDO\Forum;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\Core\GDT_Template;
use GDO\Core\GDT_UInt;
use GDO\Core\Module_Core;
use GDO\Date\GDT_DateTime;
use GDO\DB\Cache;
use GDO\UI\GDT_Link;
use GDO\UI\GDT_Message;
use GDO\UI\GDT_Page;
use GDO\User\GDO_Permission;
use GDO\User\GDO_User;
use GDO\User\GDT_ACLRelation;
use GDO\User\GDT_Level;

/**
 * GDO Forum Module.
 *
 * @version 7.0.3
 * @since 2.0.0
 * @author gizmore
 */
final class Module_Forum extends GDO_Module
{

	public int $priority = 45;

	public function getDependencies(): array
	{
		return [
			'File',
		];
	}

	public function getFriendencies(): array
	{
		return [
			'Avatar',
			'GTranslate',
		];
	}

	# #############
	# ## Module ###
	# #############

	public function href_administrate_module(): ?string
	{
		return $this->href('Admin');
	}

	public function getClasses(): array
	{
		return [
			GDO_ForumBoard::class,
			GDO_ForumThread::class,
			GDO_ForumPost::class,
			GDO_ForumUnread::class,
			GDO_ForumThreadSubscribe::class,
			GDO_ForumBoardSubscribe::class,
			GDO_ForumPostLikes::class,
		];
	}

	public function onLoadLanguage(): void
	{
		$this->loadLanguage('lang/forum');
	}

	public function onIncludeScripts(): void
	{
		$this->addCSS('css/gwf-forum.css');
	}

	# #############
	# ## Config ###
	# #############
	public function getACLDefaults(): array
	{
		return [
			'forum_posts' => [GDT_ACLRelation::ALL, '0', null],
			'forum_threads' => [GDT_ACLRelation::ALL, '0', null],
			'forum_readmark' => [GDT_ACLRelation::HIDDEN, '0', null],
			'forum_subscription' => [GDT_ACLRelation::HIDDEN, '0', null],
		];
	}

	public function getUserSettings(): array
	{
		return [
			GDT_ForumSubscribe::make('forum_subscription')->initialValue(GDT_ForumSubscribe::OWN)->noacl(),
		];
	}

	public function getUserSettingBlobs(): array
	{
		return [
			GDT_Message::make('signature')->max(4096)->label('signature')->noacl(),
		];
	}

	/**
	 * Store some stats in hidden settings.
	 */
	public function getUserConfig(): array
	{
		return [
			GDT_UInt::make('forum_posts')->initial('0'),
			GDT_UInt::make('forum_threads')->initial('0'),
			GDT_DateTime::make('forum_readmark')->label('forum_readmark')->noacl()->hidden(),
		];
	}

	public function getConfig(): array
	{
		return [
			GDT_ForumBoard::make('forum_root')->writeable(false),
			GDT_Checkbox::make('forum_guest_posts')->initial('1'),
			GDT_Checkbox::make('forum_attachments')->initial('1'),
			GDT_Level::make('forum_attachment_level')->initial('0'),
			GDT_Level::make('forum_post_level')->initial('0'),
			GDT_DateTime::make('forum_latest_post_date')->writeable(false),
			GDT_UInt::make('forum_mail_sent_for_post')->initial('0')->writeable(false),
			GDT_Checkbox::make('forum_mail_enable')->initial('1'),
			GDT_UInt::make('forum_num_latest')->initial('6'),
			GDT_Checkbox::make('hook_sidebar')->initial('1'),
			GDT_UInt::make('forum_threads_per_page')->initial('20'),
            GDT_Checkbox::make('forum_use_level')->notNull()->initial('0'),
		];
	}

	/**
	 * Create a root board element on install.
	 */
	public function onInstall(): void
	{
		if (!$this->cfgRootID())
		{
			$root = GDO_ForumBoard::blank(
				[
					'board_title' => 'GDOv6 Forum',
					'board_description' => 'Welcome to the GDOv6 Forum Module',
                    'board_guests' => '1',
				])->insert();
			$this->saveConfigVar('forum_root', $root->getID());
		}
	}

	public function cfgRootID(): ?string
	{
		return $this->getConfigVar('forum_root');
	}

    public function cfgUseLevel(): bool
    {
        return $this->getConfigValue('forum_use_level');
    }

	public function onWipe(): void
	{
		Cache::flush();
	}

	public function onInitSidebar(): void
	{
		if ($this->cfgHookLeftBar())
		{
			$user = GDO_User::current();
			if ($root = $this->cfgRoot())
			{
				$posts = $root->getUserPostCount();
				$link = GDT_Link::make()->text('link_forum', [
					$posts,
				])->href(href('Forum', 'Boards'))->icon('book');
				if ($user->isAuthenticated())
				{
					if (GDO_ForumUnread::countUnread($user) > 0)
					{
						$link->icon('alert');
					}
				}
				GDT_Page::instance()->leftBar()->addField($link);
			}
		}
	}

	public function cfgHookLeftBar(): bool
	{
		return $this->getConfigValue('hook_sidebar');
	}

	public function cfgRoot(): GDO_ForumBoard
	{
		return $this->getConfigValue('forum_root');
	}

	public function cfgGuestPosts(): bool
	{
		return Module_Core::instance()->cfgAllowGuests();
	}

	public function cfgPostLevel(): int
	{
		return $this->getConfigValue('forum_post_level');
	}

	public function cfgLastPostDate(): ?string
	{
		return $this->getConfigVar('forum_latest_post_date');
	}

	public function cfgLastPostMail(): ?string
	{
		return $this->getConfigVar('forum_mail_sent_for_post');
	}

	public function cfgNumLatestThreads(): int
	{
		return $this->getConfigValue('forum_num_latest');
	}

	public function cfgMailEnabled(): bool
	{
		return $this->getConfigValue('forum_mail_enable');
	}

	# ##################
	# ## Permissions ###
	# ##################

	public function cfgThreadsPerPage(): int
	{
		return $this->getConfigValue('forum_threads_per_page');
	}

	# ##############
	# ## Install ###
	# ##############

	public function canUpload(GDO_User $user): bool
	{
		return $this->cfgAttachments() && ($user->getLevel() >= $this->cfgAttachmentLevel());
	}

	public function cfgAttachments(): bool
	{
		return $this->getConfigValue('forum_attachments');
	}

	# ############
	# ## Hooks ###
	# ############

	public function cfgAttachmentLevel(): int
	{
		return $this->getConfigValue('forum_attachment_level');
	}

	public function hookForumPostCreated(GDO_ForumPost $post): void
	{
		$post->getThread()
			->getBoard()
			->recache();
		Cache::flush();
	}

	# #############
	# ## Render ###
	# #############

	/**
	 * On granting a permission,
	 * the new available forum threads are marked as unred.
	 *
	 * @param GDO_User $user
	 * @param GDO_Permission $permission
	 */
	public function hookUserPermissionGranted(GDO_User $user, GDO_Permission $permission)
	{
		GDO_ForumUnread::markUnreadForPermission($user, $permission);
	}

	public function renderTabs()
	{
		GDT_Page::instance()->topResponse()->addField(GDT_Template::make()->template('Forum', 'tabs.php'));
	}

}
