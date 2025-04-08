# Migration Process: dev.digitalschool.co.il ➡ app.digitalschool.co.il

## Overview
- Full site migration for production separation.
- Moved DB + Files + Licenses.
- Separate environments on WHM/cPanel.

## Steps
1. Database cloned from dev to app.
2. Updated `siteurl` + `home` in DB.
3. NGINX + Apache configurations reviewed.
4. Redis + Page cache flushed.
5. License transitions started.
6. SSL confirmed on app domain.
7. NGINX rewrite rules flushed.
8. Object-cache.php validated.

## Issues Found
- `upgrade.php` loop (solved by db_version check + permalinks flush).
- Session tokens cleared for admin accounts.
- Redis cache not initially cleared.

## Fixes Applied
- Flushed NGINX cache, Redis, and browser cache.
- Confirmed DB version and site URLs.
- PHP-FPM pools validated and restarted.
