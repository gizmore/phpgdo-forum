<?php
namespace GDO\Forum;

use GDO\Votes\GDO_LikeTable;

final class GDO_ForumPostLikes extends GDO_LikeTable
{
	public function gdoLikeObjectTable() { return GDO_ForumPost::table(); }
	
	
}
