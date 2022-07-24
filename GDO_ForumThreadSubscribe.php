<?php
namespace GDO\Forum;

use GDO\Core\GDO;
use GDO\Core\GDT_Object;
use GDO\User\GDT_User;
use GDO\User\GDO_User;

final class GDO_ForumThreadSubscribe extends GDO
{
    public function gdoCached() : bool { return false; }
    public function gdoColumns() : array
    {
        return [
            GDT_User::make('subscribe_user')->primary(),
            GDT_Object::make('subscribe_thread')->table(GDO_ForumThread::table())->primary(),
        ];
    }

    public function getUser() : GDO_User { return $this->gdoValue('subscribe_user'); }
    public function getUserID() : string { return $this->gdoVar('subscribe_user'); }
    
    public function gdoAfterCreate(GDO $gdo) : void
    {
        $this->getUser()->tempUnset('gdo_forum_board_subsciptions');
    }
}
