<?php

namespace IPriceGroup\OcApiPlugin\Console;

use Illuminate\Console\Command;
use IPriceGroup\OcApiPlugin\Classes\ApiGenerator;
use IPriceGroup\OcApiPlugin\Models\Resource;

class SyncRoutesCommand extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'ocapiplugin:routes:sync';

    /**
     * @var string The console command description.
     */
    protected $description = 'Synchronizes the routes with entries in database.';

    /**
     * Execute the console command.
     *
     * @param ApiGenerator $apiGenerator
     *
     * @return void
     */
    public function handle(ApiGenerator $apiGenerator)
    {
        $this->info('Synchronizing APIs...');
        $apiGenerator->syncApis(Resource::all());
        $this->info('APIs synchronized!');
    }
}
