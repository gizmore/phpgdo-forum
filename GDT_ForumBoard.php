<?php
namespace GDO\Forum;

use GDO\Core\GDT_ObjectSelect;

/**
 * A selection for a forum board.
 *
 * @author gizmore
 */
final class GDT_ForumBoard extends GDT_ObjectSelect
{

	public bool $defaultRoot = false;
	public $noChoices = null;

	protected function __construct()
	{
		parent::__construct();
		$this->table(GDO_ForumBoard::table());
		$this->emptyLabel('no_parent');
	}

	####################
	### Default root ###
	####################

	public function defaultLabel(): self { return $this->label('board'); }

	protected function getChoices(): array
	{
		if ($this->noChoices)
		{
			$nc = $this->noChoices;
			return [$nc->getID() => $nc];
		}
		return $this->table->allCached();
	}

	##################
	### No choices ###
	##################

	public function renderHTML(): string
	{
		if ($board = $this->getBoard())
		{
			return $board->displayTitle();
		}
		return t('none');
	}

	public function getBoard(): ?GDO_ForumBoard
	{
		return $this->getValue();
	}

	public function defaultRoot(bool $defaultRoot = true)
	{
		$this->defaultRoot = $defaultRoot;
		return $this->initialValue(Module_Forum::instance()->cfgRoot());
	}

// 	public function getValue()
// 	{
// 	    if (!($board = parent::getValue()))
// 	    {
// 	        if ($this->defaultRoot)
// 	        {
// 	            $board = ;
// 	        }
// 	    }
// 	    return $board;
// 	}

	public function noChoices(GDO_ForumBoard $noChoices = null)
	{
		$this->noChoices = $noChoices;
		return $this;
	}

	public function withCompletion()
	{
		$this->completionHref(href('Forum', 'BoardCompletion'));
	}

}
