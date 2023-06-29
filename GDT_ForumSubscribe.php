<?php
namespace GDO\Forum;

use GDO\Core\GDT_Enum;

/**
 * Forum subscription mode.
 *
 * @version 7.0.1
 * @since 6.0.0
 * @author gizmore
 */
final class GDT_ForumSubscribe extends GDT_Enum
{

	public const NONE = 'fsub_none';
	public const OWN = 'fsub_own';
	public const ALL = 'fsub_all';

	protected function __construct()
	{
		$this->enumValues(self::NONE, self::OWN, self::ALL);
	}

	public function gdtDefaultLabel(): ?string
    { return 'forum_subscription_mode'; }

}
