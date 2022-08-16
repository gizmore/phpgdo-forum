<?php
namespace GDO\Forum\Method;

use GDO\Core\GDO;
use GDO\Form\GDT_Form;
use GDO\Form\MethodCrud;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDT_ForumBoard;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;
use GDO\Util\Common;
use GDO\User\GDT_Permission;

final class CRUDBoard extends MethodCrud
{
    public function gdoTable() : GDO { return GDO_ForumBoard::table(); }
    public function hrefList() : string { return href('Forum', 'Boards', '&board='.Common::getRequestInt('board')); }
   
    public function canCreate(GDO $gdo) { return GDO_User::current()->isStaff(); }
    public function canUpdate(GDO $gdo) { return GDO_User::current()->isStaff(); }
    public function canDelete(GDO $gdo) { return GDO_User::current()->isAdmin(); }
    
    public function beforeExecute() : void
    {
        Module_Forum::instance()->renderTabs();
    }
    
    public function createForm(GDT_Form $form) : void
    {
        $gdo = GDO_ForumBoard::table();
        
        $parentId = Common::getRequestString('board', $this->gdo ? $this->gdo->getParentID() : 1);
        $boardId = $this->gdo ? $this->gdo->getID() : 0;
        
        $form->addFields(array(
            $gdo->gdoColumn('board_title'),
            $gdo->gdoColumn('board_sort'),
            $gdo->gdoColumn('board_description'),
            GDT_ForumBoard::make('board_parent')->label('parent')->notNull()->initial($parentId)->writable($boardId != 1),
            GDT_Permission::make('board_permission')->emptyInitial(t('sel_no_permissions')),
            $gdo->gdoColumn('board_allow_threads'),
            $gdo->gdoColumn('board_sticky'),
            $gdo->gdoColumn('board_image')->previewHREF(href('Forum', 'BoardImage', '&board='.$boardId.'&id={id}')),
        ));
        
        $this->createFormButtons($form);
    }

    public function afterCreate(GDT_Form $form, GDO $gdo)
    {
        $this->afterUpdate($form, $gdo);
    }
    
    public function afterUpdate(GDT_Form $form, GDO $gdo)
    {
        GDO_ForumBoard::table()->clearCache();
        $gdo->recache();
    }
    
}
