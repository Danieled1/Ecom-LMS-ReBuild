# Plugin Usage in Custom Code

Below is a comprehensive list of the plugins used in your custom code, along with the corresponding functionalities and hooks. This will help you identify what to look for during plugin updates.

---

## 1. Advanced Custom Fields (ACF)
- **Functions**:
  - `acf_form_head`
  - `acf_form`
  - `get_field`
- **Hooks**:
  - `acf/save_post`

**Usage in Custom Code**:
- **Backend**:
  - No specific usage detected directly in backend admin pages, but hooks might be utilized indirectly.
- **Client Pages**:
  - Templates: `Grades1`, `Job Placement`, `Technical Support Guide`, `Tickets`
  - Form rendering and handling via `acf_form` and `acf_form_head`
  - Retrieving custom fields using `get_field`

---

## 2. BuddyPress
- **Functions**:
  - `bp_core_new_nav_item`
  - `bp_core_load_template`
  - `bp_displayed_user_id`
  - `bp_is_user`
  - `bp_is_current_action`
- **Hooks**:
  - `bp_setup_nav`
  - `bp_get_options_nav_classes`

**Usage in Custom Code**:
- **Custom Code in functions.php**:
  - Creating and displaying custom profile tabs (e.g., Certificates)
  - Customizing profile navigation classes

---

## 3. LearnDash
- **Functions**:
  - `learndash_user_get_enrolled_courses`
  - `learndash_user_get_course_completed_date`
  - `learndash_get_course_certificate_link`
  - `learndash_get_users_group_ids`
  - `learndash_course_progress`
  - `learndash_get_course_steps`

**Usage in Custom Code**:
- **Custom Code in functions.php**:
  - Fetching and displaying user certificates
  - Managing course and group enrollments
  - Displaying related courses and progress in the Job Placement page template
  - Handling user course completion status and certificate links

---

## 4. WordPress Core Functions
- **Functions**:
  - `get_users`
  - `wp_get_current_user`
  - `current_user_can`
  - `wp_die`
  - `WP_Query`
  - `get_userdata`
  - `get_user_meta`
  - `get_header`
  - `get_footer`
  - `update_option`
  - `get_option`
  - `get_avatar`
  - `get_the_modified_date`
  - `get_current_user_id`
  - `wp_update_post`
  - `wp_send_json_success`
  - `wp_send_json_error`
  - `sanitize_email`
  - `sanitize_text_field`
  - `sanitize_textarea_field`

**Usage in Custom Code**:
- **Backend**:
  - User management and data fetching in admin pages (grades, resume, ticket management)
  - Handling user permissions and rendering content conditionally
- **Client Pages**:
  - Rendering user-specific content
  - Handling form submissions and displaying user data
  - Managing custom post types and taxonomies

---

# Complete Custom Code Plugins Interaction List

## Advanced Custom Fields (ACF)
- `acf_form_head`
- `acf_form`
- `get_field`
- `acf/save_post`

## BuddyPress
- `bp_core_new_nav_item`
- `bp_core_load_template`
- `bp_displayed_user_id`
- `bp_is_user`
- `bp_is_current_action`
- `bp_setup_nav`
- `bp_get_options_nav_classes`

## LearnDash
- `learndash_user_get_enrolled_courses`
- `learndash_user_get_course_completed_date`
- `learndash_get_course_certificate_link`
- `learndash_get_users_group_ids`
- `learndash_course_progress`
- `learndash_get_course_steps`

## WordPress Core
- `get_users`
- `wp_get_current_user`
- `current_user_can`
- `wp_die`
- `WP_Query`
- `get_userdata`
- `get_user_meta`
- `get_header`
- `get_footer`
- `update_option`
- `get_option`
- `get_avatar`
- `get_the_modified_date`
- `get_current_user_id`
- `wp_update_post`
- `wp_send_json_success`
- `wp_send_json_error`
- `sanitize_email`
- `sanitize_text_field`
- `sanitize_textarea_field`

This list will help you identify and verify functionality whenever you update any of these plugins. If you detect a problem post-update, focus your checks around these functions and hooks to diagnose and fix any issues.
