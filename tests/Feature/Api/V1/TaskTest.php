<?php

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_list_of_tasks(): void
    {
        // Arrange: Buat 2 data task menggunakan factory
        $tasks = Task::factory()->count(2)->create();

        // Act: Panggil endpoint untuk mendapatkan semua task
        $response = $this->getJson('/api/v1/tasks');

        // Assert: Pastikan response berhasil (HTTP 200)
        $response->assertOk();

        // Assert: Pastikan jumlah data dalam response adalah 2
        $response->assertJsonCount(2, 'data');

        // Assert: Pastikan struktur JSON sesuai dengan yang diharapkan
        $response->assertJsonStructure([
            'data' => [['id', 'name', 'is_completed']],
        ]);
    }

    public function test_user_can_get_single_task(): void
    {
        // Arrange: Buat 1 data task
        $task = Task::factory()->create();

        // Act: Panggil endpoint untuk mendapatkan detail task berdasarkan ID
        $response = $this->getJson('/api/v1/tasks/' . $task->id);

        // Assert: Pastikan response berhasil (HTTP 200)
        $response->assertOk();

        // Assert: Pastikan struktur JSON sesuai
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'is_completed'],
        ]);

        // Assert: Pastikan data task yang dikembalikan sama dengan yang dibuat
        $response->assertJson([
            'data' => [
                'id' => $task->id,
                'name' => $task->name,
                'is_completed' => $task->is_completed
            ]
        ]);
    }

    // 'POST /tasks' -> create a new task
    public function test_user_can_create_a_new_task(): void
    {
        // Act: Kirim request POST untuk membuat task baru
        $response = $this->postJson('/api/v1/tasks', [
            'name' => 'New Task',
        ]);

        // Assert: Pastikan response HTTP 201 Created
        $response->assertCreated();

        // Assert: Pastikan struktur JSON sesuai
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'is_completed'],
        ]);

        // Assert: Pastikan data tersimpan di database
        $this->assertDatabaseHas('tasks', [
            'name' => 'New Task',
        ]);
    }

    public function test_user_cannot_create_invalid_task(): void
    {
        // Act: Kirim data tidak valid (name kosong)
        $response = $this->postJson('/api/v1/tasks', [
            'name' => '',
        ]);

        // Assert: Pastikan response HTTP 422 (Unprocessable Entity)
        $response->assertStatus(422);

        // Assert: Pastikan field 'name' menghasilkan error validasi
        $response->assertJsonValidationErrors(['name']);
    }

    // 'PUT /tasks/{id}' -> update existing task
    public function test_user_can_update_task(): void
    {
        // Arrange: Buat 1 task
        $task = Task::factory()->create();

        // Act: Kirim PUT request untuk mengubah nama task
        $response = $this->putJson('/api/v1/tasks/' . $task->id, [
            'name' => 'Updated Task',
        ]);

        // Assert: Pastikan response sukses (HTTP 200)
        $response->assertOk();

        // Assert: Pastikan data dalam response sudah berubah
        $response->assertJsonFragment([
            'name' => 'Updated Task',
        ]);
    }

    public function test_user_cannot_update_with_invalid_data(): void
    {
        // Arrange: Buat 1 task
        $task = Task::factory()->create();

        // Act: Kirim PUT request dengan data invalid (name kosong)
        $response = $this->putJson('/api/v1/tasks/' . $task->id, [
            'name' => '',
        ]);

        // Assert: Harus gagal validasi dengan kode 422
        $response->assertStatus(422);

        // Assert: Field 'name' harus mengandung error validasi
        $response->assertJsonValidationErrors(['name']);
    }

    // 'PATCH /tasks/{id}/complete' -> mark the task as completed or incomplete
    public function test_user_can_toggle_task_completion(): void
    {
        // Arrange: Buat task dengan status belum selesai
        $task = Task::factory()->create([
            'is_completed' => false,
        ]);

        // Act: Ubah status is_completed menjadi true
        $response = $this->patchJson('/api/v1/tasks/' . $task->id . '/complete', [
            'is_completed' => true,
        ]);

        // Assert: Pastikan response sukses (HTTP 200)
        $response->assertOk();

        // Assert: Data dalam response harus sesuai (is_completed = true)
        $response->assertJsonFragment([
            'is_completed' => true,
        ]);
    }

    public function test_user_cannot_toggle_completed_with_invalid_data(): void
    {
        // Arrange: Buat 1 task
        $task = Task::factory()->create();

        // Act: Kirim data yang tidak valid untuk is_completed
        $response = $this->patchJson('/api/v1/tasks/' . $task->id . '/complete', [
            'is_completed' => 'yes', // seharusnya boolean
        ]);

        // Assert: Harus gagal validasi (HTTP 422)
        $response->assertStatus(422);

        // Assert: Error validasi muncul pada field 'is_completed'
        $response->assertJsonValidationErrors(['is_completed']);
    }

    // 'DELETE /tasks/{id}' -> delete a task
    public function test_user_can_delete_task(): void
    {
        // Arrange: Buat 1 task
        $task = Task::factory()->create();

        // Act: Hapus task tersebut
        $response = $this->deleteJson('/api/v1/tasks/' . $task->id);

        // Assert: Response tidak mengandung konten (HTTP 204)
        $response->assertNoContent();

        // Assert: Task sudah tidak ada di database
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
}
