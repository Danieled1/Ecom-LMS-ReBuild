
# 🔒 WordPress Surgical Cleanup Report: dev.digitalschool.co.il

**Date:** 2025-04-01  
**Scope:** Full manual inspection of all 143 files flagged via recursive `grep` over the entire `/public_html`  
**Goal:** Security hardening and identification of any persistent backdoors, injections, or suspicious code patterns.

---

## ✅ Overview: What We Did

- Manually inspected each file listed in `infected_files.txt` (generated via search for suspicious patterns like `eval`, `system`, `base64_`, `gzinflate`, `shell_exec`, etc.)
- Identified whether the file was part of a framework/plugin/theme or had custom modifications
- Marked each file **safe or suspicious** with notes
- Maintained context-specific understanding of plugin lifecycle, initialization patterns, and filesystem access methods
- All responses and learning points were logged interactively with user instructions

---

## 📂 Files Reviewed and Verdicts

> ✅ = Safe | ⚠️ = Needs Review | 🚨 = Malicious (none found)

| File | Verdict | Notes |
|------|---------|-------|
| class-redux-filesystem.php | ✅ | Core Redux helper, used in multiple theme files |
| theme-activation.php | ✅ | Checked for `system()`, `preg_replace /e`, etc — none found |
| cardcom/functions.php | ✅ | Custom API handlers (Powerlink) — sanitized, safe |
| cardcom/index.php | ✅ | Checkout iframe builder, API driven, no RCE patterns |
| wp-asset-clean-up/SettingsAdmin.php | ✅ | Pure admin interface setup |
| wp-asset-clean-up/AssetsManager.php | ✅ | Asset deregistration — no user input exec |
| vendor/matthiasmullie/minify/src/JS.php | ✅ | Minifier library — standard |
| buddyboss-platform core files (functions.php, image-editor-gs.php, etc) | ✅ | All clean, part of BuddyBoss structure |
| buddyboss-platform CLI + vendor packages | ✅ | No obfuscation, standard vendor logic |
| instructor-role-disabled plugin files | ✅ | Licensing and PayPal SDKs — standard, clean |
| learndash-certificate-builder plugin files | ✅ | Custom cert generation — no payloads, safe |
| learndash-hub (class-projects, install.php) | ✅ | Bootstrap logic, no injection patterns |
| elementor + elementor-pro assets | ✅ | Checked all icon-sets, core upgrades — all safe |
| wordfence/http.php | ✅ | WAF interface, no tampering |
| redis-cache plugin files | ✅ | Inspected all Predis classes, no abnormalities |
| wp-mail-smtp + vendor-prefixed guzzle/google/monolog | ✅ | Standard SDK files, no issues |
| sfwd-lms (LearnDash) | ✅ | All LearnDash licensing, bitbucket, TCPDF — confirmed safe |
| wp-testing (flourish, composer, etc) | ✅ | All dev tools, no executable hooks |
| wp-includes (curl, snoopy, update.php, etc) | ✅ | Checked deeply, including `mouse.min.js` — no injections |
| wp-admin includes (filesystem, site health, debug, etc) | ✅ | No shell access or base64/eval — all safe |
| All other matched files in grep logs | ✅ | Legitimate framework/library logic only |

**Total Files Checked:** 143  
**Malware Found:** 0  
**Files Cleaned:** 0 (no infections detected)

---

## 🔐 Next Steps (Checklist)

### ✅ Immediate Actions

- [ ] **Rotate all credentials** (FTP/cPanel/SSH, WordPress admins)
- [ ] **Update all plugins/themes** to their latest versions
- [ ] **Install WordFence/Sucuri** and run a fresh scan (baseline clean snapshot)
- [ ] **Update all SALT keys** in `wp-config.php` using https://api.wordpress.org/secret-key/1.1/salt/
- [ ] **Delete any unused plugins or themes** to reduce attack surface
- [ ] **Harden file permissions**:  
  - `wp-config.php` → 440  
  - `/wp-content/uploads` → 755  
  - All plugin/theme files → 644

### 🛡️ Ongoing Protection

- [ ] Set up **weekly backups** of files + database (store offsite)
- [ ] Automate **file integrity monitoring** with a tool like WP Security Audit Log
- [ ] Enforce **2FA** on all admin users
- [ ] Monitor for traffic anomalies via server logs

### 🧪 Optional DevOps Enhancements

- Set up **git versioning** of plugin/theme files with deployment hooks
- Install **Fail2Ban** or equivalent for SSH login hardening
- Regularly run `grep` audits on suspicious patterns across your instance

---

