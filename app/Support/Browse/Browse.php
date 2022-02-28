<?php

namespace App\Support\Browse;

use Illuminate\Support\Facades\DB;
use App\Support\Browse\Fetch as FetchBrowse;
use Illuminate\Support\Collection;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

trait Browse
{
    protected $Search = null;
    protected $OrderBy = [];
    protected $tableName = null;
    protected $Count = null;
    protected $CountOver = false;
    protected $StaticTotal = null;
    public $IsSkipGuard = false;

    public function Count($count, $over = false)
    {
        $this->Count = $count;
        $this->CountOver = $over;
        return $this;
    }


    public function Browse($request, $Model, $function = null)
    {
        $this->checkAuth();
        $IsCollection = $Model instanceof Collection;
        $WithData = false;
        if (
            !isset($request->ArrQuery->set)
            || (isset($request->ArrQuery->set) && $request->ArrQuery->set !== 'count')
        ) {
            $WithData = true;
        }

        if (!$IsCollection && count($this->OrderBy) > 0) {
            foreach ($this->OrderBy as $key => $order) {
                if (isset($request->ArrQuery->{'orderBy.' . $order})) {
                    $orderName = $order;
                    $Model->orderBy($orderName, $request->ArrQuery->{'orderBy.' . $order});
                }
            }
        }
        if (isset($request->ArrQuery->id) && $request->ArrQuery->id !== 'my'  && !$IsCollection) {
            $ids = explode(',', $request->ArrQuery->id);
            if (!empty($request->CustomFieldId)) {
                $Model->whereIn($request->CustomFieldId, $ids);
            } else {
                $Model->whereIn('id', $ids);
            }
        }
        if (isset($request->ArrQuery->take)) {
            if ($request->ArrQuery->take !== 'all') {
                $request->ArrQuery->take = (int) $request->ArrQuery->take;
            }
        }
        if (isset($request->ArrQuery->skip)) {
            $request->ArrQuery->skip = (int) $request->ArrQuery->skip;
        }

        if ($this->Search && !$IsCollection) {
            if (!empty($request->CustomFieldId)) {
                $Model = $Model->whereIn($request->CustomFieldId, $this->SearchModel->search($this->Search)->pluck($request->CustomFieldId));
            } else {
                $Model = $Model->whereIn('id', $this->SearchModel->search($this->Search)->pluck('id'));
            }
        }

        $Array = [
            'query' => $request->ArrQuery
        ];
        if ($request->ArrQuery->{'with.total'}) {
            $ModelForCount = clone $Model;
            if ($this->CountOver) {
                // $count = $this->Count ? $this->Count : '*';
                // $ModelForCount = $ModelForCount->selectRaw('COUNT('.$count.') OVER() as aggregate');
            }
        }
        if ($request->ArrQuery->take !== 'all') {
            if (!$IsCollection) {
                $Model->take($request->ArrQuery->take)->skip($request->ArrQuery->skip);
            } else {
                $Model = $Model->skip($request->ArrQuery->skip)->values();
                $Model = $Model->take($request->ArrQuery->take)->values();
            }
        }
        if (!$IsCollection && config('app.debug')) {
            $ModelForSQL = clone $Model;
            $Array['debug'] = [
                'sql' => $ModelForSQL->toSql(),
                'bindings' => $ModelForSQL->getBindings()
            ];
        }

        if ($IsCollection) {
            $data = $Model;
        } elseif ($WithData) {
            $data = $Model->get();
        }

        if ($function instanceof Closure && $WithData) {
            $data = call_user_func_array($function, [ $data ]);
        }

        if (!$IsCollection && $request->ArrQuery->{'with.total'}) {
            $ModelForCount->getQuery()->orders = null;
            if ($this->CountOver) {
                $Array['total'] = DB::table(DB::raw("({$ModelForCount->toSql()}) as sub") )->mergeBindings($ModelForCount->toBase())->count();
            } else {
                $Array['total'] = $this->Count ? (int) $ModelForCount->count($this->Count) : (int) $ModelForCount->count();
            }
        }
        if ($this->StaticTotal !== null && $request->ArrQuery->{'with.total'}) {
            $Array['total'] = $this->StaticTotal;
        }
        if ($WithData) {
            if ((isset($request->ArrQuery->set)) && $request->ArrQuery->set === 'first') {
                $Array['show'] = (int) isset($data[0]) ? 1 : 0;
                $Array['records'] = isset($data[0]) ? $data[0] : (object)[];
            } else {
                $Array['show'] = (int) count($data);
                $Array['records'] = $data;
            }
        }
        return $Array;
    }
    
    public function Group(&$item, $key, $str, &$data)
    {
        if (substr($key, 0, strlen($str)) === $str) {
            if (is_object($item)) {
                $item->{substr($key, strlen($str))} = $data->{$key};
            } else {
                $item[substr($key, strlen($str))] = $data->{$key};
            }
            unset($data->{$key});
        }
    }

    public function OrderBy($orderlist)
    {
        if (isset($orderlist)) {
            $this->OrderBy = $orderlist;
        }
        return $this;
    }

    public static function FetchBrowse($request)
    {
        $NewRequest = $request->createFromBase($request);
        $Payload = collect([]);
        $Other = (object)[];
        try {
            $Payload = $request->Payload;
            $Other = $Payload->get('Other');
            $Payload->put('Other', $Other);
            $request->merge(['Payload' => $Payload]);
        } catch (\Exception $e) {
        }
        return new FetchBrowse(__CLASS__, $NewRequest, false);
    }

    private function checkAuth() {
        if ($this->_Request->ArrQuery && !empty($this->_Request->ArrQuery->id) && $this->_Request->ArrQuery->id === 'my' && !Auth::check()) {
            throw new AuthenticationException;
        }
    }
}
