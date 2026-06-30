<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$teams = \App\Models\Team::orderBy('group')->orderBy('name')->get();
foreach ($teams as $t) {
    echo $t->id . ' | ' . $t->group . ' | ' . $t->name . "\n";
}
