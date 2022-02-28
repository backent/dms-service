<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Support\Response\Json;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function login(Request $request)
    {
        $Model = $request->Payload->get('Model');
        DB::beginTransaction();
        try {
            $Model->User->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
        $client = DB::table('oauth_clients')->where('password_client', 1)->first();
        $response = Http::asForm()->post(config('app.auth_url') . '/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $this->_Request->input('username'),
            'password' => $this->_Request->input('password'),
            'scope' => '',
        ]);

        $OauthResponse = json_decode((string) $response->getBody(), true);
        $OauthResponseCode = json_decode((string) $response->getStatusCode(), true);
        if ($OauthResponseCode == 200) {

            Json::set('data.user', $Model->User->only(['id', 'username', 'email', 'name']));
            Json::set('data.token', $OauthResponse);
            Json::set('response.code', 200);
            Json::set('response.description', 'OK');
        }

        return response()->json(Json::get(), Json::get('response.code'));
    }
}
