<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Admin\MethodAdmin;
use GDO\UI\GDT_Bar;
use GDO\UI\GDT_Page;
use GDO\UI\GDT_Link;

final class Admin extends Method
{
    use MethodAdmin;
    
    public function adminTabs()
    {
        return GDT_Bar::makeWith(
            GDT_Link::make('link_forum_repair')->text('mt_forum_repair')->href(href('Forum', 'Repair'))
        );
    }
    
    public function onRenderTabs() : void
    {
        $this->renderNavBar();
        GDT_Page::$INSTANCE->topTabs->addField($this->adminTabs());
    }
    
    public function execute()
    {
        
    }
    
}
