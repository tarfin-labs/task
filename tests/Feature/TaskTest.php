<?php

namespace Tests\Feature;

use App\User;
use App\Task;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function an_authenticated_user_can_create_a_task(): void
    {
        Passport::actingAs(factory(User::class)->create());

        $attributes = factory(Task::class)->raw();

        $response = $this->postJson('api/tasks', $attributes);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['title', 'description', 'status', 'user_id'],
            ]);

        $this->assertDatabaseHas('tasks', [
            'title'       => $attributes['title'],
            'description' => $attributes['description'],
        ]);
    }

    /** @test */
    public function a_task_requires_a_title(): void
    {
        Passport::actingAs(factory(User::class)->create());

        $attributes = factory(Task::class)->raw([
            'title' => '',
        ]);

        $response = $this->postJson('api/tasks', $attributes);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->assertDatabaseMissing('tasks', [
            'title' => $attributes['title'],
        ]);
    }

    /** @test */
    public function an_authenticated_user_can_get_a_task(): void
    {
        Passport::actingAs(factory(User::class)->create());

        $task = factory(Task::class)->create();

        $response = $this->getJson("api/tasks/{$task->id}");

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['title', 'description', 'status', 'user_id'],
            ]);
    }

    /** @test */
    public function an_authenticated_user_can_list_tasks(): void
    {
        Passport::actingAs(factory(User::class)->create());

        factory(Task::class, $this->faker->numberBetween(3, 5))->create();

        $response = $this->getJson('api/tasks');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['title', 'description', 'status', 'user_id'],
                ],
            ]);
    }

    /** @test */
    public function an_authenticated_user_can_remove_a_task(): void
    {
        Passport::actingAs(factory(User::class)->create());

        $task = factory(Task::class)->create();

        $response = $this->deleteJson("api/tasks/{$task->id}");

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['title', 'description', 'status', 'user_id'],
            ]);

        $this->assertDatabaseMissing('tasks', [
            'title'       => $task->title,
            'description' => $task->description,
        ]);
    }

    /** @test */
    public function an_authenticated_user_can_update_a_task(): void
    {
        Passport::actingAs(factory(User::class)->create());

        $task = factory(Task::class)->create();
        $newAttributes = factory(Task::class)->raw();

        $response = $this->patchJson("api/tasks/{$task->id}", $newAttributes);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['title', 'description', 'status', 'user_id'],
            ]);

        $this->assertDatabaseHas('tasks', [
            'title'       => $newAttributes['title'],
            'description' => $newAttributes['description'],
        ]);
    }
}
