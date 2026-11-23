<?php
// Reviews: CRUD routes.

Flight::route('GET /reviews', function () {
    Flight::json(Flight::reviewService()->getAll());
});

Flight::route('GET /reviews/@id:[0-9]+', function ($id) {
    Flight::json(Flight::reviewService()->getById((int) $id));
});

Flight::route('POST /reviews', function () {
    Flight::json(Flight::reviewService()->create(jsonBody()), 201);
});

Flight::route('PUT /reviews/@id:[0-9]+', function ($id) {
    Flight::json(Flight::reviewService()->update((int) $id, jsonBody()));
});

Flight::route('DELETE /reviews/@id:[0-9]+', function ($id) {
    Flight::reviewService()->delete((int) $id);
    Flight::json(['message' => 'Review deleted.']);
});
