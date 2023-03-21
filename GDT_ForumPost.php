<?php
namespace GDO\Forum;

use GDO\Core\GDT_Object;

/**
 * A selection for a forum post.
 *
 * @author gizmore
 */
final class GDT_ForumPost extends GDT_Object
{

	protected function __construct()
	{
		parent::__construct();
		$this->table(GDO_ForumPost::table());
	}

	public function defaultLabel(): self { return $this->label('post'); }

	/**
	 * @return GDO_ForumPost
	 */
	public function getPost()
	{
		return $this->getValue();
	}

}
