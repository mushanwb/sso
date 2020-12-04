<?php
namespace App\Console;

use App\Jobs\TestJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class test extends Command{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test';
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

    }

    public  function operation()
    {
        Log::info('任务开始');

        for ($i=0;$i < 10; $i++) {
            dispatch(new TestJob($i));
        }
    }
}
