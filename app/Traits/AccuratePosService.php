<?php


namespace App\Traits;

use App\Models\Accurate;
use App\Models\User;
use Illuminate\Support\Facades\Http;

trait AccuratePosService{

    /**
     * @var Accurate
     */
    private $accurate;

    public function __construct(Accurate $accurate)
    {
        $this->accurate = Accurate::all()->first();;
    }

    public function credentials(): void{
        $accurate = Http::withHeaders([
            'Authorization' => "Basic " . base64_encode(env("ACCURATE_CLIENT_ID") . ":" . env("ACCURATE_CLIENT_SECRET")),
        ])->asForm()->post(env("ACCURATE_HOST_DASAR") . "/oauth/token", [
            "code" => env("ACCURATE_OAUTH_CODE"),
            "grant_type" => "authorization_code",
            "redirect_uri" => env("ACCURATE_REDIRECT_URI")
        ]);

        Accurate::create([
            'access_token' => $accurate['access_token'],
            'refresh_token' => $accurate['refresh_token'],
        ]);
    }

    public function sendGet($url, $authorizationType = "Bearer "){
        $accurate = Accurate::all()->first();

        $response = Http::withHeaders([
            'Authorization' => $authorizationType . " " . $accurate['access_token'],
            'X-Session-ID' => $accurate['session_id'],
        ])->get( $accurate['database_host'] . $url);

        if(isset($response['s']) && $response['s'] === true){
            return $response;
        }


        try {

            if ($response->failed()){


                if(isset($response['s']) && $response['s'] === false){

                    return $this->checkSessionDB($url, __FUNCTION__);

                }

                    $data = simplexml_load_string($response);

                    $error = json_encode($data);

                    if (isset(json_decode($error, true)['error']) || isset(json_decode($error, true)['error']) == "invalid_token"){

                        return $this->refresh_token($url, __FUNCTION__);

                    }

            }
        }catch (\Exception $e){

            $response = [
                "system_error" => true,
                "message" => "Kegagalan Sistem Accurate, Harap Hubungi Administrator"
            ];

        }

        return $response;

    }

    private function refresh_token($url, $function)
    {
        $accurate_config = Accurate::all()->first();

        $accurate = Http::withHeaders([
            'Authorization' => "Basic " . base64_encode(env("ACCURATE_CLIENT_ID") . ":" . env("ACCURATE_CLIENT_SECRET")),
        ])->asForm()->post(env("ACCURATE_HOST_DASAR") . "/oauth/token", [
            "grant_type" => "refresh_token",
            "refresh_token" => $accurate_config['refresh_token']
        ]);

        $accurate_config->update([
            'access_token' => $accurate['access_token'],
            'refresh_token' => $accurate['refresh_token'],
        ]);

        return $this->checkSessionDB($url, $function);
    }

    public function checkSessionDB($url, $function){

        $accurate_config = Accurate::all()->first();

        $response = Http::withHeaders([
            'Authorization' => "Bearer " . $accurate_config['access_token']
        ])->get( env("ACCURATE_HOST_DASAR") . "/api/db-check-session.do?session=" . $accurate_config['session_id']);

        if ($response['s'] && $response['d'] === false){

            return $this->refreshSessionDB($url, $function);

        }

        return $this->$function($url);

    }

    private function refreshSessionDB($url, $function){

        $accurate_config = Accurate::all()->first();

        $response = Http::withHeaders([
            'Authorization' => "Bearer " . $accurate_config['access_token']
        ])->get( env("ACCURATE_HOST_DASAR") . "/api/db-refresh-session.do?id=398334&session=" . $accurate_config['session_id']);

        if($response['s'] && $response['d']['session']){

            $accurate_config->update([
                'database_host' => $response['d']['host'],
                'session_id' => $response['d']['session'],
            ]);

        }

        return $this->$function($url);
    }


    public function sendPost($url = "", $data = [], $bodyType = "form", $authorizationType = "Bearer "){

        $accurate = Accurate::all()->first();

        if ($bodyType === "form"){
            $response = Http::withHeaders([
                'Authorization' => $authorizationType . " " . $accurate['access_token'],
                'X-Session-ID' => $accurate['session_id'],
            ])->asForm()->post($accurate['database_host'] . $url, $data);

        }else{

            $response = Http::withHeaders([
                'Authorization' => $authorizationType . " " . $accurate['access_token'],
                'X-Session-ID' => $accurate['session_id'],
            ])->asJson()->post($accurate['database_host'] . $url, $data);

        }

        if(isset($response['s']) && $response['s'] === true){
            return $response;
        }

        if ($response->failed()){


            if(isset($response['s']) && $response['s'] === false){

                return $this->checkSessionDB($url, __FUNCTION__);

            }

            $data = simplexml_load_string($response);

            $error = json_encode($data);

            if (isset(json_decode($error, true)['error']) || isset(json_decode($error, true)['error']) == "invalid_token"){

                return $this->refresh_token($url, __FUNCTION__);

            }

        }

        return $response;

    }

    public function sendDelete($url = "", $data = 0, $bodyType = "form", $authorizationType = "Bearer "){

        $accurate = Accurate::all()->first();

        if ($bodyType === "form"){
            $response = Http::withHeaders([
                'Authorization' => $authorizationType . " " . $accurate['access_token'],
                'X-Session-ID' => $accurate['session_id'],
            ])->asForm()->post($accurate['database_host'] . $url, $data);
        }else{

            $response = Http::withHeaders([
                'Authorization' => $authorizationType . " " . $accurate['access_token'],
                'X-Session-ID' => $accurate['session_id'],
            ])->asJson()->post($accurate['database_host'] . $url, $data);

        }

        if(isset($response['s']) && $response['s'] === true){
            return $response;
        }

        if ($response->failed()){


            if(isset($response['s']) && $response['s'] === false){

                return $this->checkSessionDB($url, __FUNCTION__);

            }

            $data = simplexml_load_string($response);

            $error = json_encode($data);

            if (isset(json_decode($error, true)['error']) || isset(json_decode($error, true)['error']) == "invalid_token"){

                return $this->refresh_token($url, __FUNCTION__);

            }

        }

        return $response;

    }

    public function getDatabaseById($url = "", $authorizationType = "Bearer "){

        $accurate = Accurate::all()->first();

        $response = Http::withHeaders([
            'Authorization' => $authorizationType . " " . $accurate['access_token'],
        ])->get($url);

        if (isset($response['s']) && $response['s'] === true){
            $accurate->update([
                "database_host" => $accurate['host'],
                "session_id" => $accurate['session']
            ]);
        }

        if ($response->failed()){


            if(isset($response['s']) && $response['s'] === false){

                return $this->checkSessionDB($url, __FUNCTION__);

            }

            $data = simplexml_load_string($response);

            $error = json_encode($data);

            if (isset(json_decode($error, true)['error']) || isset(json_decode($error, true)['error']) == "invalid_token"){

                return $this->refresh_token($url, __FUNCTION__);

            }

        }

        return $response;
    }
}
