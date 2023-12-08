<?php

namespace Doorons\DoUI\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;

// Ajax Only controller!
class ResourceController extends Controller
{

    private $resource_request,$model, $model_type, $model_id, $newPosition, $modelImageCollection, $action;

    public function __construct(Request $request) {
        $this->model_type = $request->get('model');
        $this->model_id = $request->get('id');
        $this->action = $request->get('action');
        $this->newPosition = $request->get('newPosition');
        $this->modelImageCollection = $request->get('modelImageCollection');
        $this->model = $this->action == 'restore' ? $this->model_type::withTrashed()->find($this->model_id) : $this->model_type::find($this->model_id);
        $this->resource_request = $request;
    }

    public function parse(Request $request) {
        return (new ResourceController($request))->{$this->action}();
    }

    public function delete() {
        $result = $this->model->delete();

        return $result ? 'true' : 'false';
    }

    public function restore() {
        $result = $this->model->restore();

        return $result ? 'true' : 'false';
    }

    public function archive() {
        $this->model->archive = 1;
        $result = $this->model->save();

        return $result ? 'true' : 'false';
    }

    public function unarchive() {
        $this->model->archive = 0;
        $result = $this->model->save();

        return $result ? 'true' : 'false';
    }

    public function duplicate()
    {

        $new_model = $this->model->replicate(['media']);
        
        $new_model->title = str_replace(' (duplicate)','',$new_model->title).' (duplicate)';
        $new_model->status = is_int($this->model->status) ? 0 : 'inactive';

        if(Schema::hasColumn($new_model->getTable(), 'uuid')) {
            $new_model->uuid = Str::uuid();
        }

        if(Schema::hasColumn($new_model->getTable(), 'slug')) {
            $new_model->slug = $this->model->slug.'-dup-'.$this->model->where('archive',0)->count();
        }

        if(Schema::hasColumn($new_model->getTable(), 'permalink')) {
            $new_model->permalink = 'https://duplicate-'.$this->model->where('archive',0)->count().'.nl';
        }

        if(Schema::hasColumn($new_model->getTable(), 'custom_domain')) {
            $new_model->custom_domain = 'https://duplicate-'.$this->model->where('archive',0)->count().'.nl';
        }

        $result = $new_model->push();
        $new_model->save();

        $relationships = ['fields','faqs', 'deals', 'tags','rewards', 'brands', 'childs','pages','menus','links'];
        foreach ($relationships as $relationship) {
            $foreign = substr_replace($relationship,"",-1).'_id';
            $foreign_this = substr_replace($this->model->getTable(),"",-1).'_id';


            if (method_exists($this->model, $relationship) && $this->model->$relationship()->exists() && count($this->model->$relationship)) {
                foreach($this->model->$relationship as $item) {
                    $new_item = $item->replicate();
                    $new_item->save();

                    // TODO; recursive relationship. Brand > Menus > Links. Now only Menus can be replicated.
                    if(Schema::hasColumn($new_item->getTable(), $foreign_this)) {
                        $new_item->$foreign_this = $new_model->id; // @TODO; Fix foreign keys! deal_id, menu_id, etc.
                    } 
                    $new_item->save();

                }
            }
        }

        if($this->model->cats) {
            foreach($this->model->cats as $cat)
            {
                $new_model->cats()->attach($cat);
            }
        }

        // if($this->model->tags) {
        //     $new_model->tags()->saveMany($this->model->tags);
        // }
        // $new_model->save();

        \Session::flash('success-message', 'Successfully duplicated!');

        return $result ? 'true' : 'false';
    }

    

    public function reposition()
    {
        $this->model->position  = $this->newPosition;
        $result = $this->model->save();

        return $result ? 'true' : 'false';
    }


    public function deletemedia()
    {
        $this->model->clearMediaCollection($this->modelImageCollection);
        $result = $this->model->save();

        return $result ? 'true' : 'false';
    }
           
}
