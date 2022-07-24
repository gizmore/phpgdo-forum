<?php
namespace GDO\Forum;

use GDO\Core\GDT_Enum;

/**
 * Forum subscription mode.
 * 
 * @author gizmore
 * @version 7.0.1
 * @since 6.0.0
 */
final class GDT_ForumSubscribe extends GDT_Enum
{
    const NONE = 'fsub_none';
    const OWN = 'fsub_own';
    const ALL = 'fsub_all';
    
    public function defaultLabel() : self { return $this->label('forum_subscription_mode'); }
    
    protected function __construct()
    {
        $this->enumValues(self::NONE, self::OWN, self::ALL);
    }
    
}
