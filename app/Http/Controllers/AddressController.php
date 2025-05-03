<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    private $status = 200;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'street'       => 'nullable|string|max:255',
            'barangay'     => 'nullable|string|max:255',
            'city'         => 'required|string|max:255',
            'province'     => 'required|string|max:255',
            'postalCode'  => 'nullable|string|max:20',
            'country'      => 'nullable|string|max:100',

            'building'     => 'nullable|string|max:255',
            'landmark'     => 'nullable|string|max:255',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',

            'userId'      => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 405);
        }

        $address = new Address();
        $address->user_id = $request->userId;
        $address->street = $request->street;
        $address->barangay = $request->barangay;
        $address->city = $request->city;
        $address->province = $request->province;
        $address->postal_code = $request->postalCode;
        $address->country = $request->country;
        $address->building = $request->building;
        $address->landmark = $request->landmark;
        $address->latitude = $request->latitude;
        $address->longitude = $request->longitude;

        $address->save();

        $response = [
            'message' => 'Address Added!',
            'status' => $this->status
        ];

        return response($response, $this->status);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
