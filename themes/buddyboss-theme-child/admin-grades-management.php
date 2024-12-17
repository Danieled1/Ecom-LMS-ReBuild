<?php
// admin-grades-management.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check if the user has the 'manage_options' capability
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// $users = get_users();
$current_user = wp_get_current_user();
error_log('Memory used: ' . memory_get_usage());
error_log('Memory peak: ' . memory_get_peak_usage());

function render_page_content()
{
    echo '<div class="wrap">
        <h1 class="wp-heading-inline">Grades Management Dashboard</h1>
        ' . render_search_form() . '
        <button id="updateGradesButton" class="button action">Update Grades for Class</button>
        ' . render_grades_modal() . '
        ' . render_upload_modal() . '
        ' . render_tables() . '
    </div>';
}
function render_upload_section()
{
    return '<div id="uploadGradesSection">
        <h2>Upload Grades File</h2>
        <form id="uploadGradesForm" method="post" enctype="multipart/form-data">
            <input type="file" id="gradesFileInput" name="gradesFile" accept=".xlsx, .xls">
            <button type="submit">Upload File</button>
        </form>
    </div>';
}

function render_search_form()
{
    // This form now only serves to accept input for AJAX-based search
    return '<form method="get" class="search-box">
        <select id="search-type">
            <option value="courses">Courses</option>
            <option value="students">Students</option>
        </select>
        <input type="search" id="user-search-input" placeholder="Search...">
    </form>';
}

function render_grades_modal()
{
    // Modal setup for displaying grades
    return '<div id="gradesModal" class="grades-modal" style="display:none;">
        <div class="grades-modal-content">
            <div id="spinner" class="spinner"></div>
            <span class="close">&times;</span>
            <table id="gradesTable" class="grades-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Deadline</th>
                        <th>Feedback</th>
                        <th>Last Modified</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody><!-- Grades will be dynamically populated here --></tbody>
            </table>
        </div>
    </div>';
}

function render_upload_modal()
{
    // Modal setup for file upload
    return '<div id="uploadGradesModal" class="upload-grades-modal" style="display:none;">
        <div class="upload-modal-content">
            <span class="close-upload">&times;</span>
            <h2>Upload Grades for Class</h2>
            <form id="uploadGradesForm" method="post" enctype="multipart/form-data">
                <input type="file" id="gradesFileInput" name="gradesFile" accept=".xlsx, .xls">
                <div id="sheetSelectContainer" style="margin-top: 10px;">
                    <label for="sheetSelect">Choose a Sheet:</label>
                    <select id="sheetSelect">
                        <!-- Sheet names will be populated here after file is uploaded -->
                    </select>
                </div>
                <button type="button" onclick="handleUploadGradesForm(event)">Preview Sheet Before Save</button>
            </form>
            <div id="previewArea" class="preview-area">
                <h3>Preview Grades Update</h3>
                    <p id="updateInfo">Updating grades for X students (Y graded out of Z total)</p>
                    <table id="gradesPreviewTable" class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Student Email</th>
                                <th>Current Grade</th>
                                <th>New Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Preview rows will be dynamically populated here -->
                        </tbody>
                    </table>
            </div>
            <button type="button" onclick="saveGrades()">Save Grades</button>
            <div id="alertBox"></div>
        </div>
    </div>';
}

function render_tables()
{
    return '<div id="coursesTableContainer">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" style="text-align:center;">Enter search terms above to display courses.</td>
                </tr>
            </tbody>
        </table>
        </div>
        <div id="usersTableContainer">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Group</th>
                    <th>Student</th>
                    <th>Email</th>
                    <th>Path</th>
                    <th>Grades</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" style="text-align:center;">Enter search terms above to display users.</td>
                </tr>
            </tbody>
        </table>
        </div>';
}
render_page_content();
