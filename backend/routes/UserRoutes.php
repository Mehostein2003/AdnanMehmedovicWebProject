<?php
// Users: CRUD routes. A route reads the request, calls the service and returns JSON.
// No SQL and no business logic here.

Flight::route('GET /users', function () {
    Flight::json(Flight::userService()->getAll());
});

Flight::route('GET /users/@id:[0-9]+', function ($id) {
    Flight::json(Flight::userService()->getById((int) $id));
});

Flight::route('POST /users', function () {
    Flight::json(Flight::userService()->create(jsonBody()), 201);
});

Flight::route('PUT /users/@id:[0-9]+', function ($id) {
    Flight::json(Flight::userService()->update((int) $id, jsonBody()));
});

Flight::route('DELETE /users/@id:[0-9]+', function ($id) {
    Flight::userService()->delete((int) $id);
    Flight::json(['message' => 'User deleted.']);
});
