<?php

namespace App\Support;

class QueryRoute
{
    public function __construct($request, $force = false, $replace = '')
    {
        $this->_Request = $request;
        if ($force) {
            $this->Query = $replace ? $replace : '';
        } else {
            $this->Query = !empty($this->_Request->route('query')) ? $this->_Request->route('query') : null;
        }
        $this->ArrQuery = [];
        if ($this->Query) {
            $this->Query = explode('/', $this->Query);
            $this->CountQuery = count($this->Query);
            if( $this->CountQuery%2 == 0 ){
                for ($i = 0; $i < $this->CountQuery; $i++) {
                    if( $i%2 == 0 ){
                        $this->ArrQuery[$this->Query[$i]] = null;
                    }
                    if( $i%2 == 1 ){
                        $this->ArrQuery[$this->Query[$i-1]] = urldecode($this->Query[$i]);
                    }
                }
                $this->Param = true;
            }else{
                $this->Param = false;
            }
        } else {
            $this->Param = true;
        }
        return $this;
    }

    public function get()
    {
        if (isset($this->_Request->query()['take'])) {
            $this->ArrQuery['take'] = $this->_Request->take;
        }
        if (isset($this->_Request->query()['skip'])) {
            $this->ArrQuery['skip'] = $this->_Request->skip;
        }
        if (isset($this->_Request->query()['limit'])) {
            $this->ArrQuery['take'] = $this->_Request->limit;
        }
        if (isset($this->_Request->query()['position'])) {
            $this->ArrQuery['skip'] = $this->_Request->position;
        }
        if (isset($this->_Request->query()['with_total'])) {
            $this->ArrQuery['with.total'] = $this->_Request->with_total;
        }
        if(!array_key_exists('take', $this->ArrQuery)) {
            $this->ArrQuery['take'] = (string)10;
        }
        if(!array_key_exists('skip', $this->ArrQuery)) {
            $this->ArrQuery['skip'] = (string)0;
        }
        if(array_key_exists('limit', $this->ArrQuery)) {
            $this->ArrQuery['take'] = $this->ArrQuery['limit'];
        }
        if(array_key_exists('position', $this->ArrQuery)) {
            $this->ArrQuery['skip'] = $this->ArrQuery['position'];
        }
        if(array_key_exists('with.total', $this->ArrQuery)) {
            if ($this->ArrQuery['with.total'] !== 'true') {
                $this->ArrQuery['with.total'] = false;
            } else {
                $this->ArrQuery['with.total'] = true;
            }
        } else {
            $this->ArrQuery['with.total'] = false;
        }
        $this->OriginalArrQuery = (object)$this->ArrQuery;
        $this->_Request->merge(['ArrQuery' => (object)$this->ArrQuery, 'OriginalArrQuery' => $this->OriginalArrQuery]);
        return $this->_Request;
    }
}
