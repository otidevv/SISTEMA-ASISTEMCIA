<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

foreach (App\Models\Carrera::whereIn('grupo', ['B', 'C', 'D'])->get() as $c) {
    echo $c->id . ": [" . $c->nombre . "]\n";
}
