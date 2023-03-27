<?php
declare(strict_types=1);
namespace GDO\Forum;

use GDO\Core\GDO;
use GDO\Core\GDO_DBException;
use GDO\Core\GDT_Virtual;
use GDO\DB\Database;
use GDO\DB\Query;
use GDO\User\GDO_User;

/**
 * Virtual board column.
 * Visible threadcount for a user.
 * Visible postcount for a user.
 *
 * @version 7.0.3
 * @since 3.5.0
 * @author gizmore
 */
final class GDT_ForumBoardThreadcount extends GDT_Virtual
{

	private array $countValue = ['0', '0'];

	public function getBoard(): GDO_ForumBoard
	{
		return $this->gdo;
	}

	public function getThreadCount(): int
	{
		return (int) $this->countValue[0];
	}

	public function getPostCount(): int
	{
		return (int) $this->countValue[1];
	}

	public function var(?string $var): static
	{
		if ($var !== null)
		{
			$counts = explode(',', $var);
			$this->countValue = $counts;
		}
		else
		{
			$this->countValue = ['0', '0'];
		}
		return $this;
	}

	/**
	 * Query count of visible threads + posts.
	 *
	 * @throws GDO_DBException
	 */
	public function gdoBeforeRead(GDO $gdo, Query $query): void
	{
		$user = GDO_User::current();
		$dbms = Database::DBMS();
		$conc = $dbms->dbmsConcat('IFNULL(COUNT(*), 0)', "','", 'IFNULL(SUM(thread_postcount), 0)');
		$subquery = "( SELECT {$conc} " .
			'FROM gdo_forumthread AS ft ' .
			'JOIN gdo_forumboard AS fb ON ft.thread_board = fb.board_id ' .
			'WHERE ( fb.board_permission IS NULL OR ( ' .
			'SELECT 1 FROM gdo_userpermission ' .
			"WHERE (perm_user_id={$user->getID()} AND perm_perm_id=fb.board_permission) ) ) " .
			'AND fb.board_left BETWEEN gdo_forumboard.board_left AND gdo_forumboard.board_right )';
		$query->select("$subquery AS {$this->name}");
	}

}
