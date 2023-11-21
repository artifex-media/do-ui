<?php

namespace Doorons\DoUI\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

// Ajax Only controller!
class ResourceController extends Controller
{

    private $resource_request,$model, $model_type, $model_id, $newPosition, $modelImageCollection, $action;

    public function __construct(Request $request) {
        $this->model_type = $request->get('model');
        $this->model_id = $request->get('id');
        $this->action = $request->get('action');
        $this->newPosition = $request->get('newPosition');;
        $this->modelImageCollection = $request->get('modelImageCollection');;
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

        $count = $this->model->count();
        
        $new_model = $this->model->replicate(['media']);
        
        $new_model->title = $new_model->title.' '.$count + 1;
        $new_model->status = '0';
        $new_model->uuid = Str::uuid();
        $result = $new_model->push();

        if($this->model->cats) {
            foreach($this->model->cats as $cat)
            {
                $new_model->cats()->attach($cat);
            }
        }

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
