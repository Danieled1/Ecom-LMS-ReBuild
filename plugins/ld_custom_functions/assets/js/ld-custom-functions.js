console.log("Script initialized");

document.addEventListener("DOMContentLoaded", function () {
    const searchButton = document.querySelector('#search_button');
    const searchInput = document.querySelector('#search_input');
    const backButton = document.getElementById('backButton');

    let lastSearchQuery = ''; // This will hold the last search query globally

    // Handling search on button click
    searchButton.addEventListener('click', function (e) {
        e.preventDefault(); // Prevent the default form submission
        const query = searchInput.value.trim();
        if (query) {
            lastSearchQuery = query; // Store the last search query
            fetchCourses(query); // Fetch courses based on the query
            backButton.style.display = 'block'; // Show the back button when search is performed

        }
    });
    if (lastSearchQuery) {
        backButton.style.display = 'block'; // Ensure back button is visible when there's a last search
    }
    if (backButton) {
        backButton.addEventListener('click', function (e) {
            e.preventDefault();
            if (lastSearchQuery) {
                fetchCourses(lastSearchQuery); // Re-fetch courses using the last stored query
            } else {
                listRecentCourses(); // If no search was made, fetch recent courses
            }
        });
    }
});

function fetchCourses(query) {
    fetch(adminAjax.ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=relist_courses&course_name=${encodeURIComponent(query)}`
    })
        .then(response => response.text()) // Expect HTML response from server
        .then(html => {
            document.getElementById('results').innerHTML = html;
            document.getElementById('backButton').style.display = 'block';
        })
        .catch(error => console.error('Error fetching courses:', error));
}

function showLessons(course_id) {
    console.log("Fetching lessons for course ID:", course_id);
    fetch(adminAjax.ajaxUrl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=fetch_ld_course_lessons&course_id=${course_id}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                document.getElementById("results").innerHTML = data.html;
                document.getElementById('backButton').style.display = 'block';
                
            }
        })
        .catch(error => console.error("Error fetching lessons:", error));
}
function cleanLessons(course_id) {
    if (confirm('Are you sure you want to delete all lessons for this course? This cannot be undone!')) {
        console.log("Confirmed, Cleaning lessons of course_id : ", course_id);

        fetch(adminAjax.ajaxUrl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=delete_lessons&course_id=${course_id}`
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message); // Display the result message to the user

                if (data.success) {
                    // Optionally refresh the part of the page that shows courses
                }
            })
            .catch(error => console.error("Error deleting lessons:", error));
    }
}
function listRecentCourses() {
    fetch(adminAjax.ajaxUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=list_recent_courses`
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('results').innerHTML = html;
        // Optionally show the back button if you want to provide further navigation
        document.getElementById('backButton').style.display = 'none'; // or adjust as needed
    })
    .catch(error => console.error('Error listing recent courses:', error));
}