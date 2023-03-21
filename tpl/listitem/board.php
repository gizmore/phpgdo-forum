<?php
namespace GDO\Forum\tpl\listitem;

/** @var $board GDO_ForumBoard * */

use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDT_ForumSubscribe;
use GDO\Forum\Module_Forum;
use GDO\Table\GDT_ListItem;
use GDO\UI\GDT_Button;
use GDO\UI\GDT_Container;
use GDO\UI\GDT_Paragraph;
use GDO\User\GDO_User;

$user = GDO_User::current();
$bid = $board->getID(); ?>
	<?php
$subscribed = $board->hasSubscribed($user);
$subscribeClass = $subscribed ? 'gdo-forum gdo-forum-subscribed' : 'gdo-forum';
$subscribeLabel = $subscribed ? 'btn_unsubscribe' : 'btn_subscribe';
$readClass = $board->hasUnreadPosts($user) ? 'gdo-forum-unread' : 'gdo-forum-read';
$title = seo($board->getTitle());
$href = href('Forum', 'Boards', "&id={$bid}&named={$title}&page={$board->getPageCount()}");
$href2 = $subscribed ?
	href('Forum', 'UnsubscribeBoard', "&board={$bid}&named={$title}") :
	href('Forum', 'Subscribe', "&board={$bid}&named={$title}");

$li = GDT_ListItem::make();
$li->addClass($readClass);

# Image content
// if ($board->hasImage())
// {
//     $li->rawIcon(GDT_Image::fromFile($board->getImage())->renderHTML());
// }
// else
// {
//     $li->icon('book')->iconSize(26);
// }

$li->titleRaw($board->renderName());
$li->subtitle($board->displayDescription());

$li->content(GDT_Container::make()->horizontal()->addFields(
	GDT_Paragraph::make()->text('board_stats', [$board->getUserThreadCount(), $board->getUserPostCount()])
));

$lastThread = $board->getLastThread();

if ($lastThread)
{
	$li->footer(GDT_Paragraph::make()->text('forum_board_last_subtext', [$lastThread->displayTitle()]));
}

# Menu
$li->actions()->addFields(
	GDT_Button::make('view')->href($href)->icon('view'),
);

$module = Module_Forum::instance();
if ($module->userSettingVar($user, 'forum_subscription') !== GDT_ForumSubscribe::ALL)
{
	$li->actions()->addField(
		GDT_Button::make()->addClass($subscribeClass)->href($href2)->icon('email')->label($subscribeLabel)
	);
}

echo $li->render();
