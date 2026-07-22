<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('where-coffee:about', function (): void {
    $this->info('Where Coffee POS backend is ready.');
})->purpose('Display Where Coffee application information');
