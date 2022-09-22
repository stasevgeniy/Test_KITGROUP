<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Russsiq\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\Storage;

class ParseBestChange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ParseBestChange:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parsing courses from Bestchange';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo 1;
        return 0;
    }

    private function downloadZip() {

        Storage::disk('local')->put('example.txt', 'Contents');

        $url = "http://api.bestchange.ru/info.zip"; // .env

        $fp = fopen('/tmp/info.zip', 'w'); // создание файла
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_FILE, $fp); // запись в файл
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);

        curl_exec($ch);
    }

}
