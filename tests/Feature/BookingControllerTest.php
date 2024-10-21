<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Booking;
use App\Models\Place;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;


class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_all_bookings_for_authenticated_user()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $place = Place::factory()->create();
        Booking::factory()->count(3)->create(['user_id' => $user->id, 'place_id' => $place->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->getJson('/api/bookings');

        $response->assertStatus(200)
                 ->assertJsonStructure(['bookings']);
    }

    /** @test */
    public function it_denies_access_to_bookings_if_unauthenticated()
    {
        $response = $this->getJson('/api/bookings');
        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }

    /** @test */
    public function it_creates_a_new_booking_successfully()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $place = Place::factory()->create();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->postJson('/api/bookings', [
                             'place_id' => $place->id,
                             'start_date' => '2024-10-15',
                             'end_date' => '2024-10-16',
                             'start_time' => '14:00',
                             'end_time' => '16:00',
                         ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['booking', 'message']);
    }

    /** @test */
    public function it_fails_to_create_booking_with_overlapping_times()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $place = Place::factory()->create();

        Booking::factory()->create([
            'user_id' => $user->id,
            'place_id' => $place->id,
            'start_date' => '2024-10-15',
            'end_date' => '2024-10-15',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'status' => 'booked',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->postJson('/api/bookings', [
                             'place_id' => $place->id,
                             'start_date' => '2024-10-15',
                             'end_date' => '2024-10-15',
                             'start_time' => '15:00',
                             'end_time' => '17:00',
                         ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'The selected time slot is already booked.']);
    }

    /** @test */
    public function it_shows_a_specific_booking()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $booking = Booking::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure(['booking', 'message']);
    }

    /** @test */
    public function it_updates_a_booking_successfully()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $booking = Booking::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->putJson("/api/bookings/{$booking->id}", [
                             'start_date' => '2024-10-18',
                             'end_date' => '2024-10-19',
                             'start_time' => '10:00',
                             'end_time' => '12:00',
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Booking updated.']);
    }

    /** @test */
    public function it_deletes_a_booking_successfully()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $booking = Booking::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(204);
    }

    /** @test */
    public function it_cancels_a_booking_successfully()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $booking = Booking::factory()->create(['user_id' => $user->id, 'status' => 'active']);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                        ->postJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Booking cancelled successfully.']);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function it_returns_404_when_cancelling_a_nonexistent_booking()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                        ->postJson("/api/bookings/999/cancel");

        $response->assertStatus(404)
                ->assertJson(['message' => 'Booking not found.']);
    }

    /** @test */
    public function it_returns_403_when_cancelling_a_booking_that_does_not_belong_to_the_user()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $booking = Booking::factory()->create(['user_id' => $anotherUser->id, 'status' => 'active']);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                        ->postJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(403)
                ->assertJson(['message' => 'You are not authorized to cancel this booking.']);
    }

    /** @test */
    public function it_releases_dates_and_times_when_booking_is_cancelled()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'place_id' => 1,
            'start_date' => '2024-10-15',
            'end_date' => '2024-10-16',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'status' => 'active'
        ]);
        
        $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson("/api/bookings/{$booking->id}/cancel")
            ->assertStatus(200)
            ->assertJson(['message' => 'Booking cancelled successfully.']);

        $newBookingData = [
            'place_id' => 1,
            'start_date' => '2024-10-15',
            'end_date' => '2024-10-16',
            'start_time' => '14:00',
            'end_time' => '16:00'
        ];

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                        ->postJson('/api/bookings', $newBookingData);

        $response->assertStatus(201)
                ->assertJson(['message' => 'Booking created successfully.']);
    }

}