# External Services

Several pages fetch live data from external services through Laravel's HTTP client. Each controller transforms provider payloads into simple arrays for Blade views and caches successful responses.

## Shared Pattern

Most live-data controllers follow this pattern:

1. Check Laravel cache for an existing response.
2. Fetch from an external API if the cache is missing.
3. Transform the provider payload into view-friendly arrays.
4. Cache only successful responses.
5. Return an `error` flag so views can handle failures.

The controllers catch thrown errors and convert them into `error = true`, so external API failures should not crash the whole page.

## Weather

Controller: `WeatherController`

Route:

```text
GET /weather
```

Source:

```text
https://api.open-meteo.com/v1/forecast
```

Cache key:

```text
weather.georgia
```

Cache duration:

```text
20 minutes
```

The controller requests current and five-day forecast data for several Georgian cities. Kutaisi is configured as the spotlight city.

## Air Quality

Controller: `AirQualityController`

Route:

```text
GET /air
```

Source:

```text
https://air-quality-api.open-meteo.com/v1/air-quality
```

Cache key:

```text
air.georgia
```

Cache duration:

```text
20 minutes
```

The controller requests AQI and pollutant values for the same Georgian city list used by weather. Kutaisi is configured as the spotlight city.

## Crypto

Controller: `CryptoController`

Route:

```text
GET /crypto
```

Source:

```text
https://api.coingecko.com/api/v3/coins/markets
```

Cache key:

```text
crypto.markets
```

Cache duration:

```text
5 minutes
```

The controller requests selected coins in USD, ordered by market capitalization. The first returned coin is used as the spotlight item.

## Currency

Controller: `CurrencyController`

Route:

```text
GET /currency
```

Source:

```text
https://nbg.gov.ge/gw/api/ct/monetarypolicy/currencies/en/json/
```

Cache key:

```text
currency.gel
```

Cache duration:

```text
30 minutes
```

The controller reads National Bank of Georgia exchange-rate data and transforms configured currencies into GEL rates. USD is used as the spotlight currency.

## SPAR Products

Controller: `SparController`

Route:

```text
GET /spar
```

Source:

```text
https://tarifebi.ge/api/products.php
```

Cache keys:

```text
spar:browse:{category}:{page}
spar:catalog:{category}
```

Cache durations:

```text
browse pages: 20 minutes
catalog search data: 30 minutes
```

The SPAR page supports category browsing, search, pagination, and sale-price display. Search uses a cached catalog because the external API is paginated.

## Homepage Ticker

`NewsController@index` calls the `data()` methods on these controllers:

- `WeatherController`
- `CryptoController`
- `CurrencyController`
- `AirQualityController`

This gives the homepage ticker compact live-data highlights without duplicating provider logic.

## Operational Notes

- The app depends on outbound network access for live-data pages.
- Failed external responses are not cached.
- Stale successful cache entries are reused until their configured duration expires.
- If a live provider changes its response shape, update the matching controller transform method.

