<?php
namespace GDO\Forum;

use GDO\Core\GDT_ObjectSelect;
use GDO\User\GDO_User;

/**
 * A forum thread.
 * 
 * @author gizmore
 * @version 7.0.1
 */
final class GDT_ForumThread extends GDT_ObjectSelect
{
	public function defaultLabel(): static
	{
		return $this->label('thread');
	}
	
	protected function __construct()
	{
	    parent::__construct();
	    $this->table(GDO_ForumThread::table());
	}
	
	public function getThread() : GDO_ForumThread
	{
		return $this->getValue();
	}
	
	########################
	### Edit permissions ###
	########################
	public bool $withEditPermissions = false;
	public function withEditPermissions(bool $withEditPermissions = true): static
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
	public function validate($value): bool
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
	
}
