<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDT_ForumBoard;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;

/**
 * Show a boards page.
 *
 * @author gizmore
 */
final class Boards extends Method
{

	private GDO_ForumBoard $board;

	public function gdoParameters(): array
	{
		return [
			GDT_ForumBoard::make('id')->defaultRoot(),
		];
	}

	public function onRenderTabs(): void
	{
		Module_Forum::instance()->renderTabs();
	}

	public function execute()
	{
		$board = $this->board = $this->gdoParameterValue('id');

		if ((!$board) || (!$board->canView(GDO_User::current())))
		{
			return $this->error('err_permission_read');
		}

		$tVars = [
			'board' => $board,
			'inputs' => $this->getInputs(),
		];

		return $this->templatePHP('boards.php', $tVars);
	}

	public function getMethodTitle(): string
	{
		if (isset($this->board))
		{
			return $this->board->getTitle();
		}
		else
		{
			return t('gdo_forumboard');
		}
	}

}
