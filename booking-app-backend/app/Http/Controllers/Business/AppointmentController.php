<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\ServiceItem;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index($id)
    {
        $appointments = Appointment::with('serviceItem')
            ->where('service_id', $id)
            ->get();

        $now = Carbon::now();

        foreach ($appointments as $appointment) {
            if (
                $appointment->end &&
                Carbon::parse($appointment->end)->lessThan($now) &&
                $appointment->status !== 'Zakończona'
            ) {
                $appointment->status = 'Zakończona';
                $appointment->save();
            }
        }

        $updatedAppointments = Appointment::with('serviceItem')
            ->where('service_id', $id)
            ->get();

        return response()->json($updatedAppointments);
    }


    public function store(Request $request)
    {
        // 1. Walidacja danych wejściowych
        $validator = Validator::make($request->all(), [
            'service_item_id' => 'required|exists:service_items,id',
            'start' => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $serviceItem = ServiceItem::findOrFail($request->service_item_id);
        $business = Service::findOrFail($serviceItem->service_id);
        $appointmentStart = Carbon::parse($request->start);
        $appointmentEnd = $appointmentStart->copy()->addMinutes($serviceItem->duration);

        // 2. Sprawdź, czy termin wizyty przypada w godzinach otwarcia biznesu
        $dayOfWeek = $appointmentStart->englishDayOfWeek;
        $openingHoursString = $business->opening_hours;
        $openingHours = preg_split('/\r\n|\r|\n/', subject: $openingHoursString);
        $dayIndex = ($appointmentStart->dayOfWeek + 6) % 7;
        $hours = trim($openingHours[$dayIndex]);

        if ($hours === 'Zamknięte') {
            return response()->json(['message' => 'Biznes jest zamknięty w tym dniu.'], 400);
        }

        list($openTime, $closeTime) = explode('-', $hours);
        $open = Carbon::createFromFormat('H:i', $openTime, $business->timezone);
        $close = Carbon::createFromFormat('H:i', $closeTime, $business->timezone);
        $appointmentTime = Carbon::createFromFormat('H:i', $appointmentStart->format('H:i'), $business->timezone);
        $appointmentTimeEnd = Carbon::createFromFormat('H:i', $appointmentEnd->format('H:i'), $business->timezone);

        if (!$appointmentTime->between($open, $close, true) || !$appointmentTimeEnd->between($open, $close, true)) {
            return response()->json(['message' => 'Wizyta jest poza godzinami otwarcia.'], 400);
        }

        // 3. Sprawdź, czy termin nie koliduje z inną wizytą
        $hasConflict = Appointment::where('service_id', $business->id)
            ->whereDate('start', $appointmentStart->toDateString()) // tylko ten dzień
            ->where(function ($query) use ($appointmentStart, $appointmentEnd) {
                $query->where(function ($q) use ($appointmentStart, $appointmentEnd) {
                    $q->where('start', '<', $appointmentEnd)
                        ->where('end', '>', $appointmentStart);
                });
            })
            ->exists();

        if ($hasConflict) {
            return response()->json(['message' => 'Wybrany termin jest już zajęty.'], 400);
        }

        // 4. Utwórz wizytę
        $appointment = Appointment::create([
            'service_id' => $business->id,
            'service_item_id' => $serviceItem->id,
            'user_id' => auth()->id(),
            'start' => $appointmentStart,
            'end' => $appointmentEnd,
        ]);

        return response()->json($appointment, 201);
    }

    public function getAvailableSlots(Request $request, $service_id)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'service_item_id' => 'required|exists:service_items,id',
        ]);

        $serviceItem = ServiceItem::findOrFail($request->service_item_id);
        $service = $serviceItem->service;

        $date = Carbon::parse($request->date);

        if ($date->isBefore(now()->startOfDay())) {
            return response()->json(['message' => 'Nie można rezerwować wstecz.'], 200);

        }

        $openingHoursString = $service->opening_hours;
        $openingHours = preg_split('/\r\n|\r|\n/', $openingHoursString);

        $dayIndex = ($date->dayOfWeek + 6) % 7;

        if (!isset($openingHours[$dayIndex])) {
            return response()->json(['message' => 'Brak godzin otwarcia dla tego dnia.'], 200);
        }

        \Log::info('Opening hours array', $openingHours);
        \Log::info('Day index', [$dayIndex]);

        $hours = trim($openingHours[$dayIndex]);

        if ($hours === 'Zamknięte' || strpos($hours, '-') === false) {
            return response()->json(['message' => 'Tego dnia jest zamknięte.'], 200);
        }

        list($openTime, $closeTime) = explode('-', $hours);

        $open = Carbon::parse($request->date . ' ' . $openTime);
        $close = Carbon::parse($request->date . ' ' . $closeTime);

        $existingAppointments = Appointment::where('service_item_id', $serviceItem->id)
            ->whereDate('start', $date->toDateString())
            ->orderBy('start')
            ->get();

        $availableSlots = [];
        $currentSlot = $open;

        while ($currentSlot->lessThanOrEqualTo($close)) {
            $slotEnd = $currentSlot->copy()->addMinutes($serviceItem->duration);

            if ($slotEnd->greaterThan($close))
                break;

            $isConflict = false;
            foreach ($existingAppointments as $appointment) {
                $existingStart = Carbon::parse($appointment->start);
                $existingEnd = Carbon::parse($appointment->end);

                if ($currentSlot->lessThan($existingEnd) && $slotEnd->greaterThan($existingStart)) {
                    $isConflict = true;
                    break;
                }
            }

            if (!$isConflict) {
                $availableSlots[] = $currentSlot->format('H:i');
            }

            $currentSlot->addMinutes($serviceItem->duration);
        }

        return response()->json(['available_slots' => $availableSlots]);
    }

    public function getUserAppointments()
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Użytkownik nie jest zalogowany.'], 401);
        }

        $now = Carbon::now();

        // 1. Aktualizacja statusów zakończonych rezerwacji
        Appointment::where('user_id', $userId)
            ->where('end', '<', $now)
            ->where('status', '!=', 'Zakończona')
            ->update(['status' => 'Zakończona']);

        // 2. Pobranie wszystkich potwierdzonych rezerwacji
        $confirmedAppointments = Appointment::with(['service', 'serviceItem'])
            ->where('user_id', $userId)
            ->where('status', 'Potwierdzona')
            ->get();

        // 3. Pobranie trzech ostatnich zakończonych LUB anulowanych rezerwacji
        $recentHistoryAppointments = Appointment::with(['service', 'serviceItem'])
            ->where('user_id', $userId)
            ->whereIn('status', ['Zakończona', 'Anulowana'])
            ->orderBy('end', 'desc')
            ->take(15)
            ->get();

        // 4. Połączenie wyników i usunięcie duplikatów
        $appointments = $confirmedAppointments->merge($recentHistoryAppointments)->unique('id');

        return response()->json($appointments);
    }
    public function cancelAppointment(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Nie masz uprawnień do anulowania tej wizyty.'], 403);
        }

        $appointment->status = 'Anulowana';
        $appointment->save();

        return response()->json(['message' => 'Wizyta została pomyślnie anulowana.']);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update($request->all());

        return response()->json($appointment);
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return response()->json(['message' => 'Wizyta została usunięta.']);
    }
}
