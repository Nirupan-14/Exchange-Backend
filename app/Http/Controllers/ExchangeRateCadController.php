<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExchangeRateCad;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ExchangeRateCadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rate' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($validated['date'])->startOfDay();

        $existing = ExchangeRateCad::whereDate('updated_at', $date)->first();

        if ($existing) {
            $existing->update([
                'rate' => $validated['rate'],
                'updated_at' => $date,
                'created_at' => $date,
            ]);
            return response()->json(['message' => 'CAD rate updated', 'data' => $existing]);
        }

        $newRate = ExchangeRateCad::create([
            'currency_from' => 'CAD',
            'currency_to' => 'LKR',
            'rate' => $validated['rate'],
            'updated_at' => $date,
            'created_at' => $date,
        ]);

        return response()->json(['message' => 'CAD rate created', 'data' => $newRate]);
    }

    public function getLast7Days(Request $request): JsonResponse
    {
        $endDateInput = $request->query('end_date');

        try {
            $endDate = $endDateInput ? Carbon::parse($endDateInput)->endOfDay() : Carbon::now()->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        $startDate = $endDate->copy()->subDays(6)->startOfDay();

        $rates = ExchangeRateCad::whereBetween('updated_at', [$startDate, $endDate])
            ->orderBy('updated_at')
            ->get();

        if ($rates->isEmpty()) {
            return response()->json(['error' => 'No rates found'], 404);
        }

        $uniqueRates = $rates->groupBy(fn($rate) => $rate->updated_at->format('Y-m-d'))
            ->map(fn($items) => $items->first())
            ->sortBy('updated_at')
            ->values();

        $average = number_format($uniqueRates->avg('rate'), 4);

        return response()->json([
            'rates' => $uniqueRates->map(fn($rate) => [
                'rate' => number_format($rate->rate, 4),
                'date' => $rate->updated_at->format('Y-m-d'),
                'time' => $rate->updated_at->format('H:i:s'),
                'datetime' => $rate->updated_at->toDateTimeString(),
            ]),
            'average_rate' => $average,
            'reference_date' => $endDate->toDateTimeString(),
        ]);
    }

    public function getByDate(Request $request): JsonResponse
    {
        $date = $request->query('date');

        if (!$date) {
            return response()->json(['error' => 'Date is required'], 400);
        }

        try {
            $targetDate = Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        $rate = ExchangeRateCad::whereDate('updated_at', $targetDate)->first();

        if (!$rate) {
            return response()->json(['error' => 'Rate not found for this date'], 404);
        }

        return response()->json([
            'currency_from' => $rate->currency_from,
            'currency_to' => $rate->currency_to,
            'rate' => number_format($rate->rate, 4),
            'updated_at' => $rate->updated_at->toDateTimeString(),
        ]);
    }
}
