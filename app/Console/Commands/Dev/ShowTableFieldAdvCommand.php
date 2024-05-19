<?php

namespace App\Console\Commands\Dev;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ShowTableFieldAdvCommand extends Command
{
    /**
     * The name and signature of the console command.     *
     * @var string
     */
    protected $signature = 'table:list-field {index?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lệnh hiển thị danh sách field của table';

    /**
     * ShowTableFieldCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        // Lấy ds bảng và hiển thị cho user chọn
        $tables = DB::select('SHOW TABLES');
        $array_table = [];
        foreach($tables as $key => $table) {
            $array_table[] = $table->Tables_in_sscdb;
            echo "\n" . $key . '. ' . $table->Tables_in_sscdb;
        }

        $index = $this->argument('index');
        if (is_null($index)) {
            $table_index = $this->ask('What is table name index?');
            if ($table_index >= 0 && $table_index < count($array_table)) {
                $this->_process($array_table[$table_index]);
            } else {
                echo "\nBạn chọn số thứ tự ngoài phạm vi. Bạn vui lòng, chạy lại. \n";
            }
        }
    }

    /**
     * Hàm lấy ds trường của một bảng
     *
     * @param $tableName
     */
    private function _process($tableName)
    {
        echo $tableName.": ";
        $content = PHP_EOL . date('d/m/Y H:i:s') . PHP_EOL;

        /* Lay danh sach field */
        $columns = Schema::getColumnListing($tableName);

        /* In field theo hang ngang */
        echo "\n";
        //echo json_encode(is_array($columns) ? $columns : []);
        $content .= json_encode(is_array($columns) ? $columns : []) . PHP_EOL;

        //echo "\n--------------------------------------------------------------------------------------------------\n";
        if (is_array($columns) && count($columns) > 0) {
            foreach ($columns as $key => $value){
                if ($key + 1 < count($columns)){
                    //echo "'" . $value . "',";
                    $content .= "'" . $value . "',";
                } else {
                    //echo "'" . $value . "'";
                    $content .= "'" . $value . "'";
                }
            }
            $content .= PHP_EOL;
        }

        //echo "\n--------------------------------------------------------------------------------------------------\n";
        //echo implode(",", $columns);
        $content .= implode(",", $columns) . PHP_EOL;

        /* In field theo hang doc */
        if(is_array($columns) && count($columns) > 0){
            //echo "\n--------------------------------------------------------------------------------------------------";
            foreach ($columns as $key => $val) {
                if ($key + 1 < count($columns)){
                    //echo "\n'".$val."',";
                    $content .= "\n'".$val."',";
                } else {
                    //echo "\n'".$val."'";
                    $content .= "\n'".$val."'";
                }
            }
        }

        echo $content;
        // Lưu ra file
        $fields_file = storage_path('app/public/fields.txt');
        $file = @fopen($fields_file, "a") or die("Unable to open file!");
        @fclose($file);
        @file_put_contents($fields_file, $content.PHP_EOL , FILE_APPEND | LOCK_EX);
    }
}
