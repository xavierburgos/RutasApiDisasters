<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\DisasterStoreRequest;
use App\Models\Disaster;
use App\Models\DisasterServiceDamageLevel;

use Illuminate\Support\Facades\DB;

use Exception;

class DisasterController extends Controller
{

    public function index() {
        try {         
          
            $disaster = Disaster::paginate(1);
          
            return response()->json($disaster);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show(Request $request, $disaster) {
        try {
            $disaster = Disaster::selectRaw("*, ST_AsText(location) as lstring")->where('id', $disaster)
                ->with('damage_level', 'disaster_type')
                ->first();

            //$disaster->load('damage_level', 'disaster_type_all_fields');
            
            return response()->json($disaster);
        } catch(Exception $e) {

        }
    }

    public function store(DisasterStoreRequest $request) {
        try {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $request->merge(['location' => DB::raw("St_GeomFromText('Point($longitude $latitude)')")]);

            $disaster = Disaster::create($request->all());

            foreach($request->input('disaster_services', []) as $disaster_service) {
                $disaster->disasterServices()->create($disaster_service);
            }

            return response()->json($disaster);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function update(DisasterStoreRequest $request, Disaster $disaster)
    {       
        try{

            $disaster->disaster_type_id=$request->disaster_type_id;
            $disaster->city_id=$request->city_id;
            $disaster->damage_level_id=$request->damage_level_id;
            $disaster->location=$request->location;
            $disaster->casualties=$request->casualties;
            $disaster->description=$request->description;
            $disaster->save();
            $data = [
                'message' => 'Se guardo correctamente',
                'disaster' => $disaster
            ];
            return response()->json($data);

        }catch (Exception $e) {
            return response()->json(['Error no se pudo actualizar' => $e->getMessage()]);
        }
    }
}
