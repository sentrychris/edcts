# Changelog

All notable changes to EDCTS are documented here. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

---

## [2026-04-12]

### Changed
- **Database** — Converted `systems.coords_x/y/z` from stored generated columns (derived via `JSON_EXTRACT` from a JSON blob) to native `FLOAT` columns. The `coords` JSON column is now dropped. All write paths (import jobs, EDSM/EDDN services) and read paths (API resources, raw SQL selects) updated accordingly.
- **Database** — Removed two redundant indexes on `systems.name`: the fulltext index and the composite `(name, id64)` index. The `name` unique index and the `slug` index are retained. The `(coords_x, coords_y, coords_z)` bounding-box index is rebuilt on the native columns.

---

## [2026-04-11]

### Added
- **Commander** — New `PUT /api/commander` endpoint for updating the authenticated commander's profile (name, home system).
- **OpenAPI/Swagger** — Full Swagger annotations added across all controllers; documentation generated and committed.
- **Tests** — PHPUnit feature test suite for the distance search endpoint (`SearchSystemByDistanceTest`): covers slug/coordinate parity, range exclusion, ordering, and validation rules.

### Changed
- **Distance endpoint** — `GET /api/system/search/distance` now accepts a `slug` parameter as an alternative origin to raw `x/y/z` coordinates.
- **Slug endpoint** — `GET /api/system/{slug}` overhauled; slug format updated to omit the `id64` prefix from the display route.
- **Console commands** — Renamed for clarity: `ImportDumpFile` → `ImportDumpFileCommand`, `EddnListen` → `EddnListenCommand`, `CacheStatistics` → `CacheStatisticsCommand`, `ImportGalnet` → `ImportGalnetArticlesCommand`. `CachePages` command and `CacheSystemsPages` job removed.
- **Routing** — Commander and Frontier auth routes reorganised; `CommanderController` extracted.
- **Docs** — README, API overview, and Postman collection updated.

---

## [2026-04-10]

### Added
- **Route finder** — A\* pathfinding service (`NavRouteFinderService`) exposed via `GET /api/system/search/route`; accepts `from` slug, `to` slug, and `ly` jump range. PHPUnit feature tests cover direct routes, multi-hop routes, unreachable destinations, and validation.
- **DiscordAlert facade** — `DiscordAlertService` extracted from inline calls into a dedicated service with a `DiscordAlert` facade accessor, making alert dispatching uniform across all services.
- **Stored generated coord columns** — `coords_x`, `coords_y`, `coords_z` added to `systems` as `STORED` generated columns (computed from `JSON_EXTRACT(coords, '$.x/y/z')`) with a compound `(coords_x, coords_y, coords_z)` index for bounding-box pre-filtering on distance queries.

### Changed
- **Statistics** — `UseStatistics` trait converted to `StatService` class.
- **Large file split** — JSON streaming logic moved from a trait into a dedicated service.
- **EDDN/EDSM services** — Consistency and stability improvements; Discord alert integration throughout.
- **Galnet** — RSS ingestion removed; `GalnetRssService` dropped. JSON-based `GalnetNewsService` retained.

---

## [2026-04-09]

### Added
- **Commodity mappings** — Extended the commodity name normalisation config with additional EDDN → display name mappings.

### Changed
- **Route finder** — Initial `RouteFinderService` scaffolded with request/resource/factory/migration in preparation for the A\* implementation.

---

## [2026-04-08]

### Added
- New system lookup endpoint returning a system by slug or name.

---

## [2026-04-02 to 2026-04-07]

### Fixed
- Caching configuration issue in the import command and system controller.

---

## [2026-03-30 to 2026-03-31]

### Changed
- **Framework** — Upgraded Laravel v11 → v13.
- **PHP** — Docker image updated to PHP 8.4.
- **Dependencies** — Replaced deprecated `->get()` method calls throughout; removed unused packages.
- Added Laravel Boost and AI development guidelines (`CLAUDE.md`).

---

## [2024-12-06]

### Changed
- Minor EDSM API service updates.

---

## [2024-09-01 to 2024-09-13]

### Added
- **FDevIDs** — Added as a git submodule; `Shipyard` model and migration created to hold ship/module reference data.
- **Telescope** — Laravel Telescope installed and configured for monitoring; auth middleware applied; filters tuned to reduce noise.
- **Shipyard seeder** — Seed data populated from FDevIDs.

### Changed
- **Frontier OAuth** — PKCE code verifier now validated against the OAuth `state` parameter in the callback, closing a CSRF window.
- **Market data** — EDDN commodity data cached in Redis with a 5-minute rolling TTL instead of expiring immediately; `last_updated` field set on replace.
- **Stations** — EDSM station import updated; body association added to stations response; fleet carriers remain excluded.
- **Commodity mappings** — Extensive expansion of EDDN → display-name normalisation table.
- **CORS** — Allowed origins updated for production frontend.
- **Cache** — System list cache pre-population limited to first 1 000 pages.

---

## [2024-08-29 to 2024-08-31]

### Added
- **Frontier cAPI (BFF)** — Full OAuth2 + PKCE authorisation code flow against the Frontier Developments cAPI:
  - `FrontierAuthController` handles `/auth/frontier/login` and `/auth/frontier/callback`.
  - Access token stored in a Redis-backed BFF cookie; `GET /api/token` exposes it to the frontend.
  - `FrontierCApiController` proxies authenticated cAPI requests: commander profile, `me`, and journal.
  - `FrontierAuthMiddleware` guards cAPI routes via Sanctum.
  - Commander display name and CMDR ID synced to the database on login.
  - EDSM queried as a fallback if the commander's current system is not yet in the local database.
- **cAPI journal endpoint** — `GET /api/frontier/capi/journal` returns the commander's in-game journal.
- **Commander profile columns** — `capi_*` columns added to `commanders` table to store Frontier profile data.
- **Galnet** — `ordering` column added to Galnet news table.

### Changed
- User email and display name anonymised to prevent storing Frontier account PII.
- Token handling moved from cookies to Sanctum-issued tokens stored in the database.
- CORS origins updated.

---

## [2024-08-15 to 2024-08-18]

### Added
- **`body_count` column** — Added to `systems` table and exposed in the API response.

### Changed
- **Framework** — Upgraded Laravel v10 → v11.
- **Systems import** — Import logic extracted from the Artisan command into a dedicated `ImportSystemsDumpFileJob` queued job; supports batch processing of large EDSM galaxy dump files (configurable batch size, streaming JSON parse via `json-machine`).
- **Import helpers** — Autoloaded helper functions; large file splitting logic improved.

---

## [2024-03-23 to 2024-03-24]

### Added
- **Flight log** — `FlightLog` model and migration (`flight_log` table); `FlightLogController` with store/index endpoints; `FlightLogRequest` validation. Associated with the `Commander` model.

---

## [2023-11-25]

### Added
- System and station data cached per-record in the application layer; cache keys scoped by `id64`.

---

## [2023-08-01 to 2023-08-17]

### Added
- **System bodies** — Full `SystemBodyController` with search and filtering; `SearchSystemBodyRequest`; bodies included in the system show response.
- **Station market endpoint** — `GET /api/market/{station}` returning commodity data from EDDN.
- **Redis market cache** — EDDN commodity data cached per-system in Redis.
- **Market data resource** — `MarketDataResource` for structured commodity responses.
- **Station commodity data** — EDDN commodity service expanded; commodity-to-display-name mapping config added.

### Changed
- Fleet carriers filtered out of the station import; transient carrier records are skipped.
- Unique composite constraint added to the `system_stations` table to prevent duplicate station records.
- Station body association improved.

### Fixed
- Station import edge cases around nullable station type.

---

## [2023-07-13 to 2023-07-30]

### Added
- **Project foundation** — Laravel application scaffolded with Sanctum authentication, Sail Docker environment, API routing.
- **Authentication** — User registration and login (`AuthController`); Sanctum token issuance; `HasCommander` middleware.
- **Commander** — `Commander` model and resource; linked one-to-one with `User`.
- **Fleet carriers** — `FleetCarrier` model, endpoints (CRUD + search), scheduling (`FleetSchedule`); fleet carrier import from EDDN.
- **Systems** — `System` model with EDSM import, search/filter endpoints, and URL slug generation (`cviebrock/eloquent-sluggable`); `SystemInformation` relation.
- **System bodies** — Initial `SystemBody` model, migration, and resource.
- **System stations** — `SystemStation` model, migration, EDSM import, CRUD endpoints, filter by station type.
- **Galnet news** — `GalnetNews` model; JSON ingestion from the Frontier Galnet API; scheduled import command; cached response endpoint.
- **Statistics** — Request counting, scheduling, and a dedicated statistics channel for logging.
- **API documentation** — OpenAPI/Swagger annotations; Postman collection.
- **Strict types** — `declare(strict_types=1)` applied throughout.
