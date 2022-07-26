<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Admin\MethodAdmin;
use GDO\UI\GDT_Bar;
use GDO\UI\GDT_Page;
use GDO\UI\GDT_Link;

/**
 * Forum admin dashboard.
 * 
 * @author gizmore
 * @version 7.0.1
 * @since 3.2.0
 */
final class Admin extends Method
{
    use MethodAdmin;
    
    public function getMethodTitle() : string
    {
    	return t('admin');
    }
    
    public function adminTabs()
    {
        return GDT_Bar::makeWith(
            GDT_Link::make('link_forum_repair')->text('mt_forum_repair')->href(href('Forum', 'Repair'))
        );
    }
    
    public function onRenderTabs() : void
    {
        $this->renderNavBar();
        GDT_Page::instance()->topBar()->addField(
        	$this->adminTabs());
    }
    
    public function execute()
    {
        
    }
    
}
