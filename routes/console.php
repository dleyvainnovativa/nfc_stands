<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
| Hostinger: add ONE server cron running every minute:
|   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
*/

// Scan events → daily stats + stand counters.
Schedule::command('stands:rollup-scans')->dailyAt('03:10')->withoutOverlapping();
Schedule::command('stands:rollup-scans --days=1')->hourly()->withoutOverlapping();

// Google Places: refresh ratings/reviews + record daily snapshot. Once a day is
// plenty for rating trends and keeps Places API billing low.
Schedule::command('google:sync-places')->dailyAt('04:20')->withoutOverlapping();
