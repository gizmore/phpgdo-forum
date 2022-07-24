<?php
namespace GDO\Forum;

use GDO\Core\GDT_ObjectSelect;

/**
 * A forum thread.
 * 
 * @author gizmore
 * @version 7.0.1
 */
final class GDT_ForumThread extends GDT_ObjectSelect
{
	public function defaultLabel() : self
	{
		return $this->label('thread');
	}
	
	protected function __construct()
	{
	    parent::__construct();
	    $this->table(GDO_ForumThread::table());
	}
	
	public function getThread() : GDO_ForumThread
	{
		return $this->getValue();
	}

}
