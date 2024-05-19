<?php

namespace App\Console\Commands;

use App\Models\Crontjob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CrontjobCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontjob:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crontjob test';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Crontjob::create(['error' => 'Crontjob is called at ' . Carbon::now()]);
    }
}
