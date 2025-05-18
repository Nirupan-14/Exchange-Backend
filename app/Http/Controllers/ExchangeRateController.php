<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExchangeRateController extends Controller
{
    // Get the latest USD to LKR rate
    public function getUsdToLkr(): JsonResponse
    {
        $rate = ExchangeRate::where('currency_from', 'USD')
            ->where('currency_to', 'LKR')
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$rate) {
            return response()->json(['error' => 'Exchange rate not found'], 404);
        }

        return response()->json([
            'currency_from' => $rate->currency_from,
            'currency_to' => $rate->currency_to,
            'rate' => number_format($rate->rate, 4),
            'updated_at' => $rate->updated_at,
        ]);
    }

    // Insert a new exchange rate record (can be tested via GET or POST)
    public function insertSampleRate(Request $request): JsonResponse
    {
        $rate = ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to' => 'LKR',
            'rate' => 368.1234,
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => ' USD rate updated',
            'data' => $rate,
        ]);
    }

    // Insert last 7 days sample rates (manually without loops)
    public function insertLast7DaysSampleRates(): JsonResponse
    {
        ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to' => 'LKR',
            'rate' => 360.12,
            'updated_at' => Carbon::now()->subDays(0),
            'created_at' => Carbon::now()->subDays(0),
        ]);
        ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to' => 'LKR',
            'rate' => 360.45,
            'updated_at' => Carbon::now()->subDays(1),
            'created_at' => Carbon::now()->subDays(1),
        ]);
        ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to' => 'LKR',
            'rate' => 360.30,
            'updated_at' => Carbon::now()->subDays(2),
            'created_at' => Carbon::now()->subDays(2),
        ]);
        ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to' => 'LKR',
            'rate' => 360.50,
            'updated_at' => Carbon::now()->subDays(3),
            'created_at' => Carbon::now()->subDays(3),
        ]);
        ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to' => 'LKR',
            'rate' => 360.40,
            'updated_at' => Carbon::now()->subDays(4),
            'created_at' => Carbon::now()->subDays(4),
        ]);
        ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to' => 'LKR',
            'rate' => 360.35,
            'updated_at' => Carbon::now()->subDays(5),
            'created_at' => Carbon::now()->subDays(5),
        ]);
        ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to' => 'LKR',
            'rate' => 360.20,
            'updated_at' => Carbon::now()->subDays(6),
            'created_at' => Carbon::now()->subDays(6),
        ]);

        return response()->json(['message' => '7 days sample data inserted']);
    }

    // Get last 7 days of data and average
    public function getLast7DaysRatesAndAverage(Request $request): JsonResponse
    {
        $endDateInput = $request->query('end_date');

        try {
            $endDate = $endDateInput ? Carbon::parse($endDateInput)->endOfDay() : Carbon::now()->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        $startDate = $endDate->copy()->subDays(6)->startOfDay();

        $allRates = ExchangeRate::where('currency_from', 'USD')
            ->where('currency_to', 'LKR')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->orderBy('updated_at', 'asc')
            ->get(['rate', 'updated_at']);

        if ($allRates->isEmpty()) {
            return response()->json(['error' => 'No rates found in selected period'], 404);
        }

        $uniqueDateRates = $allRates->groupBy(fn($rate) => $rate->updated_at->format('Y-m-d'))
            ->map(fn($items) => $items->first())
            ->sortBy('updated_at')
            ->values();

        $average = number_format($uniqueDateRates->avg('rate'), 4);

        return response()->json([
            'rates' => $uniqueDateRates->map(function ($rate) {
                return [
                    'rate' => number_format($rate->rate, 4),
                    'date' => $rate->updated_at->format('Y-m-d'),
                    'time' => $rate->updated_at->format('H:i:s'),
                    'datetime' => $rate->updated_at->toDateTimeString(),
                ];
            }),
            'average_rate' => $average,
            'reference_date' => $endDate->toDateTimeString(),
        ]);
    }

    // Get rate for a specific date
    public function getUsdToLkrByDate(Request $request): JsonResponse
    {
        $date = $request->query('date');

        if (!$date) {
            return response()->json(['error' => 'Date query parameter is required'], 400);
        }

        try {
            $targetDate = Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        $rate = ExchangeRate::where('currency_from', 'USD')
            ->where('currency_to', 'LKR')
            ->whereDate('updated_at', $targetDate)
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$rate) {
            return response()->json(['error' => 'Exchange rate not found for the specified date'], 404);
        }

        return response()->json([
            'currency_from' => $rate->currency_from,
            'currency_to' => $rate->currency_to,
            'rate' => number_format($rate->rate, 4),
            'updated_at' => $rate->updated_at->toDateTimeString(),
        ]);
    }



    // Store custom exchange rate entry
    public function storeExchangeRate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rate' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($validated['date'])->startOfDay();

        // Check if a record already exists for this date
        $existingRate = ExchangeRate::where('currency_from', 'USD')
            ->where('currency_to', 'LKR')
            ->whereDate('updated_at', $date)
            ->first();

        if ($existingRate) {
            // Update existing record
            $existingRate->rate = $validated['rate'];
            $existingRate->updated_at = $date;
            $existingRate->created_at = $date;
            $existingRate->save();

            return response()->json([
                'message' => 'Exchange rate updated successfully',
                'data' => $existingRate,
            ]);
        }

        // Create new record
        $newRate = ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to' => 'LKR',
            'rate' => $validated['rate'],
            'updated_at' => $date,
            'created_at' => $date,
        ]);

        return response()->json([
            'message' => 'Exchange rate stored successfully',
            'data' => $newRate,
        ]);
    }
}
