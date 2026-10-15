<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('rejas:generar-cargos')->dailyAt('06:00');
