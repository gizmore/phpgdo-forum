<?php
/** @var $post GDO\Forum\GDO_ForumPost */

use GDO\Core\GDT_Hook;
use GDO\Core\GDT_UInt;
use GDO\Date\Time;
use GDO\Forum\Module_Forum;
use GDO\UI\GDT_Button;
use GDO\UI\GDT_Card;
use GDO\UI\GDT_Container;
use GDO\UI\GDT_EditButton;
use GDO\UI\GDT_HTML;
use GDO\User\GDO_User;
use GDO\User\GDT_ProfileLink;
use GDO\Votes\GDT_LikeButton;

$id = $post->getID();

$user = GDO_User::current();
$unread = $post->isUnread($user);
$readClass = $unread ? 'gdo-forum-unread' : 'gdo-forum-read';
if ($unread)
{
	$post->markRead($user);
}

$card = GDT_Card::make("post_$id")->gdo($post)->addClass('forum-post')->addClass($readClass);
$actions = $card->actions();
if ($post->isPersisted())
{
	$actions->addField(GDT_EditButton::make()->href($post->hrefEdit())->writeable($post->canEdit($user)));
	$actions->addField(GDT_Button::make('btn_reply')->icon('reply')->href($post->hrefReply()));
	$actions->addField(GDT_Button::make('btn_quote')->icon('quote')->href($post->hrefQuote()));
	$actions->addField(GDT_LikeButton::make()->gdo($post));
}


$title = '';
if ($post->isFirstInThread())
{
	$title .= $post->getThread()->getTitle();
}
$title .= Time::displayDate($post->getCreated());
$card->titleRaw($title);

$attachment = $post->hasAttachment() ? $post->getAttachment() : '';
if ($attachment)
{
	$downloadButton = $attachment->isImageType() ?
		'' :
		GDT_Button::make()->icon('download')->href($post->hrefAttachment())->render();
	$attachment = <<<EOT
<hr/>
<div class="gdo-attachment" layout="row" flex layout-fill layout-align="left center">
  <div>{$downloadButton}</div>
  <div>{$post->gdoColumn('post_attachment')->previewHREF($post->hrefPreview())->renderHTML()}</div>
</div>
EOT;
}

$html = <<<EOT
{$post->displayMessage()}
{$attachment}
{$post->displaySignature()}
EOT;

$card->editorFooter();

$card->addField(GDT_HTML::make()->var($html));

$cont = GDT_Container::make()->css('float', 'left')->addClass('post_from');
$user = $post->getCreator();
$numPosts = Module_Forum::instance()->userSettingVar($user, 'forum_posts');
$cont->addFields(
	GDT_ProfileLink::make()->nickname()->avatarUser($user),
	$user->gdoColumn('user_level')->iconNone(),
	GDT_UInt::make()->initial($numPosts)->label('num_posts')->icon('count'),
);
GDT_Hook::callHook('DecoratePostUser', $card, $cont, $user);
$card->image($cont);

echo $card->render();
