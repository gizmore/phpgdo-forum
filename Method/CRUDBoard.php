<?php
namespace GDO\Forum\Method;

use GDO\Core\GDO;
use GDO\Form\GDT_Form;
use GDO\Form\MethodCrud;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDT_ForumBoard;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;
use GDO\User\GDT_Permission;
use GDO\Util\Common;

final class CRUDBoard extends MethodCrud
{

	public function gdoTable(): GDO { return GDO_ForumBoard::table(); }

	public function hrefList(): string { return href('Forum', 'Boards', '&id=' . Common::getRequestInt('board')); }

	public function canCreate(GDO $table): bool { return GDO_User::current()->isStaff(); }

	public function canUpdate(GDO $gdo): bool { return GDO_User::current()->isStaff(); }

	public function canDelete(GDO $gdo): bool { return GDO_User::current()->isAdmin(); }

	public function onRenderTabs(): void
	{
		Module_Forum::instance()->renderTabs();
	}

	public function gdoParameters(): array
	{
		return array_merge(parent::gdoParameters(), [
			GDT_ForumBoard::make('board'),
		]);
	}

	protected function createForm(GDT_Form $form): void
	{
		$gdo = GDO_ForumBoard::table();

		$boardId = 0;
		$parentId = $this->gdoParameterVar('board');
		if (isset($this->gdo))
		{
			$gdo = $this->gdo;
			$boardId = $this->gdo->getID();
			$parentId = $parentId ? $parentId : $this->gdo->getParentID();
		}
		$parentId = $parentId ? $parentId : '0';

		$form->addFields(
			$gdo->gdoColumn('board_title'),
			$gdo->gdoColumn('board_sort'),
			$gdo->gdoColumn('board_description'),
			GDT_ForumBoard::make('board_parent')->label('parent')->notNull($parentId !== '0')->initial($parentId)->writeable($boardId != 1),
			GDT_Permission::make('board_permission')->emptyInitial(t('sel_no_permissions')),
			$gdo->gdoColumn('board_allow_threads'),
			$gdo->gdoColumn('board_sticky'),
			$gdo->gdoColumn('board_image')->previewHREF(href('Forum', 'BoardImage', '&board=' . $boardId . '&id={id}')),
		);

		$this->createFormButtons($form);
	}

	public function afterCreate(GDT_Form $form, GDO $gdo): void
	{
		$this->afterUpdate($form, $gdo);
	}

	public function afterUpdate(GDT_Form $form, GDO $gdo): void
	{
		GDO_ForumBoard::table()->clearCache();
	}

}
