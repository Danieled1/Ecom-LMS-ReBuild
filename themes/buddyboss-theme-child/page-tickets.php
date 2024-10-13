<?php
/*
Template Name: Tickets
*/
acf_form_head();
get_header();

$current_user = wp_get_current_user();
$current_user_id = get_current_user_id();

$args = array(
    'post_type' => 'ticket',
    'posts_per_page' => -1,
    'author' => $current_user_id, // Fetch tickets created by the current user
    'post_status' => 'any' // Fetch tickets with any status.

);
$user_tickets_query = new WP_Query($args);

?>
<div id="primary" class="content-area bb-grid-cell">
    <main id="main" class="site-main">
        <div class="container">
            <div class="content">

                <div class="header-image-wrapper">
                    <div class="header-image">
                        <img src="https://via.placeholder.com/1180x350" alt="Header Image" />
                        <div class="header-gradient-overlay"></div>
                        <div class="header-text">
                            <div class="header-title">פניות ואישורים</div>
                            <div class="header-subtitle">נקודות מפתח להגשת פנייה בצורה נכונה</div>
                        </div>
                    </div>
                </div>
                <div class="content-text">
                    <span>לפני שתמלאו את הפנייה, אנא קראו את הנקודות המפתח הבאות:</span>
                    <ul>
                        <li>פנייה שדורשת צירוף קבצים או תמונות ולא תכלול כאלה תאט את זמן המענה.</li>
                        <li>מלאו כל פרט בקפידה וודאו את נושא הפנייה והפרט שבו אתם זקוקים לעזרה.</li>
                        <li> עליכם להתמקד בנושא אחד בכל פנייה.</li>
                    </ul>
                </div>
                <div class="important-notes">הערות חשובות:</div>

                <div class="notes">
                    עוד מקום לפסקה של נקודות חשובות Lorem ipsum, dolor sit amet consectetur adipisicing elit. Aut
                    consequatur quia et laborum harum error, brdistinctio vel dicta labore repellendus.
                </div>

                <div class="actions-container">
                    <button style="background-color:#fff !important;  color: #6836FF !important;" id="createTicketBtn"
                        class="support-button button-text">בחירת גורם מטפל<div class="arrow-icon">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/arrow-right-solid.svg"
                                alt="Arrow Right" class="arrow-icon-img" />
                        </div>
                    </button>
                    <button style="background-color:#fff !important;  color: #6836FF !important;" id="ticketHistoryBtn"
                        class="support-button button-text">מעקב פניות<div class="arrow-icon">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/vectors/arrow-right-solid.svg"
                                alt="Arrow Right" class="arrow-icon-img" />
                        </div>
                    </button>
                    <div id="contactContent" class="ticket-content hidden">
                        <caption>בחירת גורם מטפל</caption>

                        <?php
                        $options = array(
                            'post_id' => 'new_post', // 'new_post' indicates a new post is to be created upon form submission
                            'new_post' => array(
                                'post_type' => 'ticket', // the post type to be created
                                'post_status' => 'draft', // set the initial post status to draft
                                'post_title' => ''
                            ),
                            'field_groups' => array('group_65f0608c7091b'), // the ID of your ACF field group
                            'return' => '%post_id%', // Return the ID of the new post on submission
                            'submit_value' => __('שליחת פנייה', 'text-domain'), // text for the submit button
                            'updated_message' => __("הפנייה נשלחה", 'text-domain'), // confirmation message
                            'uploader' => 'basic', // Use the basic uploader instead of the default WordPress media uploader
                            'form' => 'true',
                            'fields' => array(
                                'field_65f06082a4add',
                                'field_65f064c9a4ae1',
                                'field_65f060dba4ade',
                                'field_65f06191a4adf',
                            )
                        );

                        // Output the ACF form with your specified options
                        acf_form($options);
                        ?>
                    </div>
                    <div id="ticketHistory" class="ticket-history hidden">
                        <table class="wp-list-table striped">
                            <caption class="">מעקב פניות</caption>
                            <thead>
                                <tr>
                                    <th scope="col" class="manage-column">נוצר בתאריך</th>
                                    <th scope="col" id="ticket_sector" class="manage-column column-sector">מחלקה</th>
                                    <th scope="col" id="ticket_sector-subject" class="manage-column column-sector-subject">תת נושא</th>
                                    <th scope="col" class="manage-column">כותרת</th>
                                    <th scope="col" id="ticket_content" class="manage-column column-content">תוכן</th>
                                    <th scope="col" class="manage-column">סטטוס</th>
                                    <th scope="col" class="manage-column">משוב</th>
                                    <th scope="col" id="modified_time" class="manage-column">תאריך שינוי אחרון</th>

                                    <!-- Add other headers as needed -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($user_tickets_query->have_posts()): ?>
                                    <?php while ($user_tickets_query->have_posts()):
                                        $user_tickets_query->the_post();
                                        $ticket_content = get_field('ticket_content');
                                        $created_on = get_the_date('Y-m-d H:i:s', get_the_ID());
                                        $modified_on = get_the_modified_date('Y-m-d H:i:s', get_the_ID());

                                        // Create a new DOMDocument and load the HTML content
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
                                
                                        ?>
                                        <tr>
                                            <td><?php the_time('F j, Y'); ?></td>
                                            <td class="sector column-sector" data-colname="מחלקה">
                                                <?php echo esc_html(get_field('ticket_sector')['label']); ?>
                                            </td>
                                            <td class="sector column-sector" data-colname="תת נושא">
                                                <?php echo esc_html(get_field('ticket_sector_subject')['label']); ?>
                                            </td>
                                            <td><?php echo esc_html(get_field('ticket_title')); ?></td>
                                            <td class="content column-content" data-colname="תוכן">
                                                <button class="button view-content"
                                                    onclick="openModal('<?php the_ID(); ?>')">תוכן
                                                    הודעה</button>
                                            </td>
                                            <td><?php
                                            $ticket_status = get_field('ticket_status');
                                            echo esc_html($ticket_status ? $ticket_status['label'] : 'טרם נצפה'); ?></td>
                                            <td><?php echo esc_html(get_field('sector_feedback')); ?></td>
                                            <td class="modified_time" data-colname="תאריך שינוי אחרון">
                                                <?php echo esc_html($modified_on); ?>
                                            </td>

                                            <!-- Output other columns as necessary -->
                                        </tr>
                                        <div class="modal hidden" id="contentModal-<?php the_ID(); ?>">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <span class="close"
                                                        onclick="closeModal('<?php the_ID(); ?>')">&times;</span>
                                                    <h2><?php echo esc_html(get_field('ticket_title')); ?></h2>
                                                </div>
                                                <div class="modal-body">
                                                    <?php if ($paragraphs): ?>
                                                        <div class="modal-section">
                                                            <div class="modal-section-header">תוכן ההודעה:</div>
                                                            <?php
                                                            // Display paragraphs
                                                            foreach ($paragraphs as $paragraph) {
                                                                foreach ($paragraph->childNodes as $childNode) {
                                                                    // Only output text nodes, skipping <a> tag nodes
                                                                    if ($childNode->nodeType === XML_TEXT_NODE) {
                                                                        echo '<p>' . htmlspecialchars($childNode->nodeValue) . '</p>';
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($images): ?>
                                                        <div class="modal-section">
                                                            <div class="modal-section-header">צילומי מסך:</div>
                                                            <?php
                                                            // Display images
                                                            foreach ($images as $image) {
                                                                echo $dom->saveHTML($image);
                                                            }
                                                            ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($links): ?>
                                                        <div class="modal-section">
                                                            <div class="modal-section-header">קבצים מצורפים:</div>
                                                            <?php
                                                            // Display links
                                                            foreach ($links as $link) {
                                                                $link->setAttribute('target', '_blank');
                                                                echo $dom->saveHTML($link);
                                                            }
                                                            ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <!-- Optional footer content -->
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">לא נמצאו פניות.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>
        </div>
        <!-- Header Section with User Info and General Instructions -->

    </main><!-- #main -->
</div>
<?php wp_reset_postdata(); // Reset the query to the main loop 
?>
<?php
get_footer();
?>