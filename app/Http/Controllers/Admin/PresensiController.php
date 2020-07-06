<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Session;
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    public function pandaToken()
   	{
    	$client = new Client();

        $url = 'https://panda.unib.ac.id/api/login';
	      try {
	        $response = $client->request(
	            'POST',  $url, ['form_params' => ['email' => 'evaluasi@unib.ac.id', 'password' => 'evaluasi2018']]
	        );
	        $obj = json_decode($response->getBody(),true);
	        Session::put('token', $obj['token']);
	        return $obj['token'];
	      } catch (GuzzleHttp\Exception\BadResponseException $e) {
	        echo "<h1 style='color:red'>[Ditolak !]</h1>";
	        exit();
	      }
    }
    public function panda($query){
        $client = new Client();
        try {
            $response = $client->request(
                'POST','https://panda.unib.ac.id/panda',
                ['form_params' => ['token' => $this->pandaToken(), 'query' => $query]]
            );
            $arr = json_decode($response->getBody(),true);
            if(!empty($arr['errors'])){
                echo "<h1><i>Kesalahan Query...</i></h1>";
            }else{
                return $arr['data'];
            }
        } catch (GuzzleHttp\Exception\BadResponseException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            $res = json_decode($responseBodyAsString,true);
            if($res['message']=='Unauthorized'){
                echo "<h1><i>Meminta Akses ke Pangkalan Data...</i></h1>";
                $this->panda_token();
                header("Refresh:0");
            }else{
                print_r($res);
            }
        }
    }
    public function cariReviewer(Request $request){
       $dosen = '
       {mahasiswa {
        mhsNiu
        mhsNama
        krs(semId:20181) {
          krsId
          krsMhsNiu
          krsSempId
          krsApprovalKe
          kelas {
            klsId
            klsNama
            matakuliah {
              mkkurNamaResmi
            }
            presensi_kelas {
              presklsId
      
            }
          }
        }
      }}
        ';
        $dosens = $this->panda($dosen);
        return $dosens;
        // $datas = count($dosens['pegawai']);
        // $data = [
        //     'jumlah'    =>  $datas,
        //     'detail'    =>  $dosens,
        // ];
        // if($data['jumlah'] == 1){
        //     return response()->json($data);
        // }
    }
}