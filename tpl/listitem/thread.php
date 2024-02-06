<?php
namespace GDO\Forum\tpl\listitem;

/** @var $thread GDO_ForumThread * */

use GDO\Forum\GDO_ForumThread;
use GDO\Forum\GDT_ForumSubscribe;
use GDO\Forum\Module_Forum;
use GDO\Table\GDT_ListItem;
use GDO\UI\GDT_Button;
use GDO\UI\GDT_Link;
use GDO\User\GDO_User;

$lastPoster = $thread->getLastPoster();
$postcount = $thread->getPostCount();
$replycount = $postcount - 1;
$user = GDO_User::current();
$tid = $thread->getID();
$readClass = $thread->hasUnreadPosts($user) ? 'gdo-forum-unread' : 'gdo-forum-read';
$subscribed = $thread->hasSubscribed($user);
$subscribeClass = $subscribed ? 'gdo-forum gdo-forum-subscribed' : 'gdo-forum';
$subscribeLabel = $subscribed ? 'btn_unsubscribe' : 'btn_subscribe';

# Generate @GDT_ListItem to be compat with all themes easily.

$li = GDT_ListItem::make("thread_$tid")->gdo($thread);

$li->addClass($readClass);

$li->creatorHeader();
$li->titleRaw($thread->getTitle());
$li->date($thread->getLastPosted());

if ($replycount)
{
	$linkLastReply = GDT_Link::anchor($thread->hrefLastPost(), $thread->displayLastPosted());
	$li->subtitle('li_thread_replies', [
		$thread->getPostCount() - 1,
		$lastPoster->renderUserName(),
		$linkLastReply]);
}
else
{
	$li->subtitle('li_thread_no_replies');
}

# Actions
$href = $subscribed ? href('Forum', 'UnsubscribeThread', '&thread=' . $tid) : href('Forum', 'Subscribe', '&thread=' . $tid);
$li->actions()->addFields(
	GDT_Button::make('first_post')->href($thread->hrefFirstPost())->icon('view')->label('btn_view_first_post'),
	GDT_Button::make('last_post')->noFollow()->href($thread->hrefLastPost())->icon('view')->label('btn_view_last_post'),
);

if (GDT_ForumSubscribe::ALL !== Module_Forum::instance()->userSettingVar($user, 'forum_subscription'))
{
	$li->actions()->addField(
		GDT_Button::make()->noFollow()->href($href)->icon('email')->label($subscribeLabel)->addClass($subscribeClass)
	);
}

echo $li->render();
