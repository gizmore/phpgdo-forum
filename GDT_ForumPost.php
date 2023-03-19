<?php
namespace GDO\Forum;

use GDO\Core\GDT_Object;

/**
 * A selection for a forum post.
 * @author gizmore
 */
final class GDT_ForumPost extends GDT_Object
{
	public function defaultLabel(): static { return $this->label('post'); }
	
	protected function __construct()
	{
	    parent::__construct();
	    $this->table(GDO_ForumPost::table());
	}
	
	/**
	 * @return GDO_ForumPost
	 */
	public function getPost()
	{
		return $this->getValue();
	}
	
}
