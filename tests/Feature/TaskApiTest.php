<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving all tasks.
     */
    public function test_can_get_all_tasks(): void
    {
        // Create some test tasks
        Task::factory()->count(3)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test creating a new task.
     */
    public function test_can_create_task(): void
    {
        $taskData = [
            'name' => 'Test Task',
            'description' => 'This is a test task description with at least 10 characters',
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Test Task',
                'description' => 'This is a test task description with at least 10 characters',
            ]);

        $this->assertDatabaseHas('tasks', [
            'name' => 'Test Task',
            'description' => 'This is a test task description with at least 10 characters',
        ]);

        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'description', 'created_at', 'updated_at',
                'edit_url', 'delete_url',
            ],
        ]);
    }

    /**
     * Test validation when creating a task.
     */
    public function test_task_creation_validation(): void
    {
        // Test with invalid data (name too short, description too short)
        $invalidData = [
            'name' => 'Te',
            'description' => 'Too short',
        ];

        $response = $this->postJson('/api/tasks', $invalidData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description']);
    }

    /**
     * Test updating a task with secure token.
     */
    public function test_can_update_task_with_token(): void
    {
        // Create a task
        $task = Task::factory()->create();

        $updateData = [
            'name' => 'Updated Task Name',
            'description' => 'This is the updated description for the task',
        ];

        // Get the signed URL path
        $url = parse_url($task->edit_url, PHP_URL_PATH);
        $query = parse_url($task->edit_url, PHP_URL_QUERY);
        $fullPath = $url . '?' . $query;

        // Make request to update the task using the signed URL
        $response = $this->putJson($fullPath, $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Task Name',
                'description' => 'This is the updated description for the task',
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'name' => 'Updated Task Name',
            'description' => 'This is the updated description for the task',
        ]);
    }

    /**
     * Test updating a task with invalid token.
     */
    public function test_cannot_update_task_with_invalid_token(): void
    {
        $task = Task::factory()->create();

        $updateData = [
            'name' => 'Updated Task Name',
            'description' => 'This is the updated description for the task',
        ];

        $response = $this->putJson("/api/tasks/{$task->id}/edit/invalid-token", $updateData);

        $response->assertStatus(403);
    }

    /**
     * Test deleting a task with secure token.
     */
    public function test_can_delete_task_with_token(): void
    {
        $task = Task::factory()->create();

        $url = parse_url($task->delete_url, PHP_URL_PATH);
        $query = parse_url($task->delete_url, PHP_URL_QUERY);
        $fullPath = $url . '?' . $query;

        $response = $this->deleteJson($fullPath);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Task deleted successfully',
            ]);

        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }

    /**
     * Test deleting a task with invalid token.
     */
    public function test_cannot_delete_task_with_invalid_token(): void
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}/delete/invalid-token");

        $response->assertStatus(403);
    }

    /**
     * Test that soft deleted tasks are not returned in the main list.
     */
    public function test_soft_deleted_tasks_are_not_returned(): void
    {
        $task = Task::factory()->create();
        $task->delete();

        Task::factory()->create();

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
