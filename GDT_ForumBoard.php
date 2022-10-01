<?php
namespace GDO\Forum;

use GDO\Core\GDT_Template;
use GDO\Core\GDT_ObjectSelect;

/**
 * A selection for a forum board.
 * @author gizmore
 */
final class GDT_ForumBoard extends GDT_ObjectSelect
{
	public function defaultLabel() : self { return $this->label('board'); }
	
	protected function __construct()
	{
	    parent::__construct();
	    $this->table(GDO_ForumBoard::table());
		$this->emptyLabel('no_parent');
	}
	
	public function getChoices()
	{
	    if ($this->noChoices)
	    {
	        $nc = $this->noChoices;
	        return [$nc->getID() => $nc];
	    }
        return $this->table->allCached();
	}
	
	####################
	### Default root ###
	####################
	public bool $defaultRoot = false;
	public function defaultRoot(bool $defaultRoot = true)
	{
	    $this->defaultRoot = $defaultRoot;
	    return $this->notNull();
	}
	
	##################
	### No choices ###
	##################
	public $noChoices = null;
	public function noChoices(GDO_ForumBoard $noChoices=null)
	{
	    $this->noChoices = $noChoices;
	    return $this;
	}

	public function getBoard() : GDO_ForumBoard
	{
		return $this->getValue();
	}
	
	public function getValue()
	{
	    if (!$board = parent::getValue())
	    {
	        if ($this->defaultRoot)
	        {
	            $board = Module_Forum::instance()->cfgRoot();
	        }
	    }
	    return $board;
	}
	
	public function withCompletion()
	{
	 	$this->completionHref(href('Forum', 'BoardCompletion'));
	}
	
	public function renderHTML() : string
	{
		return $this->getBoard()->displayTitle();
// 		return GDT_Template::php('Forum', 'cell/board.php', ['field'=>$this]);
	}
	
// 	public function renderOption($choice)
// 	{
// 		return GDT_Template::php('Forum', 'choice/board.php', ['field'=>$this,'board' =>$choice]);
// 	}
	
}
