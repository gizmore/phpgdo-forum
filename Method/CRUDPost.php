<?php
declare(strict_types=1);
namespace GDO\Forum\Method;

use GDO\Core\GDO;
use GDO\Core\GDT;
use GDO\Core\GDT_Hook;
use GDO\Core\GDT_Response;
use GDO\Date\Time;
use GDO\File\GDT_File;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Hidden;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodCrud;
use GDO\Forum\GDO_ForumPost;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\GDO_ForumUnread;
use GDO\Forum\Module_Forum;
use GDO\UI\GDT_CardView;
use GDO\UI\GDT_Message;
use GDO\UI\GDT_Redirect;
use GDO\User\GDO_User;
use GDO\Util\Common;

/**
 * CRUD method for GDO_ForumPost.
 *
 * @version 7.0.3
 * @since 6.3.0
 * @author gizmore
 */
final class CRUDPost extends MethodCrud
{

	private GDO_ForumPost $post;

	private GDO_ForumThread $thread;

	public function isTrivial(): bool { return false; }

	public function gdoTable(): GDO { return GDO_ForumPost::table(); }

	public function hrefList(): string { return href('Forum', 'Thread', '&thread=' . $this->thread->getID()); }

	public function isGuestAllowed(): string { return Module_Forum::instance()->cfgGuestPosts(); }

	public function canCreate(GDO $table): bool { return true; }

	public function canUpdate(GDO $gdo): bool { return $gdo->canEdit(GDO_User::current()); }

	public function canDelete(GDO $gdo): bool { return GDO_User::current()->isAdmin(); }

	public function onRenderTabs(): void
	{
		Module_Forum::instance()->renderTabs();
	}

	public function onMethodInit(): ?GDT
	{
		# 1. Get thread
		$user = GDO_User::current();
		if (
			($pid = Common::getRequestString('quote')) ||
			($pid = Common::getRequestString('reply')) ||
			($pid = Common::getRequestString('id'))
		)
		{
			$post = $this->post = GDO_ForumPost::table()->find($pid);
			$this->thread = $post->getThread();
			if (!$post->canView($user))
			{
				return $this->error('err_permission_read');
			}
		}
		else
		{
			return $this->error('err_thread');
		}
		#
		$_REQUEST['board'] = $this->thread->getBoardID();
		return null;
	}

	public function hasPermission(GDO_User $user): bool
	{
		# 2. Check permission
		if (!$this->thread->canView($user))
		{
			return $this->error('err_permission_create');
		}
		if ($this->thread->isLocked())
		{
			return $this->error('err_thread_locked');
		}
		return true;
	}

	public function execute(): GDT
	{
		# 3. Execute
		$response = parent::execute();

		$card = GDT_CardView::make()->gdo($this->post);
		return GDT_Response::makeWith($card)->addField($response);
	}

	public function createForm(GDT_Form $form): void
	{
		$initialPostHTML = '';
//         if ($this->hasInput('quote'))
//         {
//             # Prefill post on GET and quote
//         	$initialPostHTML = $this->initialMessage();
//         }
		$form->addFields(
			GDT_Hidden::make('post_thread')->initial($this->thread->getID()),
			GDT_Message::make('post_message')->initial($initialPostHTML),
		);
		if (Module_Forum::instance()->canUpload(GDO_User::current()))
		{
			$form->addField(GDT_File::make('post_attachment'));

			if (isset($this->gdo))
			{
				$form->getField('post_attachment')->previewHREF(href('Forum', 'PostImage', '&id={id}'));
			}
		}
		$this->createFormButtons($form);
		$form->actions()->addField(GDT_Submit::make('btn_preview')->label('btn_preview')->icon('view'));
	}

	public function afterCreate(GDT_Form $form, GDO $gdo): void
	{
		$form->getField('post_attachment')->previewHREF(href('Forum', 'DownloadAttachment', "&post={$gdo->getID()}&file={id}"));
		$module = Module_Forum::instance();
		$module->saveConfigVar('forum_latest_post_date', $gdo->getCreated());
		$this->thread->tempUnset('last_post');
		$this->thread->saveVar('thread_lastposted', Time::getDate());
		$module->increaseSetting('forum_posts');
		$this->thread->increase('thread_postcount');
		GDO_ForumUnread::markUnread($gdo);
		GDO_ForumUnread::markRead(GDO_User::current(), $gdo);
		GDT_Hook::callWithIPC('ForumPostCreated', $gdo);
		$this->thread->updateBoardLastPost($gdo);
		$id = $gdo->getID();
		GDT_Redirect::to(href('Forum', 'Thread', '&post=' . $id . '#card-' . $id));
	}

	public function afterUpdate(GDT_Form $form, GDO $gdo): void
	{
		$module = Module_Forum::instance();
		$module->saveConfigVar('forum_latest_post_date', $gdo->getCreated());
		$this->thread->saveVar('thread_lastposted', Time::getDate());
		$id = $gdo->getID();
		$this->thread->updateBoardLastPost($gdo);
		GDO_ForumUnread::markUnread($gdo);
		GDO_ForumUnread::markRead(GDO_User::current(), $gdo);
		GDT_Redirect::to(href('Forum', 'Thread', '&post=' . $id . '#card-' . $id));
	}

	/**
	 * Get the initial message for quoting a message.
	 */
	public function initialMessage(): string
	{
		$by = $this->post->getCreator();
		$at = $this->post->getCreated();
		$msg = $this->post->displayMessage();
		return GDT_Message::quoteMessage($by, $at, $msg);
	}

	public function initialPostLevel()
	{
		return $this->post ? $this->post->getLevel() : '0';
	}

	public function onSubmit_btn_preview(GDT_Form $form)
	{
		$response = parent::renderPage($form);
		$preview = GDO_ForumPost::blank($form->getFormVars());
		return $response->addField(GDT_CardView::make()->gdo($preview));
	}

}
