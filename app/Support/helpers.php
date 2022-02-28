<?php

if (! function_exists('t_ldap')) {
    function t_ldap ($username, $password)
    {
        // $http = new \Illuminate\Support\Facades\Http;
        $response = \Illuminate\Support\Facades\Http::asForm()->post(config('ldap.hcm_host') . '/hcm/auth/v1/token', [
            'username' => $username,
            'password' => $password,
        ]);

        $LDAPResponse = json_decode((string) $response->getBody(), true);
        $LDAPResponseCode = json_decode((string) $response->getStatusCode(), true);

        return $LDAPResponse;
    }
}
if (! function_exists('ldap_get_user')) {
    function ldap_get_user ($token, $nik)
    {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'x-authorization' => 'Bearer ' . $token
        ])->get(config('ldap.hcm_host') . '/hcm/pwb/v1/profile/' . $nik);
        $LDAPResponse = json_decode((string) $response->getBody(), true);
        $LDAPResponseCode = json_decode((string) $response->getStatusCode(), true);

        return $LDAPResponse;
    }
}
if (! function_exists('ldap_get_bidang')) {
    function ldap_get_bidang ($long_unit)
    {
        $datas = explode('/', $long_unit);
        if (!empty($datas[2]) && (strtolower($datas[2]) === 'product' || strtolower($datas[2]) === 'service' || $datas[2] == 'DIVISI DIGITAL CONNECTIVITY SERVICE') && !empty($datas[3])) {
            $bidang = $datas[3];
        }elseif (!empty($datas[3]) && ($datas[3] == 'INFRASTRUCTURE RESEARCH & ASSURANCE')) {
            return 'IRA';
        }elseif (!empty($datas[2])) {
            $bidang = $datas[2];
        } else {
            return '';
        }
        $bidang = RemoveSpecialCharacters($bidang);
        $bidang_exploded = explode(' ', $bidang);
        if (count($bidang_exploded) == 1) {
            $bidang = strtoupper($bidang);
            if ($bidang === 'PRODUCT' || $bidang === 'SERVICE') {
                return 'DEGM';
            }
            return $bidang;
        } else {
            $string = '';
            if (strtoupper($bidang) === 'ORDER MANAGEMENT') {
                return 'ODM';
            }
            foreach ($bidang_exploded as $key => $value) {
                $string .= strtoupper(substr($value, 0, 1));
            }
            if ($string === 'ETG') {
                return 'BPP';
            }
            if ($string === 'GA') {
                return 'GAS';
            }
            if ($string === 'PPPQM') {
                return 'PQM';
            }
            if ($string === 'CDISM') {
                return 'EDI';
            }
            if ($string === 'CCISM') {
                return 'CWI';
            }
            if ($string === 'CCPM') {
                return 'CPM';
            }
            if ($string === 'DCICM') {
                return 'DCM';
            }
            return $string;
        }
    }
}
if (! function_exists('ldap_get_divisi')) {
    function ldap_get_divisi ($long_unit)
    {
        $datas = explode('/', $long_unit);
        if (!empty($datas[1])) {
            $divisi = $datas[1];
            $divisi = RemoveSpecialCharacters($divisi);
            $divisi_exploded = explode(' ', $divisi);
            if (count($divisi_exploded) == 1) {
                return $divisi;
            } else {
                $string = '';
                foreach ($divisi_exploded as $key => $value) {
                    $string .= strtoupper(substr($value, 0, 1));
                }
                return $string;
            }
        } else {
            return '';
        }
    }
}
if (! function_exists('QueryRoute')) {
    function QueryRoute($request, $force = false) {
        $QueryRoute = new \App\Support\QueryRoute($request, $force);
        return $QueryRoute->get();
    }
}
if (! function_exists('CryptText')) {
    function CryptText($text): string {
        $encryptedText = '';
        try {
            $encryptedText = \Illuminate\Support\Facades\Crypt::encryptString( (string) $text);
        } catch (\Exception $e) {

        }
        return $encryptedText;
    }
}
if (! function_exists('DecryptText')) {
    function DecryptText($encryptedText): string {
        $decrypted = '';
        try {
            $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($encryptedText);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {

        }
        return $decrypted;
    }
}


if (! function_exists('NeedCompress')) {
    function NeedCompress($filepath) {
        $filesize = filesize($filepath);
        $maxSize = 150; //in KB;
        $maxSizeMemoryCanHandle = 4000; //in KB
        $fileinKB = $filesize * 0.001;
        if ($fileinKB > $maxSize && $fileinKB <= $maxSizeMemoryCanHandle)  {
            return true;

        }
        return false;
    }
}

if (! function_exists('Compress')) {
    function Compress($image) {
        $width = 600;
        return $image->resize($width, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }
}

if (! function_exists('PermissionOwned')) {
    function PermissionOwned($rule, \Closure $isOwnClosure, $throw = true) {
        $isValid = false;
        if ($rule->own && $rule->other) {
            $isValid = true;
        } elseif (!$rule->own && !$rule->other) {
            $isValid = false;
        }

        $isOwn = call_user_func($isOwnClosure);

        if ($isOwn && $rule->own) {
            $isValid = true;
        } elseif (!$isOwn && $rule->other) {
            $isValid = true;
        } else {
            $isValid = false;
        }
        if (!$isValid && $throw) {
            ThrowPermissionDenied();
        }
        return $isValid;
    }
}

if (! function_exists('ThrowPermissionDenied')) {
    function ThrowPermissionDenied() {
        Json::set('exception.code', 'NotPermitted.Permission.Denied');
        Json::set('exception.description', trans(Json::get('exception.code')));
        Json::set('response.code', 403);
        return response()->json(Json::get(), Json::get('response.code'))->send();
    }
}

if (! function_exists('PermissionGranted')) {
    function PermissionGranted($role) {
        if (gettype($role) == 'boolean') {
            return $role;
        } elseif (gettype($role) == 'object') {
            return $role->own || $role->other;
        }
        return false;
    }
}

if (! function_exists('RemoveSpecialCharacters')) {
    function RemoveSpecialCharacters($string) {
        return preg_replace('/[^A-Za-z0-9 ]/', '', $string);
    }
}
