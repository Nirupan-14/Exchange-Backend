<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ExchangeRate;
use Carbon\Carbon;

class ExchangeRateTest extends TestCase
{
    public function test_store_exchange_rate_successfully()
    {
        $response = $this->postJson('/exchange-rate/store', [
            'rate' => 365.4321,
            'date' => now()->toDateString(),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'currency_from',
                    'currency_to',
                    'rate',
                    'updated_at',
                    '_id',
                ],
            ]);
    }

    public function test_store_exchange_rate_validation_error()
    {
        $response = $this->postJson('/exchange-rate/store', [
            'rate' => 'invalid',
            'date' => 'not-a-date',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rate', 'date']);
    }

    public function test_get_latest_usd_to_lkr_rate()
    {
        ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to' => 'LKR',
            'rate' => 370.25,
            'updated_at' => now(),
        ]);

        $response = $this->get('/exchange-rate/usd-lkr');
        $response->assertStatus(200)
            ->assertJsonFragment(['currency_from' => 'USD', 'currency_to' => 'LKR']);
    }

    public function test_get_last_7_days_rates_and_average()
    {
        for ($i = 0; $i < 7; $i++) {
            ExchangeRate::create([
                'currency_from' => 'USD',
                'currency_to' => 'LKR',
                'rate' => 360 + $i,
                'created_at' => Carbon::now()->subDays($i),
                'updated_at' => Carbon::now()->subDays($i),
            ]);
        }

        $response = $this->get('/exchange-rate/last-7-days');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'rates',
                'average_rate',
                'reference_date',
            ]);
    }

    public function test_get_usd_to_lkr_rate_by_specific_date()
    {
        $targetDate = Carbon::now()->subDays(3)->toDateString();

        ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to' => 'LKR',
            'rate' => 362.99,
            'updated_at' => $targetDate,
            'created_at' => $targetDate,
        ]);

        $response = $this->get('/exchange-rate/usd-lkr/by-date?date=' . $targetDate);
        $response->assertStatus(200)
            ->assertJsonFragment(['rate' => '362.9900']);
    }

    public function test_get_usd_to_lkr_by_date_with_invalid_date()
    {
        $response = $this->get('/exchange-rate/usd-lkr/by-date?date=invalid-date');
        $response->assertStatus(400)
            ->assertJsonFragment(['error' => 'Invalid date format']);
    }
}
