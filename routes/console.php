<?php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\ImportFoodData;

// Registra o agendamento diário
Schedule::command(ImportFoodData::class)->dailyAt('03:00');