<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'driver=' . config('database.default') . PHP_EOL;
echo 'games=' . App\Models\Game::count() . PHP_EOL;
foreach (App\Models\Game::select('id','sport_id','name','category')->get() as $g) {
    echo $g->id . ' | sport ' . $g->sport_id . ' | ' . $g->name . ' | ' . $g->category . PHP_EOL;
}
echo 'sports=' . App\Models\Sport::count() . PHP_EOL;
echo 'teams=' . App\Models\Team::count() . PHP_EOL;
echo 'medals=' . App\Models\Medal::count() . PHP_EOL;
