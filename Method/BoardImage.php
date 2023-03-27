<?php
namespace GDO\Forum\Method;

use GDO\Core\GDT;
use GDO\Core\Method;
use GDO\File\Method\GetFile;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDT_ForumBoard;
use GDO\User\GDO_User;

/**
 * Show a board image.
 *
 * @author gizmore
 */
final class BoardImage extends Method
{

	public function isSavingLastUrl(): bool { return false; }

	public function getMethodTitle(): string
	{
		return $this->getBoard()->getTitle();
	}

	public function getBoard(): GDO_ForumBoard
	{
		return $this->gdoParameterValue('board');
	}

	public function gdoParameters(): array
	{
		return [
			GDT_ForumBoard::make('board')->notNull(),
		];
	}

	public function execute(): GDT
	{
		$user = GDO_User::current();
		$board = $this->getBoard();
		if (!$board->canView($user))
		{
			return $this->error('err_not_allowed');
		}

		if (!$board->hasImage())
		{
			return $this->error('err_no_image');
		}

		return GetFile::make()->executeWithId($board->getImageId(), 'thumb');
	}

}
