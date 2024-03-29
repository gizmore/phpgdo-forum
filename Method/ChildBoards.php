<?php
namespace GDO\Forum\Method;

use GDO\Core\GDO;
use GDO\DB\Query;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDT_ForumBoard;
use GDO\Table\GDT_Table;
use GDO\Table\MethodQueryList;

/**
 * Show all child boards for a board.
 *
 * @author gizmore
 */
final class ChildBoards extends MethodQueryList
{

	public function gdoTable(): GDO { return GDO_ForumBoard::table(); }

	public function isPaginated(): bool { return false; }

	public function isOrdered(): bool { return false; }

	public function isSearched(): bool { return false; }

	public function gdoParameters(): array
	{
		return [
			GDT_ForumBoard::make('id')->defaultRoot(),
		];
	}

	public function getQuery(): Query
	{
		$board = $this->getBoard();
		return
			GDO_ForumBoard::table()->select()->
			where("board_left BETWEEN {$board->getLeft()} AND {$board->getRight()}")->
			where("board_depth={$board->getDepth()}+1 OR ( board_sticky AND board_depth>{$board->getDepth()} )")->
			order('board_sort');
//		    order('board_depth');
	}

	/**
	 * @return GDO_ForumBoard
	 */
	public function getBoard()
	{
		return $this->gdoParameterValue('id');
	}

	public function setupTitle(GDT_Table $table): void
	{
		$board = $this->getBoard();
		$table->titleRaw($board->renderName());
		$table->hideEmpty();
	}

    public function getMethodTitle(): string
    {
        $board = $this->getBoard();
        return $board->renderName();
    }

}
