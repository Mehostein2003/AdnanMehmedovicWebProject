<?php
// Test drives: CRUD routes. The URL uses a hyphen: /test-drives.

Flight::route('GET /test-drives', function () {
    Flight::json(Flight::testDriveService()->getAll());
});

Flight::route('GET /test-drives/@id:[0-9]+', function ($id) {
    Flight::json(Flight::testDriveService()->getById((int) $id));
});

Flight::route('POST /test-drives', function () {
    Flight::json(Flight::testDriveService()->create(jsonBody()), 201);
});

Flight::route('PUT /test-drives/@id:[0-9]+', function ($id) {
    Flight::json(Flight::testDriveService()->update((int) $id, jsonBody()));
});

Flight::route('DELETE /test-drives/@id:[0-9]+', function ($id) {
    Flight::testDriveService()->delete((int) $id);
    Flight::json(['message' => 'Test drive deleted.']);
});
