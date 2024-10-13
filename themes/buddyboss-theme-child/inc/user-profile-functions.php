<?php 
// Fetch all sector names for the select dropdown.
function get_all_sector_names()
{
    global $wpdb;
    $query = "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'sector_email_%'";
    $results = $wpdb->get_results($query);

    $sectors = [];
    foreach ($results as $row) {
        $sector_name = str_replace('sector_email_', '', $row->option_name);
        $sector_name = str_replace('_', ' ', $sector_name);
        $sector_name = ucwords($sector_name);
        $sectors[] = $sector_name;
    }
    return $sectors;
}

// Add custom sector field to user profiles
function add_sector_user_profile_field($user)
{
    $sectors = get_all_sector_names();
    $user_sector = get_the_author_meta('sector', $user->ID);
?>
    <h3>Sector Information</h3>
    <table class="form-table">
        <tr>
            <th><label for="sector">Sector</label></th>
            <td>
                <select name="sector" id="sector" class="regular-text">
                    <option value="">Select Sector</option>
                    <?php foreach ($sectors as $sector) : ?>
                        <option value="<?php echo esc_attr($sector); ?>" <?php selected($user_sector, $sector); ?>>
                            <?php echo esc_html($sector); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">Please select your sector.</p>
            </td>
        </tr>
    </table>
<?php
}
add_action('show_user_profile', 'add_sector_user_profile_field');
add_action('edit_user_profile', 'add_sector_user_profile_field');

// Save custom sector field in user profiles
function save_sector_user_profile_field($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    // Save the selected sector from the dropdown
    if (isset($_POST['sector'])) {
        update_user_meta($user_id, 'sector', sanitize_text_field($_POST['sector']));
    }
}
add_action('personal_options_update', 'save_sector_user_profile_field');
add_action('edit_user_profile_update', 'save_sector_user_profile_field');
?>