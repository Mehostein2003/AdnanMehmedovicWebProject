<?php
// Vehicles: CRUD routes and the external API endpoint (factory specs from the VIN).

Flight::route('GET /vehicles', function () {
    Flight::json(Flight::vehicleService()->getAll());
});

Flight::route('GET /vehicles/@id:[0-9]+', function ($id) {
    Flight::json(Flight::vehicleService()->getById((int) $id));
});

// External API (NHTSA vPIC): specs decoded from the VIN of this vehicle.
Flight::route('GET /vehicles/@id:[0-9]+/specs', function ($id) {
    Flight::json(Flight::vehicleService()->getSpecs((int) $id));
});

Flight::route('POST /vehicles', function () {
    Flight::json(Flight::vehicleService()->create(jsonBody()), 201);
});

Flight::route('PUT /vehicles/@id:[0-9]+', function ($id) {
    Flight::json(Flight::vehicleService()->update((int) $id, jsonBody()));
});

Flight::route('DELETE /vehicles/@id:[0-9]+', function ($id) {
    Flight::vehicleService()->delete((int) $id);
    Flight::json(['message' => 'Vehicle deleted.']);
});
