<?php
namespace GDO\Forum\Test;

use GDO\Forum\Method\CRUDBoard;
use GDO\Tests\GDT_MethodTest;
use GDO\Tests\TestCase;
use function PHPUnit\Framework\assertTrue;
use GDO\Forum\GDO_ForumBoard;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use GDO\Forum\Method\Boards;
use GDO\Forum\Method\CreateThread;
use GDO\Forum\GDO_ForumThread;
use function PHPUnit\Framework\assertStringContainsString;

final class ForumTest extends TestCase
{
    public function testBoardCreation()
    {
        $this->userGizmore();
        $p = [
            'board_title' => 'Test Board 2',
            'board_description' => 'Beschreibung Test Board 2',
            'board_parent' => '1',
            'board_allow_threads' => '1',
            'create' => 'create',
        ];
        GDT_MethodTest::make()->method(CRUDBoard::make())->parameters($p)->execute();
        $this->assert200("Check if Forum::CRUDBoard has easy to spot errors.");
        
        $p = [
            'board_title' => 'Test Board 3',
            'board_description' => 'Beschreibung Test Board 3',
            'board_parent' => '2',
            'board_allow_threads' => '1',
            'create' => 'create',
        ];
        GDT_MethodTest::make()->method(CRUDBoard::make())->parameters($p)->execute();
        $this->assert200("Check if Forum::CRUDBoard has easy to spot errors.");
        
        assertEquals(3, GDO_ForumBoard::table()->countWhere(), 'Check if 3 forum boards were created.');
    }
    
    public function testThreadCreation()
    {
        # Look at boards again to make sure we are not in a deadloop.
        $this->callMethod(Boards::make());
        $gp = [
            'board' => '3',
        ];
        $p = [
            'thread_title' => 'Test Thread 1',
            'post_message' => '<p>Test Thread Message 1</p>',
        ];
        GDT_MethodTest::make()->method(CreateThread::make())->getParameters($gp)->parameters($p)->execute();
        $this->assert200("Check if CreateThread results in code 200.");
        $threads = GDO_ForumThread::table()->all();
        assertCount(1, $threads, 'Check if we have 1 thread');
        assertStringContainsString("Thread", $threads[1]->getTitle(), 'check if thread title is set');
        
        $post = $threads[1]->getFirstPost();
        $message = $post->displayMessage();
        assertStringContainsString("Thread", $message, 'check if post message is set.');
        assertStringContainsString("<div", $message, 'check if post message display is output prerendered.');
        
        # Look at boards again to make sure we are not in a deadloop.
        $this->callMethod(Boards::make());
    }
    
}
