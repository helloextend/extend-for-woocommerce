# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WooCommerce plugin that integrates Extend's product and shipping protection services. Merchants install the plugin to offer Extend protection plans on their WooCommerce stores. The plugin handles offer rendering, cart management, order creation via the Extend API, and post-purchase flows.

## Commands

### JavaScript

```bash
pnpm install                    # Install Node dependencies
npx eslint .                    # Lint JavaScript (WordPress + JSDoc rules)
npx prettier --check .          # Check formatting
npx prettier --write .          # Fix formatting
```

### PHP

```bash
composer install                # Install PHP dependencies
vendor/bin/phpcs                # Lint PHP (uses phpcs.xml config — WordPress standards + PHP 7.4 compat)
vendor/bin/phpcbf               # Auto-fix PHP standards violations
```

### E2E Tests (Cypress)

Tests run against a live WooCommerce instance at `https://woocommerce.woodys.extend.com`. Required env vars: `WP_ADMIN_USERNAME`, `WP_ADMIN_PASSWORD`, `STORE_ID`, `CLIENT_ID`, `CLIENT_SECRET`.

```bash
npx cypress open                # Interactive mode
npx cypress run                 # Headless
npx cypress run --spec "cypress/integration/01_plugin_settings.cy.js"  # Single suite
```

Test suites run in order: `01_plugin_settings` → `02_frontend_checks` → `03_order_processing_checks`.

CI runs these automatically on PRs to master via `.github/workflows/pr-tests.yml`, which zips the plugin, deploys it to the test store, then runs all three suites.

## Architecture

The plugin entry point is `helloextend-protection/helloextend-protection.php`. It registers activation/deactivation hooks and bootstraps the main class.

### Class Hierarchy

- **`class-helloextend-protection.php`** — Core orchestrator. Instantiates the loader and all feature classes, then wires them together via WordPress hooks.
- **`class-helloextend-protection-loader.php`** — Collects action/filter registrations and runs them on `add_action`/`add_filter`.
- **`class-helloextend-protection-admin.php`** — Admin settings pages with three tabs (general, product protection, shipping protection).
- **`class-helloextend-protection-public.php`** — Enqueues public-facing scripts and styles.
- **`class-helloextend-protection-orders.php`** — Hooks into WooCommerce order lifecycle to create/cancel Extend contracts via the Extend API.
- **`class-helloextend-protection-shipping.php`** — Manages shipping protection fees via `woocommerce_cart_calculate_fees`.
- **`class-helloextend-protection-pdp-offer.php`** — Renders product protection offers on product detail pages.
- **`class-helloextend-protection-cart-offer.php`** — Renders protection offers in the cart.
- **`class-helloextend-protection-global.php`** — Global hooks shared across contexts.
- **`class-helloextend-protection-logger.php`** — Debug logging; logs are viewable in the WP admin logger dashboard (`helloextend_logger_admin.php`).

### Frontend JavaScript (`js/`)

Each JS file corresponds to a specific placement and is enqueued conditionally:

| File | Placement |
|------|-----------|
| `helloextend-pdp-offers.js` | Product detail page |
| `helloextend-cart-offers.js` | Cart page |
| `helloextend-shipping-offers.js` | Shipping protection in cart |
| `helloextend-minicart-offers.js` | Mini cart |
| `helloextend-post-purchase.js` | Post-purchase confirmation page |
| `helloextend-global.js` | Sitewide globals |

The JS files use the Extend SDK Client (external CDN script) to render offer widgets. The plugin passes `storeId`, `environment`, and product data from PHP to JS via `wp_localize_script`.

### Settings Storage

Plugin settings are stored in WordPress options. The three settings tabs (`tabs/`) render forms that save to `wp_options`. The general settings tab stores API credentials (Store ID, Client ID, Client Secret) and environment (sandbox vs. live).

### API Integration

All Extend API calls (contract creation, cancellation) are made server-side from `class-helloextend-protection-orders.php` using `wp_remote_post`/`wp_remote_get`. The sandbox vs. live environment toggle in settings switches the API base URL.

## Tech Stack

- **PHP 7.4+** (8.0+ recommended), **WordPress**, **WooCommerce 7.0+**
- **Vanilla JS / jQuery** — no build step for frontend JS; files are served directly
- **pnpm 10.30.3** — pinned, use pnpm not npm
- **Cypress 10** — E2E tests only, no unit tests
- **PHPCS** with WordPress Coding Standards + PHPCompatibility rules (configured in `phpcs.xml`)
- **ESLint** with `@wordpress/eslint-plugin` (configured in `.eslintrc.json`)
