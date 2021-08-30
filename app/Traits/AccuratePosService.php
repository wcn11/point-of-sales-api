<?php


namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Http;

trait AccuratePosService{

    public function __construct()
    {

    }

    public function sendGet($url = "", $authorizationType = "Bearer "){
        return Http::withHeaders([
            'Authorization' => $authorizationType . " " . env("ACCURATE_ACCESS_TOKEN"),
            'X-Session-ID' => auth()->user()['session_database_key'],
        ])->get( auth()->user()['session_database_host'] . $url);
    }

    public function sendPost($url = "", $data = [], $bodyType = "form", $authorizationType = "Bearer "){

        if ($bodyType === "form"){
            return Http::withHeaders([
                'Authorization' => $authorizationType . " " . env("ACCURATE_ACCESS_TOKEN"),
                'X-Session-ID' => auth()->user()['session_database_key'],
            ])->asForm()->post(auth()->user()['session_database_host'] . $url, $data);
        }

        return Http::withHeaders([
            'Authorization' => $authorizationType . " " . env("ACCURATE_ACCESS_TOKEN"),
            'X-Session-ID' => auth()->user()['session_database_key'],
        ])->asJson()->post(auth()->user()['session_database_host'] . $url, $data);

    }

    public function getDatabaseById($url = "", $authorizationType = "Bearer "){
        $accurate = Http::withHeaders([
            'Authorization' => $authorizationType . " " . env("ACCURATE_ACCESS_TOKEN"),
        ])->get($url);

        if ($accurate['s']){
            User::find(auth()->user()['id'])->update([
                "session_database_host" => $accurate['host'],
                "session_database_key" => $accurate['session']
            ]);
        }

        return $accurate;
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
