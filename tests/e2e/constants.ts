import path from "node:path";
import process from "node:process";

export const BASE_URL = process.env.WP_BASE_URL ?? "http://localhost:8000";

/**
 * Fixed test credentials matching the idempotent `wp core install` run by
 * global-setup.ts, both locally and in CI — there's no real user data here.
 */
export const ADMIN_USER = process.env.WP_ADMIN_USER ?? "admin";
export const ADMIN_PASSWORD = process.env.WP_ADMIN_PASSWORD ?? "password";

export const WP_CONTAINER_NAME = process.env.WP_CONTAINER_NAME ?? "wordpress";

/** Storage state produced by the `setup` project's login, reused by specs. */
export const STORAGE_STATE = path.join(__dirname, ".auth", "admin.json");
