<?php

namespace App\Providers;

use App\Models\EventSetting;
use App\Models\GameMatch;
use App\Models\Medal;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.sport', function (ViewInstance $view): void {
            $data = $view->getData();
            $level = $data['level'] ?? ($data['game']->level ?? null);
            $lastUpdated = collect([GameMatch::max('updated_at'), Medal::max('updated_at')])
                ->filter()
                ->map(fn ($date) => Carbon::parse($date)->setTimezone('Asia/Manila'))
                ->sortDesc()
                ->first();

            $view->with([
                'eventSetting' => EventSetting::first(),
                'lastUpdated' => $lastUpdated,
                'level' => $level,
            ]);
        });
    }
}
