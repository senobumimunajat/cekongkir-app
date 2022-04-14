<?php

namespace App\Http\Controllers;

use App\City;
use App\Courier;
use App\Province;
use Illuminate\Http\Request;
use Kavist\RajaOngkir\Facades\RajaOngkir;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $province = $this->getProvince();
        $courier = $this->getCourier();
        return view('home', compact('province', 'courier'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $courier = $request->input('courier');
        if ($courier) {
            $data = [
                'origin' => $this->getCiti($request->origin_city),
                'destination' => $this->getCity($request->destination_city),
                'weight' => 1300,
                'result' => []
            ];

            foreach ($courier as $value) {
                $ongkir = RajaOngkir::ongkosKirim([
                    'origin' => $data['origin']->code,
                    'destination' => $data['destination']->code,
                    'weight' => $data['weight'],
                    'courier' => $value,
                ])->get();

                $data['result'][] = $ongkir;
            }
            return view('costs')->with($data);
        }
        return redirect()->back()->with('error', 'Pilih Kurir Terlebih Dahulu');
    }

    public function getProvince()
    {
        return Province::pluck('title', 'code');
    }
    public function getCourier()
    {
        return Courier::all();
       
    }

    public function getCity($code)
    {
        return City::where('id', $code)->first();
    }

    public function getCiti($code)
    {
        return City::where('code', $code)->first();
    }

    public function getCities($provinceId)
    {
        $cities = City::where('province_code', $provinceId)->pluck('title', 'code');
        return json_encode($cities);
    }

    public function searchCities(Request $request)
    {
        $search = trim($request->search);

        if (empty($search)) {
            $cities = City::orderBy('title', 'asc')
                ->select('id', 'title')
                ->limit(5)->get();
        } else {
            $cities = City::orderBy('title', 'asc')
                ->where('title', 'like', '%' . $search . '%')
                ->select('id', 'title')
                ->limit(5)->get();
        }

        $response  = [];

        foreach ($cities as $city) {
            $response[] = ['id' => $city->id, 'text' => $city->title];
        }

        return json_encode($response);
    }
}