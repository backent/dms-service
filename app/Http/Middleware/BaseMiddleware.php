<?php

namespace App\Http\Middleware;

use App\Support\Response\Json;
use Closure;
use Illuminate\Http\Request;
use stdClass;

abstract class BaseMiddleware
{
    protected $_Request;
    protected $Model;
    protected $Payload;
    protected $Other;
    protected $Json;
    
    public function __construct(Request $request)
    {
        $this->_Request = $request;
        $this->Json = Json::class;

        if (!empty($this->_Request->Payload)) {
            $this->Payload = $this->_Request->Payload;
            $Model = $this->Payload->get('Model');
            $Other = $this->Payload->get('Other');
            if (!$Model) {
                $Model = new stdClass();
            }
            if (!$Other) {
                $Other = new stdClass();
            }
            
            $this->Model = $Model;
            $this->Other = $Other;
        } else {
            $this->Payload = collect([]);
            $this->Model = new stdClass();
            $this->Other = new stdClass();
        }
    }
    protected function initiate() {}
    protected function validation():bool {
        return true;
    }
    protected function Preparation() {

    }

    public function handle(Request $request, Closure $next, $code = null) {
        $this->initiate($code);
        if (!$this->validation()) {
            return response()->json(Json::get(), Json::get('response.code'));
        } 
        $this->Payload->put('Model', $this->Model);
        $this->Payload->put('Other', $this->Other);
        $this->Preparation();
        
        $this->_Request->merge(['Payload' => $this->Payload]);
        return $next($this->_Request);
    }

}
