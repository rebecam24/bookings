<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Booking;

/**
 * @OA\Schema(
 *     schema="Booking",
 *     type="object",
 *     required={"id", "user_id", "place_id", "start_date", "end_date", "start_time", "end_time"},
 *     @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="place_id", type="integer", example=1),
 *     @OA\Property(property="start_date", type="string", format="date", example="2024-10-15"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2024-10-16"),
 *     @OA\Property(property="start_time", type="string", format="time", example="14:00:00"),
 *     @OA\Property(property="end_time", type="string", format="time", example="12:00:00"),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="event_name", type="string", example="XIV Culture Show"),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true),
 * )
 */
class BookingController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/bookings",
     *     summary="Get all bookings",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Bookings retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="bookings", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1, description="The unique identifier for the booking."),
     *                         @OA\Property(property="user_id", type="integer", example=3, description="The ID of the user who made the booking."),
     *                         @OA\Property(property="place_id", type="integer", example=1, description="The ID of the booked place."),
     *                         @OA\Property(property="event_name", type="string", example="XIV Culture Show", description="The name of the event for the booking."),
     *                         @OA\Property(property="start_date", type="string", format="date-time", example="2024-10-15 00:00:00", description="The start date and time of the booking."),
     *                         @OA\Property(property="end_date", type="string", format="date-time", example="2024-10-15 00:00:00", description="The end date and time of the booking."),
     *                         @OA\Property(property="start_time", type="string", format="time", example="14:00:00", description="The start time of the booking."),
     *                         @OA\Property(property="end_time", type="string", format="time", example="15:00:00", description="The end time of the booking."),
     *                         @OA\Property(property="status", type="string", example="booked", description="The status of the booking."),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-14T01:26:15.000000Z", description="The date and time the booking was created."),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-14T01:26:15.000000Z", description="The date and time the booking was last updated.")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *     )
     * )
     */
    public function index()
    {   $user = Auth::user();
        try {
            
            $bookings = $user->bookings;
            
            return $this->sendResponse(['bookings' => $bookings], 200);

        } catch (\Throwable $th) {
            return $this->sendError(['error' => $th->getMessage()], 500);
        } 
    }

    /**
     * @OA\Post(
     *     path="/bookings",
     *     summary="Create a booking",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"place_id", "start_date", "end_date", "start_time", "end_time", "event_name"},
     *             @OA\Property(property="place_id", type="integer", example=1),
     *             @OA\Property(property="start_date", type="string", format="date", example="2024-10-15"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2024-10-16"),
     *             @OA\Property(property="start_time", type="string", format="time", example="14:00"),
     *             @OA\Property(property="end_time", type="string", format="time", example="12:00"),
     *             @OA\Property(property="event_name", type="string", example="XIV Culture Show"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Booking created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true, description="Indicates whether the booking was created successfully."),
     *             @OA\Property(property="message", type="string", example="Booking created successfully.", description="A message indicating the result of the operation."),
     *             @OA\Property(property="booking", type="object",
     *                 @OA\Property(property="id", type="integer", example=2, description="The unique identifier for the booking."),
     *                 @OA\Property(property="place_id", type="integer", example=1, description="The ID of the booked place."),
     *                 @OA\Property(property="user_id", type="integer", example=2, description="The ID of the user who made the booking."),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2024-10-15", description="The start date of the booking."),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2024-10-15", description="The end date of the booking."),
     *                 @OA\Property(property="start_time", type="string", format="time", example="10:00", description="The start time of the booking."),
     *                 @OA\Property(property="end_time", type="string", format="time", example="12:00", description="The end time of the booking."),
     *                 @OA\Property(property="event_name", type="string", example="Conference", description="The name of the event for the booking."),
     *                 @OA\Property(property="status", type="string", example="booked", description="The status of the booking."),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-14T01:35:05.000000Z", description="The date and time the booking was created."),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-14T01:35:05.000000Z", description="The date and time the booking was last updated."),
     *                 @OA\Property(property="place", type="object",
     *                     @OA\Property(property="id", type="integer", example=1, description="The ID of the place."),
     *                     @OA\Property(property="name", type="string", example="Main Auditorium", description="The name of the place."),
     *                     @OA\Property(property="description", type="string", example="A large auditorium suitable for conferences and big events.", description="The description of the place."),
     *                     @OA\Property(property="capacity", type="integer", example=300, description="The maximum capacity of the place."),
     *                     @OA\Property(property="type", type="string", example="auditorio", description="The type of place (e.g., auditorium, conference room)."),
     *                     @OA\Property(property="default_hours", type="string", example="08:00-18:00", description="The default operational hours of the place."),
     *                     @OA\Property(property="default_days", type="array", @OA\Items(type="string"), example={"Monday", "Tuesday", "Wednesday", "Thursday", "Friday"}, description="The default operational days of the place.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'place_id' => 'required|exists:places,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'event_name' => 'required|string|max:255',
        ]);

        try {
            $overlapping = Booking::where('place_id', $validated['place_id'])
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($validated) {
                    $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                        ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                        ->orWhere(function ($q) use ($validated) {
                            $q->where('start_date', '<=', $validated['start_date'])
                                ->where('end_date', '>=', $validated['end_date']);
                        });
                })
                ->where(function ($query) use ($validated) {
                    $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                        ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                        ->orWhere(function ($q) use ($validated) {
                            $q->where('start_time', '<=', $validated['start_time'])
                                ->where('end_time', '>=', $validated['end_time']);
                        });
                })
                ->exists();
        
            if ($overlapping) {
                return $this->sendError(['message' => 'The selected time slot is already booked.'], 422);
            }
        
            $booking = new Booking($validated);
            $booking->user_id = $user->id; 
            $booking->status = 'booked'; 
            $booking->save(); 

           
            $booking->load('place');
            return $this->sendResponse(['booking' => $booking, 'message' => 'Booking created successfully.'], 201);
            //return $this->sendResponse(['booking' => $booking->with('place'), 'message' => 'Booking created successfully.'], 201);
        
        } catch (\Throwable $th) {
            return $this->sendError(['message' => $th->getMessage()], 500);
        }

        
    }

    /**
     * @OA\Get(
     *     path="/bookings/{id}",
     *     summary="Get booking details",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID booking",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking details retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="booking", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=3),
     *                     @OA\Property(property="place_id", type="integer", example=1),
     *                     @OA\Property(property="event_name", type="string", example="XIV Culture Show"),
     *                     @OA\Property(property="start_date", type="string", format="date-time", example="2024-10-15 00:00:00"),
     *                     @OA\Property(property="end_date", type="string", format="date-time", example="2024-10-15 00:00:00"),
     *                     @OA\Property(property="start_time", type="string", format="time", example="14:00:00"),
     *                     @OA\Property(property="end_time", type="string", format="time", example="15:00:00"),
     *                     @OA\Property(property="status", type="string", example="booked"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-14T01:26:15.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-14T01:26:15.000000Z")
     *                 ),
     *                 @OA\Property(property="message", type="string", example="Booking details.")
     *             ),
     *             @OA\Property(property="message", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *     )
     * )
     */
    public function show($id)
    {
        $user = Auth::user();
        try {
            $booking = Booking::where('id', $id)->where('user_id', $user->id)->firstOrFail();
            
            return $this->sendResponse(['booking' => $booking, 'message' => 'Booking details.'], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError(['message' => 'Booking not found.'], 404);
        } catch (\Throwable $th) {
            return $this->sendError(['message' => $th->getMessage()], 500);
        }
        
    }

    /**
     * @OA\Put(
     *     path="/bookings/{id}",
     *     summary="Update booking details",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Booking ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="place_id", type="integer"),
     *             @OA\Property(property="start_date", type="string", format="date"),
     *             @OA\Property(property="end_date", type="string", format="date"),
     *             @OA\Property(property="start_time", type="string", format="time"),
     *             @OA\Property(property="end_time", type="string", format="time"),
     *             @OA\Property(property="event_name", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="booking", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=3),
     *                     @OA\Property(property="place_id", type="integer", example=1),
     *                     @OA\Property(property="event_name", type="string", example="XIV Culture Show"),
     *                     @OA\Property(property="start_date", type="string", format="date-time", example="2024-10-15 00:00:00"),
     *                     @OA\Property(property="end_date", type="string", format="date-time", example="2024-10-15 00:00:00"),
     *                     @OA\Property(property="start_time", type="string", format="time", example="14:00:00"),
     *                     @OA\Property(property="end_time", type="string", format="time", example="15:00:00"),
     *                     @OA\Property(property="status", type="string", example="booked"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-14T01:26:15.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-14T01:26:15.000000Z")
     *                 ),
     *                 @OA\Property(property="message", type="string", example="Booking details.")
     *             ),
     *             @OA\Property(property="message", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $booking = Booking::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'place_id' => 'nullable|required|exists:places,id',
            'start_date' => 'nullable|required|date',
            'end_date' => 'nullable|required|date|after_or_equal:start_date',
            'start_time' => 'nullable|required|date_format:H:i',
            'end_time' => 'nullable|required|date_format:H:i|after:start_time',
            'event_name' => 'nullable|max:255',
        ]);
        try {
            $overlapping = Booking::where('place_id', $validated['place_id'] ?? $booking->place_id)
            ->where('id', '!=', $id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($validated, $booking) {
                $query->whereBetween('start_date', [$validated['start_date'] ?? $booking->start_date, $validated['end_date'] ?? $booking->end_date])
                    ->orWhereBetween('end_date', [$validated['start_date'] ?? $booking->start_date, $validated['end_date'] ?? $booking->end_date])
                    ->orWhere(function ($q) use ($validated, $booking) {
                        $q->where('start_date', '<=', $validated['start_date'] ?? $booking->start_date)
                            ->where('end_date', '>=', $validated['end_date'] ?? $booking->end_date);
                    });
            })
            ->where(function ($query) use ($validated, $booking) {
                $query->whereBetween('start_time', [$validated['start_time'] ?? $booking->start_time, $validated['end_time'] ?? $booking->end_time])
                    ->orWhereBetween('end_time', [$validated['start_time'] ?? $booking->start_time, $validated['end_time'] ?? $booking->end_time])
                    ->orWhere(function ($q) use ($validated, $booking) {
                        $q->where('start_time', '<=', $validated['start_time'] ?? $booking->start_time)
                            ->where('end_time', '>=', $validated['end_time'] ?? $booking->end_time);
                    });
            })
            ->exists();

            if ($overlapping) {
                return $this->sendError(['message' => 'The selected time slot is already booked.'], 422);
            }

            $booking->status = 'booked';
            $booking->update($validated);
            return $this->sendResponse(['booking' => $booking, 'message' => 'Booking updated successfully.'], 200);
        } catch (\Throwable $th) {
            return $this->sendError(['message' => $th->getMessage()], 500);
        }

        
    }

    /**
     * @OA\Delete(
     *     path="/bookings/{id}",
     *     summary="Delete a booking",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Booking ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Booking deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */

    public function destroy($id)
    {
        $user = Auth::user();
        try {
            $booking = Booking::where('id', $id)->where('user_id', $user->id)->firstOrFail();
            $booking->delete();
            return $this->sendResponse(null, 204);
        } catch (\Throwable $th) {
            return $this->sendError(['message' => $th->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/bookings/{id}/cancel",
     *     summary="Cancell a booking",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Booking ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking cancelled successfully",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function cancel($id)
    {
        $user = Auth::user();

        try {
            $booking = Booking::where('id', $id)->where('user_id', $user->id)->firstOrFail();
            $booking->status = 'cancelled';
            $booking->save();

            return $this->sendResponse(['message' => 'Booking cancelled successfully.'], 200);
        } catch (\Throwable $th) {
            return $this->sendError(['message' => $th->getMessage()], 500);
        }
    }

}