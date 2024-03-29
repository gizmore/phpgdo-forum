<?php
namespace GDO\Forum\Method;

use GDO\Core\GDO;
use GDO\DB\Query;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\GDT_ForumBoard;
use GDO\Table\GDT_Table;
use GDO\Table\MethodQueryList;

/**
 * Show one page of a thread.
 * The order is ascending, so old links show the same page.
 *
 * @author gizmore
 */
final class Threads extends MethodQueryList
{

	public function isOrdered(): bool { return false; }

	public function isPaginated(): bool { return true; }

	public function isSearched(): bool { return false; }

//    public function getPageName(): string { return 'page'; }

    public function getDefaultOrder(): ?string { return 'thread_created'; }

	public function gdoParameters(): array
	{
		return array_merge(parent::gdoParameters(), [
			GDT_ForumBoard::make('id')->notNull(),
		]);
	}

	public function gdoTable(): GDO
	{
		return GDO_ForumThread::table();
	}

	public function getQuery(): Query
	{
		$board = $this->getBoard();
		return
			parent::getQuery()->
			where("thread_board={$board->getID()}");
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
		$table->title('forum_board_threads', [
			$board->renderName(), $table->countItems()]);
	}

    public function getMethodTitle(): string
    {
        $board = $this->getBoard();
        return t('forum_board_threads2', [
            html($board->renderName()),
        ]);
    }


}
