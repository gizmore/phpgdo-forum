<?php
namespace GDO\Forum\tpl;
/** @var $thread \GDO\Forum\GDO_ForumThread **/
use GDO\Forum\GDO_ForumPost;
use GDO\Table\GDT_List;
use GDO\Table\GDT_PageMenu;

# Posts as list
$list = GDT_List::make();
$pagemenu = GDT_PageMenu::make();
$query = GDO_ForumPost::table()->select()->where("post_thread={$thread->getID()}")->order('post_created');
$pagemenu->filterQuery($query);
$list->query($query);
$list->listMode(GDT_List::MODE_CARD);
$list->title(t('list_title_thread_posts', [$thread->displayTitle(), $thread->getPostCount()]));
echo $list->render();
