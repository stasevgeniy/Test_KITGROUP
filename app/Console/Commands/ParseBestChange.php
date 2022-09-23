<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Russsiq\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

//ini_set('memory_limit', '64M'); // set to php.ini

class ParseBestChange extends Command
{
    private $best = [];
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
        ini_set('memory_limit', -1); // Change to php.ini

        $this->downloadZip();
        //$this->parseCourses();
        return 0;
    }

    private function downloadZip() {
        $url = "http://api.bestchange.ru/info.zip"; // .env
        $file_name_zip = "info.zip"; // .env
        $file = file_get_contents($url);
        if($file !== false) {
            Storage::disk('local')->put($file_name_zip, $file);
            // unpacking
            $filename = \storage_path('app/'.$file_name_zip);

            $destination = \storage_path('app/temp/');
            if (\file_exists($filename)) {
                $zipper = Zipper::open($filename);
                $zipper->extractTo($destination);
                $zipper->close();

                $this->parseCourses();
            }

        }else{
            $this->error('Zip archive is empty');
            return false;
        }
    }

    private function parseCourses() {
        $filename = "bm_rates.dat";
        $file_path = Storage::path('temp/'.$filename);

        $file = fopen($file_path, "r");
        $array = null;
        if ($file) {
            while (($buffer = fgets($file)) !== false) {
                $buffer = explode(";", $buffer);
                $buffer = [
                    "first" => intval($buffer[0]), 
                    "second" => intval($buffer[1]), 
                    "id_obmennik" => intval($buffer[2]), 
                    "best_course" => floatval($buffer[5])
                ];
                $array[] = $buffer;
            }
        }else{
            $this->error('not found bm_rates.dat');
            return false;
        }
        fclose($file);
        $this->searchBestCourse($array);
    }

    private function searchBestCourse(array $courses) {
        uasort($courses, 'static::cmp_function');
        $r=0;

        $result = [];

        $first_val = null;
        $second_val = null;
        $best_course = null;
        $id_obmennik = null;

        foreach ($courses as $key => $val) {
            $r++;
            if($first_val === null){
                $first_val = $courses[$key]["first"];
                $second_val = $courses[$key]["second"];
                $best_course = $courses[$key]["best_course"];
                $id_obmennik = $courses[$key]["id_obmennik"];
                continue;
            }

            if($courses[$key]["first"] == $first_val) {
                if($courses[$key]["second"] == $second_val) {
                    if($courses[$key]["best_course"] > $best_course) {
                        $best_course = $courses[$key]["best_course"];
                        $id_obmennik = $courses[$key]["id_obmennik"];
                    }
                }else{
                    $buffer = [
                        "first" => $first_val, 
                        "second" => $second_val, 
                        "id_obmennik" => $id_obmennik, 
                        "best_course" => $best_course
                    ];
                    $result[] = $buffer;
                    $second_val = $courses[$key]["second"];
                    $best_course = $courses[$key]["best_course"];
                }

            }else{
                $buffer = [ 
                    "first" => $first_val, 
                    "second" => $second_val, 
                    "id_obmennik" => $id_obmennik, 
                    "best_course" => $best_course
                ];
                $result[] = $buffer;

                $first_val = null;
                $second_val = null;
                $best_course = null;
                $id_obmennik = null;
            }
        }

        $this->insertDatabase($result);
    }

    private function insertDatabase(array $courses) {
        foreach ($courses as $key => $val) 
        {
            DB::table('bestcourses')->insert([
                'first' => $courses[$key]["first"],
                'second' => $courses[$key]["second"],
                'id_obmennik' => $courses[$key]["id_obmennik"],
                'best_course' => $courses[$key]["best_course"]
            ]);
        }
       
    }

    private static function cmp_function($a, $b){
        $array = array( 'first'=>'asc', 'second'=>'desc' , 'best_course'=>'desc' );

        $res = 0;
        foreach( $array as $k=>$v ){
            if( $a[$k] == $b[$k] ) continue;

            $res = ( $a[$k] < $b[$k] ) ? -1 : 1;
            if( $v=='desc' ) $res= -$res;
            break;
        }

        return $res;

    }
}
