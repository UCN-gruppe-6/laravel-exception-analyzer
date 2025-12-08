<?php

namespace App\Console\Commands;

use App\Models\AiJobTestLog;
use Illuminate\Console\Command;


class SendExceptionsToAi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exceptions:classify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        AiJobTestLog::create([
            'message' => 'Dette er en test. Jobbet kørte kl. ' . now(),
        ]);

        $this->info('Testbesked er gemt i databasen!');

        $this->info('Denne test tester schedule funktion i Kernel');
    }
}
