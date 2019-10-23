<?php

namespace Tests\Unit;

use App\Task;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function a_task_has_an_assigned_user(): void
    {
          $task = factory(Task::class)->create();

          $this->assertInstanceOf(User::class, $task->assignedUser);
    }

    /** @test */
    public function tasks_can_be_sorted_by_statuses(): void
    {
        $tasks = collect([
            ['Todo A', Task::TODO],
            ['Todo B', Task::DOING],
            ['Todo C', Task::DOING],
            ['Todo Ç', Task::DONE],
            ['Todo 05', Task::DONE],
            ['Todo 06', Task::TODO,],
            ['Todo 07', Task::TODO],
            ['Todo *', Task::DONE],
            ['Todo >', Task::DOING],
            ['Todo #', Task::DOING],
        ])->map(function ($task) {
            return factory(Task::class)->create(['title' => $task[0], 'status' => $task[1]]);
        });

        $this->assertEquals(
            Task::sortByStatus()->pluck('title')->toArray(),
            [
                'Todo #',   // DOING
                'Todo >',   // DOING
                'Todo B',   // DOING
                'Todo C',   // DOING
                'Todo 06',  // TODO
                'Todo 07',  // TODO
                'Todo A',   // TODO
                'Todo *',   // DONE
                'Todo 05',  // DONE
                'Todo Ç',   // DONE
            ]);
    }
}
