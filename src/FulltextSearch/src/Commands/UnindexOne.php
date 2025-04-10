<?php

namespace WebMonksBlog\Laravel\Fulltext\Commands;

use Illuminate\Console\Command;
use WebMonksBlog\Laravel\Fulltext\Indexer;

class UnindexOne extends Command
{
    protected $signature = 'laravel-fulltext:unindex {model_class} {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a single record from the searchindex';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $indexer = new Indexer();
        $indexer->unIndexOneByClass($this->argument('model_class'), $this->argument('id'));
    }
}
