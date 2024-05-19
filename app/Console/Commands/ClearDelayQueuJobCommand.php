<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class ClearDelayQueuJobCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:clear-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lệnh xóa job bị ứ đọng trong jobs';

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
        echo "\n- Bắt đầu xóa jobs lỗi.";

        Job::where('attempts', '>', 0)->delete();

        echo "\n- Kết thúc xóa jobs lỗi.\n";
    }
}
