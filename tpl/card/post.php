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
$mod = Module_Forum::instance();

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
	$actions->addField(GDT_EditButton::make()->href($post->hrefEdit())->writeable($post->canEdit($user))->noFollow());
	$actions->addField(GDT_Button::make('btn_reply')->icon('reply')->href($post->hrefReply())->noFollow());
	$actions->addField(GDT_Button::make('btn_quote')->icon('quote')->href($post->hrefQuote())->noFollow());
	$actions->addField(GDT_LikeButton::make()->gdo($post));
}


$title = '';
if ($post->isFirstInThread())
{
	$title .= $post->getThread()->getTitle();
    $title .= ' ';
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
<div class="post-attachment">
  <div>{$downloadButton}</div>
  <div>{$post->gdoColumn('post_attachment')->previewHREF($post->hrefPreview())->renderHTML()}</div>
</div>
EOT;
}

$signature = $post->displaySignature();
$signature = $signature ? '<div class="post-signature">' . $signature . '</div>' : '';

$html = <<<EOT
<div class="post-message">
{$post->displayMessage()}
</div>
{$attachment}
{$signature}
EOT;
$card->editorFooter();
$card->addField(GDT_HTML::make()->var($html));
$cont = GDT_Container::make()->addClass('post_from');
$user = $post->getCreator();
$numPosts = Module_Forum::instance()->userSettingVar($user, 'forum_posts');
$cont->addField(GDT_ProfileLink::make()->gdo($user)->avatar()->nickname());
if ($mod->cfgUseLevel())
{
    $cont->addField($user->gdoColumn('user_level'));
}
$cont->addField(GDT_UInt::make()->initial($numPosts)->label('num_posts'));
GDT_Hook::callHook('DecoratePostUser', $card, $cont, $user);
$card->image($cont);

echo $card->renderCard();
