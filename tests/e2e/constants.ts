import process from "node:process";

export const BASE_URL = process.env.WP_BASE_URL ?? "http://localhost:8000";

/**
 * Fixed test credentials matching the idempotent `wp core install` run by
 * global-setup.ts, both locally and in CI — there's no real user data here.
 * Only used to bootstrap the WordPress admin account itself; no spec logs in.
 */
export const ADMIN_USER = process.env.WP_ADMIN_USER ?? "admin";
export const ADMIN_PASSWORD = process.env.WP_ADMIN_PASSWORD ?? "password";

export const WP_CONTAINER_NAME = process.env.WP_CONTAINER_NAME ?? "wordpress";
