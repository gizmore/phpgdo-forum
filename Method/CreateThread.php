<?php
declare(strict_types=1);
namespace GDO\Forum\Method;

use GDO\Core\GDO_ArgError;
use GDO\Core\GDO_DBException;
use GDO\Core\GDT;
use GDO\Core\GDT_Hook;
use GDO\Core\Website;
use GDO\Date\Time;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumPost;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\GDO_ForumUnread;
use GDO\Forum\GDT_ForumBoard;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;

/**
 * Start a new thread.
 *
 * @author gizmore
 * @version 7.0.3
 * @see GDO_ForumBoard
 * @see GDO_ForumThread
 * @see GDO_ForumPost
 */
final class CreateThread extends MethodForm
{

	private GDO_ForumPost $post;

	public function isUserRequired(): bool { return true; }

	public function isGuestAllowed(): bool { return Module_Forum::instance()->cfgGuestPosts(); }

	public function onRenderTabs(): void
	{
		Module_Forum::instance()->renderTabs();
	}

	public function gdoParameters(): array
	{
		return [
			GDT_ForumBoard::make('board')->notNull(),
		];
	}

	/**
	 * @throws GDO_ArgError
	 */
	public function execute(): GDT
	{
		$board = $this->getBoard();
		if (
			(!$board->canView(GDO_User::current())) ||
			(!$board->allowsThreads())
		)
		{
			return $this->permissionError('err_permission_create');
		}
		return parent::execute();
	}

	public function permissionError(string $reason): GDT
	{
		return Website::error($this->renderTitle(), 'err_permission', [$this->gdoHumanName(), $reason]);
	}

	/**
	 * @throws GDO_ArgError
	 */
	public function getBoard(): GDO_ForumBoard
	{
		return $this->gdoParameterValue('board');
	}

	/**
	 * @throws GDO_ArgError
	 */
	protected function createForm(GDT_Form $form): void
	{
        $module = Module_Forum::instance();
        $board = $this->getBoard();
        $gdo = GDO_ForumThread::table();
        $posts = GDO_ForumPost::table();
        $form->addFields(
			$gdo->gdoColumn('thread_board')->noChoices($board)->initial($board ? $board->getID() : null)->writeable(false),
			$gdo->gdoColumn('thread_level'),
			$gdo->gdoColumn('thread_title'),
			$posts->gdoColumn('post_message'),
			$posts->gdoColumn('post_attachment'),
			GDT_AntiCSRF::make(),
		);

        if (!$module->cfgUseLevel())
        {
            $form->removeFieldNamed('thread_level');
        }

        $form->actions()->addField(GDT_Submit::make());

        $user = GDO_User::current();
		if (!$module->canUpload($user))
		{
			$form->removeFieldNamed('post_attachment');
		}
	}

	public function formValidated(GDT_Form $form): GDT
	{
		$module = Module_Forum::instance();
		$thread = GDO_ForumThread::blank($form->getFormVars());
		$thread->setValue('thread_lastposter', GDO_User::current());
		$thread->setVar('thread_lastposted', Time::getDate());
		$thread->insert();
		$data = $form->getFormVars();
		$post = $this->post = GDO_ForumPost::blank($data);
		$post->setVar('post_thread', $thread->getID());
		$post->setVar('post_first', '1');
		$post->insert();
		$module->saveConfigVar('forum_latest_post_date', $post->getCreated());
		GDO_ForumUnread::markUnread($post);
		$thread->updateBoardLastPost($post);
		$module->increaseSetting('forum_threads');
		$module->increaseSetting('forum_posts');
		$href = href('Forum', 'Thread', "&post={$post->getID()}");
		return $this->redirectMessage('msg_thread_created', null, $href);
	}

	public function afterExecute(): void
	{
		if (isset($this->post) && $this->post->isPersisted())
		{
			GDT_Hook::callWithIPC('ForumPostCreated', $this->post);
		}
	}

	/**
	 * To test, this method requires to plug a board that allows threads.
	 *
	 * @throws GDO_DBException
	 */
	public function plugVars(): array
	{
		return [
			['board' => GDO_ForumBoard::table()->select('board_id')->where('board_allow_threads')->first()->exec()->fetchVar()],
		];
	}

}
