<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumBoardSubscribe;
use GDO\Forum\GDT_ForumBoard;
use GDO\UI\GDT_Redirect;
use GDO\User\GDO_User;

/**
 * Unsubscribe from a board.
 *
 * @version 7.0.1
 * @author gizmore
 */
final class UnsubscribeBoard extends Method
{

	public function getMethodTitle(): string
	{
		return t('btn_unsubscribe');
	}

	public function gdoParameters(): array
	{
		return [
			GDT_ForumBoard::make('board')->notNull(),
		];
	}

	public function execute()
	{
		$user = GDO_User::current();
		$board = $this->getBoard();
		if ($board->isRoot())
		{
			return $this->error('err_please_use_subscribe_all');
		}
		GDO_ForumBoardSubscribe::table()->deleteWhere(
			"subscribe_user={$user->getID()} AND subscribe_board={$board->getID()}");
		$user->tempUnset('gdo_forum_board_subsciptions');
		$user->recache();
		$href = href('Forum', 'Boards', '&id=' . $board->getParent()->getID());
		$href = GDT_Redirect::hrefBack($href);
		return $this->redirectMessage('msg_unsubscribed', null, $href);
	}

	public function getBoard(): GDO_ForumBoard
	{
		return $this->gdoParameterValue('board');
	}

}
