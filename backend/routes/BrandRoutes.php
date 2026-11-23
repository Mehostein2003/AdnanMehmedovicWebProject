<?php
// Brands: CRUD routes.

Flight::route('GET /brands', function () {
    Flight::json(Flight::brandService()->getAll());
});

Flight::route('GET /brands/@id:[0-9]+', function ($id) {
    Flight::json(Flight::brandService()->getById((int) $id));
});

Flight::route('POST /brands', function () {
    Flight::json(Flight::brandService()->create(jsonBody()), 201);
});

Flight::route('PUT /brands/@id:[0-9]+', function ($id) {
    Flight::json(Flight::brandService()->update((int) $id, jsonBody()));
});

Flight::route('DELETE /brands/@id:[0-9]+', function ($id) {
    Flight::brandService()->delete((int) $id);
    Flight::json(['message' => 'Brand deleted.']);
});
