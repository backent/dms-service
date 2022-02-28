<?php

namespace App\Http\Middleware\Api\Auth;

use App\Http\Middleware\BaseMiddleware;
use App\Models\DataBod;
use App\Models\DSSORG;
use App\Models\RoleUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class Login extends BaseMiddleware
{

    protected function initiate () {
      $this->Other->ldapAuth = t_ldap($this->_Request->input('username'), $this->_Request->input('password'));
      $this->Other->ldapAuthToken = $this->Other->ldapAuth['data']['jwt']['token'] ?? null;
      
      if (isset($this->Other->ldapAuthToken)) {
        $this->Other->ldap = ldap_get_user($this->Other->ldapAuthToken, $this->_Request->input('username')) ?? null;
        $this->Other->userLdap = $this->Other->ldap['data']['dataPosisi'] ?? null;

        $this->Model->User = User::where('username', $this->_Request->input('username'))->first() ?? new User();
        $this->Model->User->username = $this->_Request->input('username');
        $this->Model->User->name = $this->Other->userLdap['NAMA'] ?? $this->_Request->input('username');
        $this->Model->User->divisi = ldap_get_divisi($this->Other->userLdap['LONG_UNIT'] ?? '');
        $this->Model->User->email = $this->Other->userLdap['EMAIL'] ?? $this->_Request->input('username') . '@telkom.co.id';
        $this->Model->User->password = bcrypt($this->_Request->input('password'));
      }
    }   

    protected function validation():bool {
        $validator = Validator::make($this->_Request->all(), [
          'username' => ['required'],
          'password' => ['required']
        ]);

        if ($validator->fails()) {
          $this->Json::set('errors', $validator->errors()->jsonSerialize());
          $this->Json::set('response.code', 400);
          return false;
        }

        if (!isset($this->Other->ldapAuthToken)) {
          $this->Json::set('exception.code', 'NotPermitted.Login.UserOrPasswordInCorrect');
          $this->Json::set('exception.description', trans($this->Json::get('exception.code')));
          $this->Json::set('response.code', 400);
          return false;
        }

        return true;
    }
    
}
