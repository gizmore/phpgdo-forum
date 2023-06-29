<?php
namespace GDO\Forum;

use GDO\Core\GDT_ObjectSelect;
use GDO\User\GDO_User;

/**
 * A forum thread.
 *
 * @version 7.0.1
 * @author gizmore
 */
final class GDT_ForumThread extends GDT_ObjectSelect
{

	public bool $withEditPermissions = false;

	protected function __construct()
	{
		parent::__construct();
		$this->table(GDO_ForumThread::table());
	}

	public function gdtDefaultLabel(): ?string
	{
		return 'thread';
	}

	########################
	### Edit permissions ###
	########################

	public function validate(int|float|string|array|null|object|bool $value): bool
	{
		if (parent::validate($value))
		{
			if ($value !== null)
			{
				if ($this->withEditPermissions())
				{
					return $this->validateEditPermissions($value);
				}
			}
		}
		return false;
	}

	public function withEditPermissions(bool $withEditPermissions = true): self
	{
		$this->withEditPermissions = false;
		return $this;
	}

	protected function validateEditPermissions(GDO_ForumThread $thread): bool
	{
		return $thread->canEdit(GDO_User::current()) ?
			true :
			$this->error('err_permission_update');
	}

	################
	### Validate ###
	################

	public function getThread(): GDO_ForumThread
	{
		return $this->getValue();
	}

}
