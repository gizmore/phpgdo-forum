<?php
/** @var $board \GDO\Forum\GDO_ForumBoard **/

use GDO\UI\GDT_Button;
use GDO\Forum\Module_Forum;
use GDO\Forum\Method\LatestPosts;
use GDO\Forum\Method\Threads;
use GDO\Forum\Method\ChildBoards;

# 0. Newest threads
$numLatest = Module_Forum::instance()->cfgNumLatestThreads();
if ($numLatest && $board->isRoot())
{
    echo LatestPosts::make()->executeWithInit()->render();
}

# 1. Children boards as list.
echo ChildBoards::make()->executeWithInit()->render();

# 2. Create thread button
if ($board->allowsThreads())
{
    echo GDT_Button::make('btn_create_thread')->icon('create')->href(href('Forum', 'CreateThread', '&board='.$board->getID()))->render();
}

# 3. Threads as list
if ($board->allowsThreads())
{
    $_REQUEST['board'] = $board->getID();
    echo Threads::make()->execute()->render();
}
