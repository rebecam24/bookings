<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Place;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlaceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_requires_authentication_to_access_places()
    {
        $response = $this->getJson('/api/places');
        $response->assertStatus(401);
    }

    /** @test */
    public function admin_can_create_a_place_with_images()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user, 'api');

        $file1 = UploadedFile::fake()->image('image1.jpg');
        $file2 = UploadedFile::fake()->image('image2.jpg');

        $response = $this->postJson('/api/places', [
            'name' => 'Sala de Conferencia',
            'description' => 'Espacio amplio para conferencias.',
            'images' => [$file1, $file2],
            'capacity' => 100,
            'available_from' => '2024-10-01',
            'available_to' => '2024-12-31',
            'type' => 'salon',
            'default_days' => ['Monday', 'Tuesday'],
            'default_hours' => '09:00-18:00',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['space', 'message']);

        Storage::disk('public')->assertExists("images/places/{$file1->hashName()}");
        Storage::disk('public')->assertExists("images/places/{$file2->hashName()}");

        $this->assertDatabaseHas('places', ['name' => 'Sala de Conferencia']);
    }

    /** @test */
    public function non_admin_user_cannot_create_a_place()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'api');

        $response = $this->postJson('/api/places', [
            'name' => 'Sala de Reunión',
            'description' => 'Una sala para reuniones pequeñas.',
            'capacity' => 20,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_update_an_existing_place()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user, 'api');

        $place = Place::factory()->create();

        $response = $this->putJson("/api/places/{$place->id}", [
            'name' => 'Sala de Actualizada',
            'capacity' => 50,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Place updated successfully.']);

        $this->assertDatabaseHas('places', ['name' => 'Sala de Actualizada']);
    }

    /** @test */
    public function it_can_delete_a_place()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user, 'api');

        $place = Place::factory()->create();

        $response = $this->deleteJson("/api/places/{$place->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Place deleted successfully.']);

        $this->assertDatabaseMissing('places', ['id' => $place->id]);
    }

    /** @test */
    public function it_can_show_a_specific_place()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $place = Place::factory()->create();

        $response = $this->getJson("/api/places/{$place->id}");

        $response->assertStatus(200)
                 ->assertJson(['place' => ['id' => $place->id]]);
    }

    /** @test */
    public function it_can_filter_places_based_on_capacity_and_type()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $place1 = Place::factory()->create(['capacity' => 50, 'type' => 'salon']);
        $place2 = Place::factory()->create(['capacity' => 20, 'type' => 'sala de conferencia']);

        $response = $this->getJson('/api/places/filter?capacity=30&type=salon');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'places')
                 ->assertJsonFragment(['id' => $place1->id]);
    }

    /** @test */
    public function it_returns_no_places_found_message_when_no_places_exist()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $response = $this->getJson('/api/places');

        $response->assertStatus(200)
                 ->assertJson(['places' => [], 'message' => 'No places found.']);
    }
}