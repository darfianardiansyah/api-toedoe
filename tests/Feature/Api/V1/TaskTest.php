<?php

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_list_of_tasks()
    {
        // Arrange: Create 2 fake tasks
        $tasks = Task::factory()->count(2)->create();
        // Act: Get all tasks
        $response = $this->getJson('/api/v1/tasks');
        // Assert: Assert that the response is successful
        $response->assertOk();
        // Assert: Assert that the response has the expected tasks
        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure([
            'data' => [['id', 'name', 'is_completed']],
        ]);
    }
    public function test_user_can_get_single_task()
    {
        // Arrange: Create a fake tasks
        $task = Task::factory()->create();
        // Act: Get all tasks
        $response = $this->getJson('/api/v1/tasks/' . $task->id);
        // Assert: Assert that the response is successful
        $response->assertOk();
        // Assert: Assert that the response has the expected tasks
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'is_completed'],
        ]);
        $response->assertJson([
            'data' => [
                'id' => $task->id,
                'name' => $task->name,
                'is_completed' => $task->is_completed
            ]
            ]);
    }

}
