<?php


namespace App\Traits;

use App\Models\Accurate;
use Illuminate\Support\Facades\Http;

trait AccurateService{

    /**
     * @var Accurate
     */
    private $accurate;

    public function __construct(Accurate $accurate)
    {
        $this->accurate = Accurate::all()->first();
    }

    public function sendGet($url = "", $authorizationType = "Bearer "){

        $accurate = Accurate::all()->first();

        $response = Http::withHeaders([
            'Authorization' => $authorizationType . " " . $accurate['access_token']
        ])->get(env("ACCURATE_HOST_DASAR") . $url);

        if(isset($response['s'])){
            return $response;
        }

        if ($response->failed()){
            $data = simplexml_load_string($response);

            $error = json_encode($data);

            if (isset(json_decode($error, true)['error']) || isset(json_decode($error, true)['error']) == "invalid_token"){
                $this->refresh_token($accurate['refresh_token'], $url);
            }
        }

        return $response;
    }

    public function refresh_token($refresh_token, $url)
    {
        $accurate_config = Accurate::all()->first();

        $accurate = Http::withHeaders([
            'Authorization' => "Basic " . base64_encode(env("ACCURATE_CLIENT_ID") . ":" . env("ACCURATE_CLIENT_SECRET")),
        ])->asForm()->post(env("ACCURATE_HOST_DASAR") . "/oauth/token", [
            "grant_type" => "refresh_token",
            "refresh_token" => $refresh_token
        ]);

        $accurate_config->update([
            'access_token' => $accurate['access_token'],
            'refresh_token' => $accurate['refresh_token'],
        ]);

        return $this->sendGet($url);
    }

    public function sendPost($url = "", $data = [], $bodyType = "form", $authorizationType = "Bearer "){

        if ($bodyType === "form"){
            return Http::withHeaders([
                'Authorization' => $authorizationType . " " . env("ACCURATE_ACCESS_TOKEN"),
                'X-Session-ID' => auth('api-admin')->user()['session_database_key'],
            ])->asForm()->post($url, $data);
        }

        return Http::withHeaders([
            'Authorization' => $authorizationType . " " . env("ACCURATE_ACCESS_TOKEN"),
            'X-Session-ID' => auth('api-admin')->user()['session_database_key'],
        ])->asJson()->post($url, $data);

    }

//    public function checkSession(){
//
//        return $this->sendCheck(env('ACCURATE_HOST_DASAR') . '/api/db-list.do');
//
//    }
//
//    public function sendCheck($url = "", $authorizationType = "Bearer ", $method = "get")
//    {
//         $response = Http::withHeaders([
//            'Authorization' => $authorizationType . " " . env("ACCURATE_ACCESS_TOKEN"),
//        ])->get($url);
//
//         if ($response->failed()){
//             return $response->status();
//         }
//
//         if ($response->json()['s']){
//
//             if ($response->json(['d'][0]['expired'])){
//
//                 $this->refreshSession();
//             }
//
//         }
//
//         return $response->json();
//    }
//
//    private function refreshSession(){
//
//    }
}
