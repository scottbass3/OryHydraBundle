# Changelog

## 2.0.0

Requires `scottbass3/hydra-client-php-psr-18` 2.x (Ory Hydra v26.2.0).

### Breaking changes

- Hydra responses declared by the spec are returned as models instead of thrown as `ApiException`
  (client 2.0). For example a 410 on `getOAuth2LoginRequest()` returns an `OAuth2RedirectTo`.
- `host` is required.
- Symfony 6.0 to 6.3 are no longer supported (they lack `AbstractBundle`); Symfony 6.4, 7 and 8 are.
- The `scottbass3.ory_hydra` container parameter is removed: configuration values are injected into the services.
- The `scottbass3.ory_hydra_client` alias is replaced by `scottbass3_ory_hydra.client`. Autowiring
  `Scottbass3\OryHydraBundle\Client` still works.
- The `Client` constructor takes the API services; build it from the container rather than by hand.
- The `temp_folder_path` option is removed: Hydra has no file endpoint.
- Defaults: `user_agent` is `scottbass3/ory-hydra-bundle`, `debug_file` is `php://stderr` (was `php://output`,
  which wrote debug logs into HTTP responses).
- Redirects are no longer followed, so that the 302 responses declared by Hydra are returned.

### Added

- Each API (`OAuth2Api`, `OidcApi`, `JwkApi`, `MetadataApi`, `WellknownApi`) is an autowirable service.
- `http_client` option to use a scoped Symfony HTTP client, for sync and async requests (previously the async
  requests used whatever client php-http/discovery found).
- Documentation in `docs/index.md`, tests and CI.

### Upgrading from 1.x

1. Rename the configuration root key to `scottbass3_ory_hydra` if you followed the 1.x README (`ory_hydra_api` never
   worked) and remove `temp_folder_path`.
2. Replace `try { ... } catch (ApiException $e)` around Hydra errors with `instanceof` checks on the returned model.
3. Replace uses of the `scottbass3.ory_hydra_client` service id with autowiring or `scottbass3_ory_hydra.client`.

## 1.0.0

Initial release.
