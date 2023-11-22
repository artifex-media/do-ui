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

        // $action = variable vanuit de model controller. Bijv: archive, trashed, etc. (Waar je je bevindt in index overzicht)
        // pages = route prefix van de model, bijv: pages, users, packages, partners
        // @button_archive($action.',pages')
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

        // $model->slug = $model->slug, $page->slug, etc. Kan ook id zijn of iets anders, afhankelijk van de route (MODEL.show,$param)
        // pages = route prefix van de model, bijv: pages, users, packages, partners
        // Page = model naam, Page, Product, Brand, etc.
        // @button_preview($model->slug.',pages,Page')
        Blade::directive('button_preview', function ($expression) {
            return '<?php
                list($arg1, $arg2, $arg3) = explode(\',\',str_replace([\'(\',\')\',\' \'], \'\', ' . $expression . '));
                $model = $arg3::find($arg1);
                $model_type = $model->type;
                $route = $model_type == "landing" ? route("$arg2.landing") : route("$arg2.show",$model->slug);
                echo "<a data-toggle=\"tooltip\" title=\"Preview\" href=\"".$route."\" data-toggle=\"tooltip\" title=\"Preview\" class=\"btn btn-sm btn-icon-sm\"><i class=\"far fa-external-link\"></i></a>";
            ?>';
        });

        Blade::directive('button_preview_link', function ($expression) {
            return '<?php
            list($url) = explode(\',\',str_replace([\'(\',\')\',\' \'], \'\', ' . $expression . '));
            echo "<a data-toggle=\"tooltip\" title=\"Preview\" href=\"".$url."\" target=\"_blank\" data-toggle=\"tooltip\" title=\"Preview\" class=\"btn btn-sm btn-icon-sm\"><i class=\"far fa-external-link\"></i></a>";
   
            ?>';
        });

        Blade::directive('button_save', function () {
            return '<?php
            echo "<button class=\"btn btn-sm btn-secondary ml-2\" type=\"submit\"><i class=\"fad fa-save d-none d-sm-inline-block\"></i>&nbsp;Save</button>";
   
            ?>';
        });

        Blade::directive('searchable_form', function () {
            return '<?php 
            
            $class = isset($_GET["search"]) && $_GET["search"] != "" ? "input-group-focus" : "";
            $class2 = isset($_GET["search"]) && $_GET["search"] != "" ? "input-focus" : "";
            $value = isset($_GET["search"]) && $_GET["search"] != "" ? $_GET["search"] : "";

            echo
            "<div class=\"input-group input-group-sm ".$class." mt-3 my-sm-0 w-auto\">
                <div class=\"input-group-prepend\">
                    <div class=\"input-group-text\"><i class=\"fas fa-search\"></i></div>
                </div>
                <form method=\"get\">
                    <input type=\"text\" name=\"search\" id=\"search\" class=\"form-control-search ".$class2." form-control form-control-sm\" placeholder=\"Search\" value=\"".$value."\">
                </form>
            </div>";
            ?>';
        });

        // Parent models:
        // $model->id = $model->id, $page->id, $partner->id, etc.
        // pages = route prefix van de model, bijv: pages, users, packages, partners
        // @button_edit($model->id.',pages')

        // Submodels:
        // $brand->id = parent id, bijv $brand->id, $deal->id.
        // brands.deals = parent route prefix en submodel route prefix
        // @button_edit($brand->id.'-'.$deal->id.',brands.deals')

        Blade::directive('button_edit', function ($expression) {
            // dd($array);
            return '<?php
                list($arg1, $arg2) = explode(\',\',str_replace([\'(\',\')\',\' \'], \'\', ' . $expression . '));
                $arg1 = explode("-",$arg1);
                echo "<a data-toggle=\"tooltip\" title=\"Edit\" href=\"".route("$arg2.edit",$arg1)."\" data-toggle=\"tooltip\" title=\"Edit\" class=\"btn btn-sm btn-icon-sm\"><i class=\"far fa-edit\"></i></a>";
            ?>';
        });

        // $model->id = $model->id, $page->id, $partner->id, etc.
        // partner_logo = image (collection) name, bijv partner_logo, header_image, mobile_logo
        // Partner = Model -> Partner, User, Page, etc.
        // @button_deletemedia($model->id.',partner_logo,Partner')
        Blade::directive('button_deletemedia', function ($expression) {
            return '<?php
                list($arg1, $arg2, $arg3) = explode(\',\',str_replace([\'(\',\')\',\' \'], \'\', ' . $expression . '));
                echo "<a href=\"#\" class=\"btn-xs _resource-action btn btn-white mb-0\" data-model-id=\"".$arg1."\" data-model=\"".$arg3."\" data-action=\"deletemedia\" data-model-image-collection=\"".$arg2."\"><i class=\"far fa-trash-alt\"></i></a>";
            ?>';
        });


    }

    public function register()
    {

    }
}
