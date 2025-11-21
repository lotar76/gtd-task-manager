<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->user->assignRole('user');
        
        // Использование fake storage для тестов
        Storage::fake('s3');
    }

    public function test_authenticated_user_can_upload_file(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/files/upload', [
                'file' => $file,
                'directory' => 'test-uploads',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'path',
                    'url',
                    'filename',
                    'original_name',
                    'mime_type',
                    'size',
                ],
            ]);

        Storage::disk('s3')->assertExists($response->json('data.path'));
    }

    public function test_unauthenticated_user_cannot_upload_file(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson('/api/v1/files/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_file_upload_requires_file(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/files/upload', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_authenticated_user_can_get_file_info(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        Storage::disk('s3')->put('test/test.jpg', $file->getContent());

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/files/show?path=test/test.jpg');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'path',
                    'url',
                    'temporary_url',
                    'metadata',
                ],
            ]);
    }

    public function test_authenticated_user_can_delete_file(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        Storage::disk('s3')->put('test/test.jpg', $file->getContent());

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/files/delete', [
                'path' => 'test/test.jpg',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);

        Storage::disk('s3')->assertMissing('test/test.jpg');
    }
}

