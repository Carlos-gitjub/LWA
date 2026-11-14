# Test organization

Movies tests follow the application's boundaries:

- Unit/Movies/UseCases: plain PHP Interactors, with substitute lookup and output interfaces. No Laravel or real Presenters.
- Unit/Movies/Presenters: real Presenters given Output Data directly, checking display formatting and platform identity.
- Integration/Movies/HttpClients: real HTTP clients with Laravel HTTP responses simulated.
- Integration/Movies/Adapters: response mapping and provider coordination.
- Integration/Movies/MoviesAssemblyTest.php: service-provider wiring and shared Presenter resolution.
- Feature/Movies: real application routes, authenticated users created in the test database, HTTP validation and responses. MoviesAuthenticationTest separately checks guest rejection.

The feature tests keep the actual authentication and throttling middleware. Laravel's test environment bypasses CSRF checks, so these are HTTP behavior tests, not browser tests. They do not execute Vue.

External provider calls are simulated; unexpected requests are blocked. These tests do not check live provider availability. Database-backed tests use the in-memory SQLite database configured in phpunit.xml.

Run the full suite with php vendor/bin/phpunit. Run only framework-independent Movies tests with php vendor/bin/phpunit tests/Unit/Movies.

Existing authentication, profile, and example tests retain their previous organization.

Library follows the same division under Unit/Library, Integration/Library, and Feature/Library. Its HTTP tests use real routes, in-memory SQLite, fake storage, and locally generated PDFs with the real parser. Unit tests cover save-failure cleanup without Laravel.
