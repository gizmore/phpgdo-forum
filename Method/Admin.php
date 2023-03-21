<?php
namespace GDO\Forum\Method;

use GDO\Admin\MethodAdmin;
use GDO\Core\Method;
use GDO\UI\GDT_Bar;
use GDO\UI\GDT_Link;
use GDO\UI\GDT_Page;

/**
 * Forum admin dashboard.
 *
 * @version 7.0.1
 * @since 3.2.0
 * @author gizmore
 */
final class Admin extends Method
{

	use MethodAdmin;

	public function getMethodTitle(): string
	{
		return t('admin');
	}

	public function execute() {}

	public function onRenderTabs(): void
	{
		$this->renderAdminBar();
		GDT_Page::instance()->topResponse()->addField(
			$this->adminTabs());
	}

	public function adminTabs()
	{
		return GDT_Bar::makeWith(
			GDT_Link::make('link_forum_repair')->text('mt_forum_repair')->href(href('Forum', 'Repair'))
		);
	}

}
