<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\File\Method\GetFile;
use GDO\Forum\GDO_ForumPost;
use GDO\Forum\GDT_ForumPost;
use GDO\User\GDO_User;

/**
 * Download a post attachment.
 *
 * @version 6.05
 * @since 3.00
 * @author gizmore
 */
final class DownloadAttachment extends Method
{

	public function isSavingLastUrl(): bool { return false; }

	public function getMethodTitle(): string
	{
		return t('download');
	}

	public function gdoParameters(): array
	{
		return [
			GDT_ForumPost::make('post')->notNull(),
		];
	}

	public function execute()
	{
		$user = GDO_User::current();
//         $table = GDO_ForumPost::table();
		$post = $this->getPost();
		if (!$post->canView($user))
		{
			return $this->error('err_permission_read');
		}
		if (!$post->hasAttachment())
		{
			return $this->error('err_post_has_no_attachment');
		}

		return GetFile::make()->executeWithId($post->getAttachmentID());
	}

	public function getPost(): GDO_ForumPost
	{
		return $this->gdoParameterValue('post');
	}

}
