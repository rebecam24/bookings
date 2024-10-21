<?php

namespace App\Http\Controllers\API;

use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

    /**
     * @OA\Schema(
     *     schema="Place",
     *     type="object",
     *     required={"id", "name", "description", "capacity", "default_days", "default_hours"},
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Sala de Reuniones A"),
     *     @OA\Property(property="description", type="string", example="Una sala espaciosa para reuniones."),
     *     @OA\Property(property="capacity", type="integer", example=10),
     *     @OA\Property(property="default_days", type="array", @OA\Items(type="string", example="Monday")),
     *     @OA\Property(property="default_hours", type="string", example="09:00-17:00"),
     *     @OA\Property(property="created_at", type="string", format="date-time"),
     *     @OA\Property(property="updated_at", type="string", format="date-time")
     * )
     */ 
class PlaceController extends BaseController
{ 
    /**
     * @OA\Get(
     *     path="/places",
     *     tags={"Places"},
     *     summary="Get all places",
     *     description="Retrieve a list of all available places.",
     *     operationId="getAllPlaces",
     *     @OA\Response(
     *         response=200,
     *         description="Places obtained successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="places", type="array", @OA\Items(ref="#/components/schemas/Place")),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $user = Auth::user();
        if(!$user){
          return $this->sendError(['message' => 'Unauthorized.'], 401);  
        }
        try {
            $places = Place::all();

            if ($places->isEmpty()) {
                return $this->sendResponse(['places' => [], 'message' => 'No places found.'], 200);
            }

            return $this->sendResponse(['places' => $places, 'message' => 'Places obtained successfully.'], 200);

        } catch (\Throwable $th) {
            return $this->sendError(['error' => $th->getMessage()], 500);
        }        
    }

    /**
     * @OA\Post(
     *     path="/places",
     *     tags={"Places"},
     *     summary="Create a new place",
     *     description="Create a new place. Only accessible by admins.",
     *     operationId="createPlace",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Main Auditorium"),
     *             @OA\Property(property="description", type="string", example="A large conference hall."),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="capacity", type="integer", example=300),
     *             @OA\Property(property="available_from", type="string", format="date", example="2024-01-01"),
     *             @OA\Property(property="available_to", type="string", format="date", example="2024-12-31"),
     *             @OA\Property(property="type", type="string", example="auditorio"),
     *             @OA\Property(property="default_days", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="default_hours", type="string", example="09:00-17:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Place created successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="space", ref="#/components/schemas/Place"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized. Only admins can create places.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized. Only admins can create places.'], 403);
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'capacity' => 'required|integer|min:1',
                'available_from' => 'nullable|date',
                'available_to' => 'nullable|date|after_or_equal:available_from',
                'type' => 'nullable|in:salon,auditorio,sala de reunion,sala de conferencia',
                'default_days' => 'nullable|array',
                'default_days.*' => 'in:Lun,Mar,Mie,Jue,Vie',
                'default_hours' => 'nullable|string|regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/',
            ]);

            
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $imagePath = $image->storeAs('images/places', $imageName, 'public');
                    $imagePaths[] = $imagePath;
                }
            }

            $space = Place::create([
                'name' => $request->name,
                'description' => $request->description,
                'images' => json_encode($imagePaths), 
                'capacity' => $request->capacity,
                'available_from' => $request->available_from,
                'available_to' => $request->available_to,
                'type' => $request->type ?? 'salon',
                'default_days' => json_encode($request->default_days ?? ['Lun', 'Mar', 'Mie', 'Jue', 'Vie']),
                'default_hours' => $request->default_hours ?? '09:00-17:00',
            ]);

            return $this->sendResponse(['space' => $space, 'message' => 'Place created successfully.'], 201);
        } catch (\Exception $e) {
            return $this->sendError(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/places/{id}",
     *     tags={"Places"},
     *     summary="Get a specific place by ID",
     *     description="Retrieve detailed information of a specific place using its ID.",
     *     operationId="getPlaceById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the place to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Place retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="place", ref="#/components/schemas/Place"),
     *             @OA\Property(property="availability", type="object",
     *                 @OA\Property(property="days", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="hours", type="string")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Place not found.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show($id)
    {   $user = Auth::user();
        try {
            if(!$user){
                return $this->sendError(['message' => 'Unauthorized.'], 403);
            }else{
                $place = Place::findOrFail($id);
               
                return $this->sendResponse([
                    'place' => $place,
                    'availability' => [
                        'days' => $place->default_days,
                        'hours' => $place->default_hours,
                    ],
                    'message' => 'Place retrieved successfully.',
                ], 200);
            }
        }catch (ModelNotFoundException $e) {
            return $this->sendError(['message' => 'Place not found.'], 404);
        } catch (\Exception $e) {
            return $this->sendError(['message' => $e->getMessage()], 500);
        }
        
    }
    
    /**
     * @OA\Put(
     *     path="/places/{id}",
     *     tags={"Places"},
     *     summary="Update a specific place by ID",
     *     description="Update the details of a specific place. Only admins can perform this action.",
     *     operationId="updatePlace",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the place to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Auditorium"),
     *             @OA\Property(property="description", type="string", example="An updated description."),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="capacity", type="integer", example=500),
     *             @OA\Property(property="available_from", type="string", format="date", example="2024-02-01"),
     *             @OA\Property(property="available_to", type="string", format="date", example="2024-11-30"),
     *             @OA\Property(property="type", type="string", example="sala de conferencia"),
     *             @OA\Property(property="default_days", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="default_hours", type="string", example="08:00-18:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Place updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="space", ref="#/components/schemas/Place"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized. Only admins can update places.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $place = Place::findOrFail($id);
        if (!$user || !$user->hasRole('admin')) {
            return $this->sendError(['message' => 'Unauthorized. Solo los administradores pueden actualizar lugares.'], 403);
        }

        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validación para archivos de imagen
                'capacity' => 'sometimes|required|integer|min:1',
                'available_from' => 'nullable|date',
                'available_to' => 'nullable|date|after_or_equal:available_from',
                'type' => 'nullable|in:salon,auditorio,sala de reunión,sala de conferencia',
                'default_days' => 'nullable|array',
                'default_days.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday',
                'default_hours' => 'nullable|string|regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/',
            ]);

            $existingImages = json_decode($place->images, true) ?? [];
            $newImagePaths = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $imagePath = $image->storeAs('images/places', $imageName, 'public');
                    $newImagePaths[] = $imagePath;
                }
            }
            
            $allImages = array_merge($existingImages, $newImagePaths);

            $place->update([
                'name' => $request->input('name', $place->name),
                'description' => $request->input('description', $place->description),
                'images' => json_encode($allImages), 
                'capacity' => $request->input('capacity', $place->capacity),
                'available_from' => $request->input('available_from', $place->available_from),
                'available_to' => $request->input('available_to', $place->available_to),
                'type' => $request->input('type', $place->type),
                'default_days' => json_encode($request->input('default_days', json_decode($place->default_days))),
                'default_hours' => $request->input('default_hours', $place->default_hours),
            ]);

            return $this->sendResponse(['space' => $place, 'message' => 'Place updated successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/places/{id}",
     *     tags={"Places"},
     *     summary="Delete a specific place by ID",
     *     description="Delete a place from the system. Only admins can perform this action.",
     *     operationId="deletePlace",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the place to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Place deleted successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized. Only admins can delete places.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('admin')) {
            return $this->sendError(['message' => 'Unauthorized. Only admins can delete places.'], 403);
        }else{
            try {
                $place = Place::findOrFail($id);
                $place->delete();
                return $this->sendResponse(['message' => 'Place deleted successfully.'], 200);
            } catch (\Throwable $th) {
                return $this->sendError(['error' => $th->getMessage()], 500);
            }
        }
    }

    /**
     *
     * @OA\Get(
     *     path="/filter-places",
     *     summary="Filter places based on criteria",
     *     description="Filter places based on type, capacity, and availability dates and times.",
     *     operationId="filterPlaces",
     *     tags={"Places"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Space type (Ej: oficina, sala de reuniones, etc.)",
     *         required=false,
     *         @OA\Schema(type="string", example="office")
     *     ),
     *     @OA\Parameter(
     *         name="capacity",
     *         in="query",
     *         description="Minimum capacity of the space",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date for availability check",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-10-15")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date for availability check",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-10-16")
     *     ),
     *     @OA\Parameter(
     *         name="start_time",
     *         in="query",
     *         description="Start time for availability check (HH:mm)",
     *         required=false,
     *         @OA\Schema(type="string", format="time", example="09:00")
     *     ),
     *     @OA\Parameter(
     *         name="end_time",
     *         in="query",
     *         description="End time for availability check (HH:mm)",
     *         required=false,
     *         @OA\Schema(type="string", format="time", example="17:00")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Places filtered successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="places", type="array", @OA\Items(ref="#/components/schemas/Place"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized.",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server error message")
     *         )
     *     )
     * )
     */
    public function filter(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError(['message' => 'Unauthorized.'], 401);
        }

        $query = Place::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
        }
        
        if ($request->has('start_date') && $request->has('end_date') && $request->has('start_time') && $request->has('end_time')) {
            $query->whereDoesntHave('bookings', function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('start_date', '<=', $request->end_date)
                        ->where('end_date', '>=', $request->start_date)
                        ->where(function ($timeQuery) use ($request) {
                            $timeQuery->where('start_time', '<=', $request->end_time)
                                        ->where('end_time', '>=', $request->start_time);
                        });
                });
            });
        } elseif ($request->has('start_date') && $request->has('end_date')) {
            $query->whereDoesntHave('bookings', function ($q) use ($request) {
                $q->where('start_date', '<=', $request->end_date)
                ->where('end_date', '>=', $request->start_date);
            });
        }

        try {
            $places = $query->get();
            return $this->sendResponse(['places' => $places, 'message' => 'Filtered places obtained successfully.'], 200);
        } catch (\Throwable $th) {
            return $this->sendError(['message' => $th->getMessage()], 500);
        }
    }
}