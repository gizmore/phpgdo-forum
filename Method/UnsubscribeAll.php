<?php
namespace GDO\Forum\Method;

use GDO\Core\GDT;
use GDO\Core\GDT_Token;
use GDO\Core\Method;
use GDO\Forum\GDO_ForumBoardSubscribe;
use GDO\Forum\GDO_ForumThreadSubscribe;
use GDO\Forum\GDT_ForumSubscribe;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;
use GDO\User\GDT_User;

/**
 * Unsubscribe the whole forum.
 *
 * @version 7.0.1
 * @since 6.10
 * @author gizmore
 */
final class UnsubscribeAll extends Method
{

	public function getMethodTitle(): string
	{
		return t('btn_unsubscribe');
	}

	public function gdoParameters(): array
	{
		return [
			GDT_User::make('user')->notNull(),
			GDT_Token::make('token')->notNull(),
		];
	}

	public function execute(): GDT
	{
		$user = $this->getUser();
		$token = $this->getToken();
		if ($token !== $user->gdoHashcode())
		{
			return $this->error('err_token');
		}

		GDO_ForumThreadSubscribe::table()->deleteWhere("subscribe_user={$user->getID()}")->exec();

		GDO_ForumBoardSubscribe::table()->deleteWhere('subscribe_user=' . $user->getID())
			->exec();

		Module_Forum::instance()->saveUserSetting($user, 'forum_subscription', GDT_ForumSubscribe::NONE);

		return $this->redirectMessage('msg_unsubscribed', null, hrefDefault());
	}

	public function getUser(): GDO_User
	{
		return $this->gdoParameterValue('user');
	}

	public function getToken()
	{
		return $this->gdoParameterVar('token');
	}

}
