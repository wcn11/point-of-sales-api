<?php


namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait AccurateService{

//    public function __construct()
//    {
//
//    }

    public function sendGet($url = "", $authorizationType = "Bearer "){
        return Http::withHeaders([
            'Authorization' => $authorizationType . " " . env("ACCURATE_ACCESS_TOKEN"),
            'X-Session-ID' => env('ACCURATE_SESSION_ID'),
        ])->get($url);
    }

    public function sendPost($url = "", $data = [], $bodyType = "form", $authorizationType = "Bearer "){

        if ($bodyType === "form"){
            return Http::withHeaders([
                'Authorization' => $authorizationType . " " . env("ACCURATE_ACCESS_TOKEN"),
                'X-Session-ID' => env('ACCURATE_SESSION_ID'),
            ])->asForm()->post($url, $data);
        }

        return Http::withHeaders([
            'Authorization' => $authorizationType . " " . env("ACCURATE_ACCESS_TOKEN"),
            'X-Session-ID' => env('ACCURATE_SESSION_ID'),
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
