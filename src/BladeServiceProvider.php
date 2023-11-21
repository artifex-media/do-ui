<?php
namespace Doorons\DoUI; //Change namespace here

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Doorons\DoUI\ResourceServiceProvider;

class BladeServiceProvider extends ServiceProvider //Change class name here
{
    public function boot(): void
    {

        $this->loadRoutesFrom(__DIR__."/routes/web.php");

        Blade::directive('button_duplicate', function () {
            return '<a data-toggle="tooltip" href="" data-toggle="tooltip" title="Duplicate" data-action="duplicate" class="btn btn-sm btn-icon-sm _resource-action"><i class="far fa-clone"></i></a>';
        });

        Blade::directive('button_archive', function ($expression) {

            return '<?php
            if(isset($action1)) { $action1 = ""; }
            if(isset($action2)) { $action2 = ""; }
                list($arg1,$arg2) = explode(\',\',str_replace([\'(\',\')\',\' \'], \'\', ' . $expression . '));
                $action1 = $arg1 == "archive" ? "Unarchive" : "Archive";
                $action2 = $arg1 == "archive" ? "unarchive" : "archive";

                echo "<a data-toggle=\"tooltip\" title=\"".$action1."\" href=\"\" data-toggle=\"tooltip\" class=\"btn btn-sm btn-icon-sm _resource-action\" data-action=\"".$action2."\"><i class=\"far fa-archive\"></i></a>";
            ?>';

        });



        Blade::directive('button_delete', function ($action) {
            return '<a data-toggle="tooltip" title="Delete" href="#" data-toggle="tooltip" title="Delete" class="btn btn-sm btn-icon-sm _resource-action"  data-action="delete"><i class="far fa-trash"></i></a>';
        });
        
        Blade::directive('button_restore', function ($action) {
           return '<a data-toggle="tooltip" title="Restore"  href="" class="btn btn-sm btn-icon-sm _resource-action" data-action="restore"><i class="far fa-trash-undo-alt"></i></a>';
        });

        Blade::directive('button_preview', function ($expression) {
            return '<?php
                list($arg1, $arg2) = explode(\',\',str_replace([\'(\',\')\',\' \'], \'\', ' . $expression . '));
                echo "<a data-toggle=\"tooltip\" title=\"Preview\" href=\"".route("$arg2.show",$arg1)."\" data-toggle=\"tooltip\" title=\"Preview\" class=\"btn btn-sm btn-icon-sm\"><i class=\"far fa-external-link\"></i></a>";
            ?>';
        });




        Blade::directive('button_edit', function ($expression) {
            // dd($array);
            return '<?php
                list($arg1, $arg2) = explode(\',\',str_replace([\'(\',\')\',\' \'], \'\', ' . $expression . '));
                echo "<a data-toggle=\"tooltip\" title=\"Edit\" href=\"".route("$arg2.edit",$arg1)."\" data-toggle=\"tooltip\" title=\"Edit\" class=\"btn btn-sm btn-icon-sm\"><i class=\"far fa-edit\"></i></a>";
            ?>';
        });

        // Button delete media, werkt nog niet.
        Blade::directive('button_deletemedia', function ($action) {
        });


    }

    public function register()
    {

    }
}
