<?php
namespace GDO\Forum;

use GDO\Core\GDO;
use GDO\Core\GDT_UInt;
use GDO\User\GDT_User;
use GDO\User\GDO_User;
use GDO\Core\GDT_Index;
use GDO\Core\GDT_Join;
use GDO\User\GDO_Permission;
use GDO\DB\Database;
use GDO\User\GDO_UserPermission;
use GDO\Core\GDT_Hook;

/**
 * When a post is created, entries will be made for all users.
 * @author gizmore
 */
final class GDO_ForumUnread extends GDO
{
    ###########
    ### GDO ###
    ###########
    public function gdoEngine() : string { return self::MYISAM; } # Faster inserts
    public function gdoCached() : bool { return false; } # No L1/L2 cache
    public function gdoColumns() : array
    {
        return [
            GDT_User::make('unread_user')->primary(),
            GDT_ForumPost::make('unread_post')->primary()->cascade(),
            GDT_Index::make('unread_user_index')->indexColumns('unread_user'),
            GDT_Join::make('unread_post_thread')->joinRaw("gdo_forumthread AS ft ON ft.thread_id=post_thread"),
            GDT_Join::make('unread_post_board')->joinRaw("gdo_forumboard AS fb ON ft.thread_board=fb.board_id"),
        ];
    }
    
    ################
    ### MarkRead ###
    ################
    public static function markRead(GDO_User $user, GDO_ForumPost $post)
    {
        self::table()->
            deleteWhere("unread_user={$user->getID()} AND unread_post={$post->getID()}");
    }
    
    public static function markUnread(GDO_ForumPost $post)
    {
        $thread = $post->getThread();
        $perm = $thread->getBoard()->getPermissionID();
        
        if ($perm)
        {
            $query = GDO_UserPermission::table()->select('user_id')->
            joinObject('perm_user_id')->
            where("perm_perm_id={$perm}");
        }
        else
        {
            $query = GDO_User::table()->select()->
            where('user_type IN ("guest", "member")');
        }
        $query->where("user_id != {$post->getCreatorID()}");
        
        $bulkSize = 250;
        $postID = $post->getID();
        $users = $query->exec();
        $fields = [GDT_UInt::make('unread_user'), GDT_UInt::make('unread_post')];
        $bulk = [];
        while ($userID = $users->fetchValue())
        {
            $bulk[] = [$userID, $postID];
            
            foreach (GDO_User::table()->cache->cache as $user)
            {
                $user->tempUnset('forum_unread');
                $user->recacheMemcached();
            }
            
            if (count($bulk) >= 100)
            {
                self::table()->bulkInsert($fields, $bulk, $bulkSize);
                $bulk = [];
            }
        }
        if (count($bulk))
        {
            self::table()->bulkInsert($fields, $bulk, $bulkSize);
        }
        
        GDT_Hook::callWithIPC('ForumActivity', $thread, $post);
    }
    
    public static function countUnread(GDO_User $user)
    {
        if (null === ($unread = $user->tempGet('forum_unread')))
        {
            $unread = self::table()->countWhere("unread_user={$user->getID()}");
            $user->tempSet('forum_unread', $unread);
            $user->recache();
        }
        return $unread;
    }
    
    public static function isThreadUnread(GDO_User $user, GDO_ForumThread $thread)
    {
        return
            self::table()->select('COUNT(*)')->
            joinObject('unread_post')->
            joinObject('unread_post_thread')->
            where("thread_id={$thread->getID()}")->
            where("unread_user={$user->getID()}")->
            exec()->fetchValue();
    }
    
    public static function isBoardUnread(GDO_User $user, GDO_ForumBoard $board)
    {
        return
        self::table()->select('COUNT(*)')->
        joinObject('unread_post')->
        joinObject('unread_post_thread')->
        joinObject('unread_post_board')->
        where("board_left BETWEEN {$board->getLeft()} AND {$board->getRight()}")->
        where("unread_user={$user->getID()}")->
        exec()->fetchValue();
    }

    public static function isPostUnread(GDO_User $user, GDO_ForumPost $post)
    {
        return self::getById($user->getID(), $post->getID());
    }
    
    /**
     * Mark new available posts as unread for a user.
     * @param GDO_User $user
     * @param GDO_Permission $permission
     */
    public static function markUnreadForPermission(GDO_User $user, GDO_Permission $permission)
    {
        $query =
            "REPLACE INTO gdo_forumunread SELECT {$user->getID()}, post_id FROM gdo_forumpost ".
            "JOIN gdo_forumthread ft ON post_thread=thread_id ".
            "JOIN gdo_forumboard fb ON thread_board=board_id ".
            "WHERE (board_permission={$permission->getID()})";
        return Database::instance()->queryWrite($query);
    }
    
}
