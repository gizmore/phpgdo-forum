<?php
namespace GDO\Forum\tpl;

/** @var $board GDO_ForumBoard * */

/** @var $inputs array * */

use GDO\Core\GDT;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\Method\ChildBoards;
use GDO\Forum\Method\LatestPosts;
use GDO\Forum\Method\Threads;
use GDO\Forum\Module_Forum;
use GDO\UI\GDT_Button;

# 0. Newest threads
$numLatest = Module_Forum::instance()->cfgNumLatestThreads();
if ($numLatest && $board->isRoot())
{
	echo LatestPosts::make()->executeWithInputs(GDT::EMPTY_ARRAY)->render();
}

# 1. Children boards as list.
echo ChildBoards::make()->executeWithInputs($inputs)->render();

# 2. Create thread button
if ($board->allowsThreads())
{
	echo GDT_Button::make('btn_create_thread')->icon('create')->href(href('Forum', 'CreateThread', '&board=' . $board->getID()))->render();
}

# 3. Threads as list
if ($board->allowsThreads())
{
	echo Threads::make()->executeWithInputs($inputs)->render();
}
