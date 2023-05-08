<?php
namespace GDO\Forum\Method;

use GDO\Admin\MethodAdmin;
use GDO\Core\GDT;
use GDO\Core\GDT_Checkbox;
use GDO\DB\Database;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumPost;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\UI\GDT_Page;
use GDO\User\GDO_User;

/**
 * Repair values like likes, lastposter, lastpostdate, etc.
 *
 * Used after an import from other forums or when something went wrong.
 *
 * @version 7.0.1
 * @since 6.10.0
 * @author gizmore
 */
final class Repair extends MethodForm
{

	use MethodAdmin;

	public function isTrivial(): bool { return false; }

	public function isTransactional(): bool { return false; }

	protected function createForm(GDT_Form $form): void
	{
		$form->text('info_forum_repair');
		$form->addFields(
			GDT_Checkbox::make('repair_empty_threads')->initial('1'),
			GDT_Checkbox::make('repair_tree')->initial('1'),
			GDT_Checkbox::make('repair_firstpost_flag')->initial('1'),
			GDT_Checkbox::make('repair_thread_lastpost')->initial('1'),
			GDT_Checkbox::make('repair_thread_firstpost')->initial('1'),
			GDT_Checkbox::make('repair_thread_postcount')->initial('1'),
			GDT_Checkbox::make('repair_forum_lastpost')->initial('1'),
			GDT_Checkbox::make('repair_user_postcount')->initial('1'),
			GDT_Checkbox::make('repair_readmark')->initial('1'),
			GDT_AntiCSRF::make(),
		);
		$form->actions()->addField(GDT_Submit::make());
	}

	##################
	### MethodForm ###
	##################

	public function formValidated(GDT_Form $form): GDT
	{
		$this->repair($form);
		return parent::formValidated($form);
	}

	/**
	 * Start the selected repairs.
	 *
	 * @param GDT_Form $form
	 */
	public function repair(GDT_Form $form)
	{
		set_time_limit(60 * 60);
		ini_set('memory_limit', '512M');

		if ($form->getFormValue('repair_empty_threads'))
		{
			$this->repairEmptyThreads();
		}
		if ($form->getFormValue('repair_tree'))
		{
			$this->repairTree();
		}
		if ($form->getFormValue('repair_firstpost_flag'))
		{
			$this->repairIsFirstPost();
		}
		if ($form->getFormValue('repair_thread_lastpost'))
		{
			$this->repairThreadLastPoster();
		}
		if ($form->getFormValue('repair_thread_firstpost'))
		{
			$this->repairThreadFirstPoster();
		}
		if ($form->getFormValue('repair_forum_lastpost'))
		{
			$this->repairLastPostInForum();
		}
		if ($form->getFormValue('repair_readmark'))
		{
			$this->repairReadmark();
		}
		if ($form->getFormValue('repair_user_postcount'))
		{
			$this->repairUserPostcount();
		}
		if ($form->getFormValue('repair_thread_postcount'))
		{
			$this->repairThreadPostcount();
		}
	}

	private function repairEmptyThreads()
	{
		$subquery = 'SELECT COUNT(*) FROM gdo_forumpost WHERE post_thread=thread_id';
		GDO_ForumThread::table()->deleteWhere("( $subquery ) = 0");
	}

	############
	### Util ###
	############

	private function repairTree()
	{
		GDO_ForumBoard::table()->rebuildFullTree();
	}

	###############
	### Repairs ###
	###############

	/**
	 * Repair the post_first indicator in posts table.
	 */
	private function repairIsFirstPost()
	{
		GDO_ForumPost::table()->update()->set('post_first=0')->exec();
		$threads = GDO_ForumThread::table()->select()->exec();
		while ($thread = $threads->fetchObject())
		{
			$this->repairIsFirstPostB($thread);
		}
	}

	private function repairIsFirstPostB(GDO_ForumThread $thread)
	{
		$firstPost = GDO_ForumPost::table()->select()->
		where("post_thread={$thread->getID()}")->
		order('post_created', true)->
		first()->exec()->fetchObject();
		if (!$firstPost)
		{
			$thread->delete();
		}
		else
		{
			$firstPost->saveVar('post_first', '1', false);
		}
	}

	private function repairThreadLastPoster()
	{
		foreach (GDO_ForumThread::table()->all() as $thread)
		{
			$post = $thread->getLastPost();
			$thread->saveVars([
				'thread_lastposter' => $post->isEdited() ? $post->getEditorID() : $post->getCreatorID(),
				'thread_lastposted' => $post->isEdited() ? $post->getEdited() : $post->getCreated(),
			], false);
		}
	}

	private function getLastPost()
	{
		return
			GDO_ForumPost::table()->select()->
			first()->order('post_created', false)->
			exec()->fetchObject();
	}

	private function repairThreadFirstPoster()
	{
		foreach (GDO_ForumThread::table()->all() as $thread)
		{
			$post = $thread->getLastPost(true);
			$thread->saveVars([
				'thread_creator' => $post->getCreatorID(),
				'thread_created' => $post->getCreated(),
			], false);
		}
	}

	private function repairLastPostInForum()
	{
		$module = Module_Forum::instance();
		if ($post = $this->getLastPost())
		{
			$module->saveConfigVar('forum_latest_post_date', $post->getCreated());
			$module->saveConfigVar('forum_mail_sent_for_post', $post->getID());
		}
	}

	/**
	 * Repair readmark and lastpost.
	 */
	private function repairReadmark()
	{
		$module = Module_Forum::instance();
		$lastPost = $this->getLastPost();
		$users = GDO_User::table()->select()->exec();
		/** @var $user GDO_User * */
		while ($user = $users->fetchObject())
		{
			$module->saveUserSetting($user, 'forum_readmark', $lastPost->getCreated());
		}
	}

	private function repairUserPostcount()
	{
		$module = Module_Forum::instance();
		$result = GDO_User::table()->select()->exec();
		/** @var $user GDO_User * */
		while ($user = $result->fetchObject())
		{
			$count = GDO_ForumPost::table()->countWhere("post_creator={$user->getID()}");
			if ($count)
			{
				$module->saveUserSetting($user, 'forum_posts', $count);
			}
			$count = GDO_ForumThread::table()->countWhere("thread_creator={$user->getID()}");
			if ($count)
			{
				$module->saveUserSetting($user, 'forum_threads', $count);
			}
		}
	}

	#############################
	### Postcount in settings ###
	#############################

	private function repairThreadPostcount()
	{
		$subselect = 'SELECT COUNT(*) FROM gdo_forumpost WHERE post_thread=thread_id';
		$query = "UPDATE gdo_forumthread SET thread_postcount = ( $subselect )";
		Database::instance()->queryWrite($query);
	}

	public function onRenderTabs(): void
	{
		$this->renderAdminBar();
		GDT_Page::$INSTANCE->topResponse()->addField(
			Admin::make()->adminTabs());
	}

}
