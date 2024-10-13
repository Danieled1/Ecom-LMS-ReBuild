<?php
// admin-ticket-management.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check if the user has the 'manage_options' capability
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

$current_user = wp_get_current_user();
$user_sector = get_user_meta($current_user->ID, 'sector', true); // Retrieves the user's sector from the profile field

// Fetch all ticket posts
$args = array(
    'post_type' => 'ticket',
    'posts_per_page' => -1,
    'meta_query' => $user_sector ? array(
        array(
            'key' => 'ticket_sector',
            'value' => $user_sector,
            'compare' => '=' // Use '=' for exact match; 'LIKE' could be used for partial matches
        ),
    ) : null, // If no sector is defined, no meta query is needed
);

$tickets = new WP_Query($args);

function render_sector_emails()
{
    if (current_user_can('manage_options')) : ?>
        <button id="manage-sector-emails-btn" class="button" onclick="openEmailSettingsModal('<?php the_ID(); ?>')">Manage Sector Emails</button>
    <?php endif;
    
}

function render_search_form()
{
    return '
    <form method="get" class="search-box">
        <label for="user-search-input">Search Users:</label>
        <input type="search" id="user-search-input" placeholder="Search tickets...">
        <small>Enter at least 3 characters to search</small>
    </form>';
}

function render_table_body($tickets)
{
    ob_start();
?>
    <tbody id="the-list">
        <?php if ($tickets->have_posts()) : ?>
            <?php while ($tickets->have_posts()) : $tickets->the_post(); ?>
                <?php echo render_ticket_row(); ?>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="3">No tickets found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
<?php
    return ob_get_clean();
}


function render_ticket_row()
{
    $author_id = get_post_field('post_author', get_the_ID());
    $author = get_userdata($author_id);
    $created_on = get_the_date('Y-m-d H:i:s', get_the_ID());
    $modified_on = get_the_modified_date('Y-m-d H:i:s', get_the_ID());
    $sector_feedback = get_field('sector_feedback');
    $ticket_status = get_field('ticket_status', get_the_ID());
    $ticket_content = get_field('ticket_content');

    $ticket_content_parts = parse_ticket_content($ticket_content);


    ob_start();
?>
    <tr id="ticket-<?php the_ID(); ?>">
        <?php
        echo render_ticket_cell($author->display_name, 'user_display_name', 'Display Name');
        echo render_ticket_cell($author->user_login, 'created_by', 'Created By');
        echo render_ticket_cell(get_field('ticket_sector')['label'], 'sector column-sector', 'Sector');
        echo render_ticket_cell(get_field('ticket_sector_subject')['label'], 'sector-subject column-sector', 'Sector Subject');
        echo render_ticket_cell(get_field('ticket_title'), 'title column-title', 'Title', true);
        echo render_status_cell($ticket_status);
        echo render_ticket_cell($created_on, 'creation_time', 'Created On');
        echo render_ticket_cell($modified_on, 'modified_time', 'Last Modified');
        echo render_content_cell();
        echo render_feedback_cell();
        echo render_actions_cell();
        ?>
    </tr>
<?php
    echo render_modal($ticket_content_parts);
    return ob_get_clean();
}

function parse_ticket_content($ticket_content)
{
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($ticket_content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // Use DOMXPath to query the document
    $xpath = new DOMXPath($dom);

    // Extract paragraphs for Message Content
    $paragraphs = $xpath->query('//p'); // Selects <p> but not those directly containing <img> or <a>

    // Extract images for Screenshots
    $images = $xpath->query('//img');

    // Extract links for Attachments
    $links = $xpath->query('//a[@href]'); // Selects <a> tags with an href attribute

    return compact('paragraphs', 'images', 'links', 'dom');
}

function render_ticket_cell($content, $class, $colname, $isStrong = false)
{
    ob_start();
?>
    <td class="<?php echo $class; ?>" data-colname="<?php echo $colname; ?>">
        <?php echo $isStrong ? '<strong>' . esc_html($content) . '</strong>' : esc_html($content); ?>
    </td>
<?php
    return ob_get_clean();
}

function render_status_cell($ticket_status)
{
    ob_start();
?>
    <td class="status column-status" data-colname="Status">
        <div class="status-view" data-ticket-id="<?php the_ID(); ?>"><?php echo esc_html($ticket_status['label']); ?></div>
        <select class="status-select hidden" data-ticket-id="<?php the_ID(); ?>">
            <option value="review" <?php selected(get_field('ticket_status')['value'], 'review'); ?>>Waiting For Review</option>
            <option value="documents" <?php selected(get_field('ticket_status')['value'], 'documents'); ?>>Missing Documents</option>
            <option value="done" <?php selected(get_field('ticket_status')['value'], 'done'); ?>>Done</option>
        </select>
    </td>
<?php
    return ob_get_clean();
}

function render_content_cell()
{
    ob_start();
?>
    <td class="content column-content" data-colname="Content">
        <button class="button view-content" onclick="openModal('<?php the_ID(); ?>')">תוכן הודעה</button>
    </td>
<?php
    return ob_get_clean();
}

function render_feedback_cell()
{
    $feedback = get_post_meta(get_the_ID(), 'sector_feedback', true);
    ob_start();
?>
    <td class="sector_feedback column-sector_feedback" data-colname="Sector's Feedback">
        <div id="feedbackDisplay-<?php echo get_the_ID(); ?>" class="feedback-display">
            <?php echo $feedback ? esc_html($feedback) : 'No Feedback Provided'; ?>
        </div>
        <div id="feedbackEdit-<?php echo get_the_ID(); ?>" class="feedback-edit hidden">
            <input type="text" id="feedback-<?php echo get_the_ID(); ?>" value="<?php echo esc_attr($feedback); ?>">
        </div>
    </td>
<?php
    return ob_get_clean();
}

function render_actions_cell()
{
    ob_start();
?>
    <td class="actions column-actions" data-colname="Actions">
        <button class="button edit-status" data-ticket-id="<?php the_ID(); ?>">עריכה</button>
        <button class="button save-status hidden" data-ticket-id="<?php the_ID(); ?>">שמור</button>
    </td>
<?php
    return ob_get_clean();
}

function render_modal($content_parts)
{
    extract($content_parts);
    ob_start();
?>
    <div class="modal" id="contentModal-<?php the_ID(); ?>">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeModal('<?php the_ID(); ?>')">&times;</span>
                <h2><?php echo esc_html(get_field('ticket_title')); ?></h2>
            </div>
            <div class="modal-body">
                <?php if ($paragraphs) : ?>
                    <div class="modal-section">
                        <div class="modal-section-header">Message Content:</div>
                        <?php
                        foreach ($paragraphs as $paragraph) {
                            foreach ($paragraph->childNodes as $childNode) {
                                if ($childNode->nodeType === XML_TEXT_NODE) {
                                    echo '<p>' . htmlspecialchars($childNode->nodeValue) . '</p>';
                                }
                            }
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($images) : ?>
                    <div class="modal-section">
                        <div class="modal-section-header">Screenshots:</div>
                        <?php
                        foreach ($images as $image) {
                            echo $dom->saveHTML($image);
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($links) : ?>
                    <div class="modal-section">
                        <div class="modal-section-header">Attachments:</div>
                        <?php
                        foreach ($links as $link) {
                            $link->setAttribute('target', '_blank');
                            echo $dom->saveHTML($link);
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function render_page_content($tickets)
{
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Ticket Management</h1>
        <?php render_sector_emails(); ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" id="user_display_name" class="manage-column">Name</th>
                    <th scope="col" id="created_by" class="manage-column">User Email</th>
                    <th scope="col" id="ticket_sector" class="manage-column column-sector">Sector</th>
                    <th scope="col" id="ticket_sector-subject" class="manage-column column-sector-subject">Sector Subject</th>
                    <th scope="col" id="ticket_title" class="manage-column column-title column-primary">Title</th>
                    <th scope="col" id="ticket_status" class="manage-column column-status">Status</th>
                    <th scope="col" id="creation_time" class="manage-column">Created On</th>
                    <th scope="col" id="modified_time" class="manage-column">Last Modified</th>
                    <th scope="col" id="ticket_content" class="manage-column column-content">Content</th>
                    <th scope="col" id="sector_feedback" class="manage-column">Sector's Feedback</th>
                    <th scope="col" id="actions" class="manage-column column-actions">Actions</th>
                </tr>
            </thead>
            <?php echo render_table_body($tickets); ?>
        </table>
        <div id="sector-emails-modal" class="modal" style="display:none;">
            <div class="modal-content">
                <h2>Sector-Specific Email Settings</h2>
                <span class="close" onclick="closeEmailSettingsModal('<?php the_ID(); ?>')">&times;</span>
                <form id="sectorEmailsForm">
                    <!-- Dynamic content will be inserted here via JavaScript -->
                </form>
            </div>
        </div>
    </div>
<?php
    print_r('New Php Page');
}

render_page_content($tickets);
wp_reset_postdata();
?>