# EDCS API Overview

The **Elite Dangerous Companion Suite (EDCS) API** is the backend data service for [edcs.app](https://edcs.app). It aggregates, stores, and serves data from the Elite Dangerous galaxy — systems, celestial bodies, stations, markets, and in-game news — to the EDCS frontend and any other authorised consumers.

Data is sourced from:
- **EDSM** (Elite Dangerous Star Map) — the canonical source for system, body, and station data
- **EDDN** (Elite Dangerous Data Network) — real-time commodity market data pushed from in-game
- **Frontier CAPI** (Companion API) — authenticated player/commander data direct from Frontier Developments

---

## Base URL

```
{{url}} = http://localhost/api  (local dev)
```

---

## Authentication

Most read endpoints are public. Write/delete operations and commander-specific endpoints require a bearer token.

Two authentication flows are supported:

**Standard (email/password)** — intended for admin/internal use. Returns a Laravel Sanctum token.

**Frontier SSO (OAuth 2.0 PKCE)** — the primary player auth flow. Redirects through Frontier's login page and stores a Sanctum token in an `HttpOnly` cookie (`cmdr_token`).

Tokens are passed as `Authorization: Bearer <token>`.

---

## Endpoint Categories

---

### Auth

#### Standard Auth

| Method | Endpoint | Auth required |
|--------|----------|---------------|
| `POST` | `/auth/register` | No |
| `POST` | `/auth/login` | No |
| `POST` | `/auth/logout` | Yes |
| `GET` | `/auth/me` | Yes |

**`POST /auth/register`** — Creates a new user account. Returns a Sanctum token.

```json
// Request
{ "name": "Chris Rowles", "email": "me@rowles.ch", "password": "secret123" }

// Response
{ "name": "Chris Rowles", "token": "1|abc123...", "expiry": 3600 }
```

**`GET /auth/me`** — Returns the authenticated user with their in-game commander name and linked third-party API keys (EDSM, Inara).

```json
{
  "id": 1,
  "name": "Chris Rowles",
  "email": "me@rowles.ch",
  "commander": {
    "name": "CMDR Rowles",
    "api": { "edsm": "abc123", "inara": "xyz789" }
  }
}
```

---

#### Frontier SSO

| Method | Endpoint | Auth required |
|--------|----------|---------------|
| `GET` | `/auth/frontier/login` | No |
| `GET` | `/auth/frontier/callback` | No |
| `POST` | `/auth/frontier/me` | Cookie |

**`GET /auth/frontier/login`** — Returns the Frontier OAuth authorization server metadata (auth URL, token endpoint, PKCE parameters). The frontend uses this to construct the PKCE login redirect.

**`GET /auth/frontier/callback?code=...&code_verifier=...`** — Handles the OAuth callback from Frontier. Exchanges the authorization code for an access token, creates/confirms the user and commander record, then redirects to the frontend with a `cmdr_token` cookie.

**`POST /auth/frontier/me`** — Returns the authenticated user (via `cmdr_token` cookie) with their commander data and current token.

---

#### Frontier CAPI *(requires Sanctum bearer token)*

| Method | Endpoint |
|--------|----------|
| `GET` | `/frontier/capi/profile` |
| `GET` | `/frontier/capi/journal` |

**`GET /frontier/capi/profile`** — Fetches and returns the authenticated commander's live profile from the Frontier CAPI, confirming and updating the stored commander record.

**`GET /frontier/capi/journal`** — Retrieves the commander's in-game journal for a specific date (`year`, `month`, `day` query parameters).

---

### GalNet

In-game news from the GalNet feed, set in the 34th century.

| Method | Endpoint |
|--------|----------|
| `GET` | `/galnet/news` |
| `GET` | `/galnet/news/:slug` |
| `DELETE` | `/galnet/news/:id` |

**`GET /galnet/news`** — Paginated list of GalNet articles, most recent first.

**`GET /galnet/news/16-aug-3310-the-assault-on-thor`** — A single article by its URL slug (format: `{date}-{title-kebab}`).

```json
{
  "id": 42,
  "title": "The Assault on Thor",
  "content": "<p>In a bold manoeuvre...</p>",
  "audio_file": "https://...",
  "uploaded_at": "3310-08-16T00:00:00Z",
  "banner_image": "https://...",
  "slug": "16-aug-3310-the-assault-on-thor"
}
```

**`DELETE /galnet/news/:id`** — Removes a news article. Requires admin authentication.

---

### Statistics

| Method | Endpoint |
|--------|----------|
| `GET` | `/statistics` |

**`GET /statistics`** — Returns aggregate database statistics (system, body, and station counts). Results are cached and refreshed on a schedule; pass `?resetCache=1` to force a refresh.

```json
{
  "data": {
    "systems": 123456,
    "bodies": 789012,
    "stations": 34567
  }
}
```

---

### Systems (Collection)

Search and browse the galaxy's star systems.

| Method | Endpoint |
|--------|----------|
| `GET` | `/systems` |
| `GET` | `/systems/:slug` |

**`GET /systems`** — Paginated list of systems. Supports several query parameters:

| Parameter | Description |
|-----------|-------------|
| `name` | Filter by system name (partial match by default) |
| `exactSearch=1` | Require an exact name match |
| `withInformation=1` | Embed political/demographic information |
| `withBodies=1` | Embed celestial bodies |
| `withStations=1` | Embed stations and outposts |
| `limit` | Page size |

**`GET /systems/10477373803-sol`** — Retrieves a single system by its slug (`{id64}-{name}`). If not in the local database, the API transparently queries EDSM and stores the result. Accepts the same `with*` parameters as the list endpoint.

```json
{
  "id": 1,
  "id64": 10477373803,
  "name": "Sol",
  "coords": { "x": 0, "y": 0, "z": 0 },
  "slug": "10477373803-sol",
  "updated_at": "2024-01-15T12:00:00Z",
  "information": {
    "allegiance": "Federation",
    "government": "Democracy",
    "population": 22780000000,
    "security": "High",
    "economy": "Refinery",
    "controlling_faction": { "name": "Mother Gaia", "state": "None" }
  },
  "bodies": [...],
  "stations": [...]
}
```

---

### System (Utilities & Search)

| Method | Endpoint |
|--------|----------|
| `GET` | `/system/last-updated` |
| `GET` | `/system/id64s` |
| `GET` | `/system/search/distance` |
| `GET` | `/system/search/route` |
| `GET` | `/system/search/information` |

**`GET /system/last-updated`** — Returns the most recently updated system, including its bodies and information. Useful for monitoring data freshness.

**`GET /system/id64s`** — Streams a large JSON object listing every system `id64`. Delivered as a chunked stream to avoid memory limits. Used by consumers that need the full system index.

**`GET /system/search/distance?x=0&y=0&z=0&ly=100`** — Finds all systems within `ly` light years of galactic coordinates `(x, y, z)`. Results include the calculated distance from the origin point.

```json
[
  { "name": "Sol", "coords": { "x": 0, "y": 0, "z": 0 }, "distance": 0.0, "slug": "10477373803-sol" },
  { "name": "Alpha Centauri", "coords": { "x": 3.03, "y": -0.09, "z": 3.16 }, "distance": 4.38, "slug": "5068464797-alpha-centauri" }
]
```

**`GET /system/search/route?from=8216113749-maia&to=670685668665-pleiades-sector-ag-n-b7-0&ly=40`** — Computes the shortest jump route between two systems within a given jump range. Returns an ordered list of waypoints with per-hop and cumulative distances.

```json
[
  { "jump": 0, "name": "Maia", "distance": 0.0, "total_distance": 0.0, "slug": "8216113749-maia" },
  { "jump": 1, "name": "Pleiades Sector IH-V c2-5", "distance": 38.2, "total_distance": 38.2, "slug": "..." },
  { "jump": 2, "name": "Pleiades Sector AG-N b7-0", "distance": 31.7, "total_distance": 69.9, "slug": "670685668665-pleiades-sector-ag-n-b7-0" }
]
```

**`GET /system/search/information?population=5000000000&security=high&government=Dictato`** — Filters systems by political and demographic attributes. All filters are partial-match. Supports `with*` relation parameters.

| Parameter | Description |
|-----------|-------------|
| `population` | Minimum population (≥) |
| `security` | Security level (`high`, `medium`, `low`, `anarchy`) |
| `government` | Government type (partial match) |
| `allegiance` | Allegiance (partial match) |
| `economy` | Economy type (partial match) |

---

### Bodies

Celestial bodies (stars, planets, moons) within systems.

| Method | Endpoint |
|--------|----------|
| `GET` | `/bodies/:slug` |

**`GET /bodies/108086401534265707-earth?withSystem=1`** — Retrieves a single body by slug. The slug format is `{body_id}-{name}`. Pass `withSystem=1` to embed the parent system.

```json
{
  "id": 1,
  "name": "Earth",
  "type": "Planet",
  "sub_type": "Earth-like world",
  "distance_to_arrival": 500,
  "is_landable": false,
  "is_main_star": false,
  "spectral_class": null,
  "gravity": 1.0,
  "earth_masses": 1.0,
  "surface_temp": 288,
  "atmosphere_type": "Suitable for water-based life",
  "terraforming_state": "Already terraformed",
  "discovery": { "commander": "Whoever", "date": "3302-06-01" },
  "slug": "108086401534265707-earth"
}
```

---

### Stations (Collection)

Stations, outposts, and megaships orbiting bodies within systems.

| Method | Endpoint |
|--------|----------|
| `GET` | `/stations/:slug` |

**`GET /stations/128016384-daedalus?withSystem=1`** — Retrieves a single station by slug. Pass `withSystem=1` to include the parent system and its bodies.

```json
{
  "id": 1,
  "name": "Daedalus",
  "type": "Orbis Starport",
  "body": "Sol",
  "distance_to_arrival": 508,
  "controlling_faction": "Sol Constitution Party",
  "allegiance": "Federation",
  "government": "Democracy",
  "economy": "Industrial",
  "second_economy": "Refinery",
  "has_market": true,
  "has_shipyard": true,
  "has_outfitting": true,
  "other_services": ["Restock", "Repair", "Contacts"],
  "last_updated": {
    "information": "2024-01-10T08:00:00Z",
    "market": "2024-01-15T14:30:00Z",
    "shipyard": "2024-01-12T10:00:00Z",
    "outfitting": "2024-01-12T10:00:00Z"
  },
  "slug": "128016384-daedalus"
}
```

---

### Station (Market)

Real-time commodity market data sourced from EDDN.

| Method | Endpoint |
|--------|----------|
| `GET` | `/station/:slug/market` |

**`GET /station/128016384-daedalus/market`** — Returns live commodity prices for a station, read from Redis where EDDN data is stored as it arrives. Commodity internal names are mapped to human-readable display names.

```json
{
  "station": "Daedalus",
  "system": "Sol",
  "last_updated": "2024-01-15T14:30:00Z",
  "prohibited": ["Narcotics", "Slaves"],
  "commodities": {
    "gold": {
      "name": "Gold",
      "buy_price": 0,
      "sell_price": 47238,
      "mean_price": 47201,
      "demand": 14320,
      "stock": 0
    },
    "biowaste": {
      "name": "Biowaste",
      "buy_price": 92,
      "sell_price": 0,
      "mean_price": 181,
      "demand": 0,
      "stock": 8500
    }
  }
}
```

---

## Slug Format Reference

Slugs are used as URL-safe identifiers throughout the API, combining the numeric `id64` with the human-readable name:

| Resource | Slug format | Example |
|----------|-------------|---------|
| System | `{id64}-{name}` | `10477373803-sol` |
| Body | `{body_id}-{name}` | `108086401534265707-earth` |
| Station | `{market_id}-{name}` | `128016384-daedalus` |

---

## Notes

- **Caching** is used heavily — systems, pages, and search results are cached for 1 hour; distance searches for 24 hours. The `resetCache=1` param on `/statistics` is the only public cache-bust mechanism.
- **EDSM fallback** — when a system or its relations (bodies, information, stations) aren't in the local database, the API transparently fetches them from EDSM and stores them, so the first request for an obscure system may be slower.
- **Market data** is ephemeral and lives in Redis, not the relational database — it reflects whatever EDDN last broadcast for that station.
