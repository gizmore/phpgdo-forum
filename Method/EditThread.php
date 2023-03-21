<?php
namespace GDO\Forum\Method;

use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\GDT_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\UI\GDT_DeleteButton;
use GDO\User\GDO_User;

/**
 * Edit a thread.
 *
 * @version 7.0.1
 * @since 6.3.0
 * @see GDO_ForumThread
 * @see GDO_ForumPost
 * @author gizmore
 * @see GDO_ForumBoard
 */
final class EditThread extends MethodForm
{

	public function isTrivial(): bool
	{
		return false;
	}

	public function isUserRequired(): bool { return true; }

	public function isGuestAllowed(): bool { return Module_Forum::instance()->cfgGuestPosts(); }

	public function onRenderTabs(): void
	{
		Module_Forum::instance()->renderTabs();
	}

	public function gdoParameters(): array
	{
		return [
			GDT_ForumThread::make('id')->notNull()->withEditPermissions(),
		];
	}

	public function createForm(GDT_Form $form): void
	{
		$user = GDO_User::current();
		$gdo = $this->gdoParameterValue('id');
		if ($user->isStaff())
		{
			$form->addField($gdo->gdoColumn('thread_board'));
		}
		$form->addFields(
			$gdo->gdoColumn('thread_title'),
			GDT_AntiCSRF::make(),
		);
		$form->actions()->addFields(
			GDT_Submit::make(),
			GDT_DeleteButton::make(),
		);
	}

	public function formValidated(GDT_Form $form)
	{
		$this->getThread()->saveVar('thread_title', $form->getFormVar('thread_title'));
		if ($form->hasChanged('thread_board'))
		{
			$this->changeBoard($form->getFormValue('thread_board'));
		}
		$url = href('Forum', 'Thread', '&thread=' . $this->getThread()->getID());
		return $this->redirectMessage('msg_thread_edited', null, $url);
	}

	public function getThread(): GDO_ForumThread
	{
		return $this->gdoParameterValue('id');
	}

	private function changeBoard(GDO_ForumBoard $newBoard)
	{
		$thread = $this->getThread();
		$postsBy = $thread->getPostCount();
		$oldBoard = $thread->getBoard();
		$oldBoard->increaseCounters(-1, -$postsBy);
		$newBoard->increaseCounters(1, $postsBy);
		$thread->saveVar('thread_board', $newBoard->getID());
		return $this->message('msg_thread_moved');
	}

}
