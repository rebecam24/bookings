<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Place extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'images',
        'capacity',
        'available_from',
        'available_to',
        'type',
        'active',
        'default_days',
        'default_hours',
    ];

    protected $casts = [
        'images' => 'array',
        'default_days' => 'array',
    ];

    /**
     * Verifica si un lugar está disponible para una fecha y hora dadas.
     *
     * @param string $date La fecha a verificar (formato Y-m-d)
     * @param string $start_time Hora de inicio a verificar (formato H:i)
     * @param string $end_time Hora de fin a verificar (formato H:i)
     * @return bool
     */
    public function isAvailable($date, $start_time, $end_time)
    {
        // Verifica si la fecha cae dentro del rango de disponibilidad general
        if ($this->available_from && $this->available_to) {
            if ($date < $this->available_from || $date > $this->available_to) {
                return false; // No está disponible en este rango de fechas
            }
        }

        // Verifica si el día de la semana está en los días disponibles por defecto
        $dayOfWeek = date('l', strtotime($date)); // Obtiene el día de la semana
        if ($this->default_days && !in_array($dayOfWeek, $this->default_days)) {
            return false; 
        }

        // Verifica si el horario está dentro del rango por defecto
        $defaultHours = explode('-', $this->default_hours);
        if ($start_time < $defaultHours[0] || $end_time > $defaultHours[1]) {
            return false; 
        }

        return true; 
    }
    
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}