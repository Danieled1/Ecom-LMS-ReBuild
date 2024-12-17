// Initialization on document load
document.addEventListener("DOMContentLoaded", initializeApp);
function initializeApp() {
  attachEventListeners();
}

let debounceTimeout = null;

// Generic modal setup with an optional trigger element
function setupModal(modalSelector, closeSelector, triggerSelector) {
  const modal = getElement(modalSelector, `${modalSelector} not found`);
  const closeBtn = getElement(closeSelector, `${closeSelector} not found`);

  if (modal && closeBtn) {
    closeBtn.onclick = () => (modal.style.display = "none");
    window.onclick = (event) => {
      if (event.target === modal) modal.style.display = "none";
    };

    if (triggerSelector) {
      const trigger = getElement(triggerSelector, `${triggerSelector} not found`);
      if (trigger) {
        trigger.onclick = () => (modal.style.display = "block");
      }
    }
  }
}

function attachEventListeners() {
  const usersTableContainer = getElement("#usersTableContainer", "Users table container not found");
  const coursesTableContainer = getElement(
    "#coursesTableContainer",
    "Courses table container not found"
  );
  const uploadGradesForm = getElement("#uploadGradesForm", "Grades form not found");
  const gradesTable = getElement("#gradesTable", "Grades table not found");
  const gradesFileInput = getElement("#gradesFileInput", "Grades File Input not found");

  setupModal("#gradesModal", ".close", null);
  setupModal("#uploadGradesModal", ".close-upload", "#updateGradesButton");

  const elements = getElements(["#user-search-input", "#search-type"]);
  elements["#user-search-input"].addEventListener("input", () => triggerSearch(elements));
  elements["#search-type"].addEventListener("change", () => triggerSearch(elements));

  if (usersTableContainer && coursesTableContainer && gradesTable && uploadGradesForm) {
    usersTableContainer.addEventListener("click", handleUserActions);
    coursesTableContainer.addEventListener("click", handleCourseActions);
    gradesTable.addEventListener("click", handleGradeActions);
    gradesFileInput.addEventListener("change", handleFileSelect);
  }
}

function handleUploadGradesForm(event) {
  event.preventDefault();
  const fileInput = getElement("#gradesFileInput", "File input not found");
  const sheetSelect = document.getElementById("sheetSelect");
  const selectedSheet = sheetSelect.value;
  if (!selectedSheet) {
    console.error("No sheet selected.");
    return;
  }
  console.log(selectedSheet);
  const file = fileInput.files[0];
  console.log("File", file);
  if (file) {
    console.log("Uploaded file:", file.name); // Log the file name to ensure it's captured

    const reader = new FileReader();
    reader.onload = async (e) => {
      const arrayBuffer = e.target.result;
      const data = new Uint8Array(arrayBuffer);
      const workbook = XLSX.read(data, { type: "array" }); // Read the workbook using SheetJS
      const worksheet = workbook.Sheets[selectedSheet];
      const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
      const headers = jsonData[0];
      const emailIndex = headers.indexOf("email");
      const gradeIndex = headers.indexOf("grade");
      console.log("workbook:", workbook, "selectedSheet: ", selectedSheet, "worksheet:", worksheet);
      const usersToUpdate = jsonData
        .slice(1)
        .map((row) => ({
          // Skip header row
          student_email: row[emailIndex],
          new_grade: row[gradeIndex],
        }))
        .filter((user) => user.student_email && user.new_grade !== undefined); // Ensure both email and grade are defined

      // Log the structured data or use it to update the UI
      console.log("Preparing to fetch user details with:", usersToUpdate);

      const userDetails = await fetchUserDetails(
        usersToUpdate.map((user) => user.student_email),
        selectedSheet
      );
      console.log("User details fetched:", userDetails);

      const combinedDetails = userDetails.map((user) => {
        const userUpdate = usersToUpdate.find((update) => update.student_email === user.email);
        return {
          ...user,
          new_grade: userUpdate ? userUpdate.new_grade : "N/A", // Add a fallback if not found
        };
      });
      console.log("Combined user details:", combinedDetails);

      populateGradePreviewTable(combinedDetails);
    };
    reader.readAsArrayBuffer(file); // Read the file data as an ArrayBuffer for SheetJS
  } else {
    console.error("No file selected.");
  }
}

function handleFileSelect(event) {
  const file = event.target.files[0];
  const reader = new FileReader();
  reader.onload = (e) => {
    const data = new Uint8Array(e.target.result);
    const workbook = XLSX.read(data, { type: "array" });
    populateSheetNames(workbook.SheetNames);
  };
  reader.readAsArrayBuffer(file);
}

function populateSheetNames(sheetNames) {
  const select = document.getElementById("sheetSelect");
  select.innerHTML = sheetNames.map((name) => `<option value="${name}">${name}</option>`).join("");
}

async function fetchUserDetails(emails, testName) {
  const data = new FormData();
  data.append("action", "fetch_user_grades"); // The WordPress action hook
  data.append("emails", JSON.stringify(emails)); // Encode array as JSON string
  data.append("test_name", testName);

  console.log();
  try {
    const response = await fetch(adminAjax.ajaxUrl, {
      method: "POST",
      credentials: "same-origin",
      body: data,
    });
    if (!response.ok) throw new Error("Network response was not ok.");

    const result = await response.json();
    if (result.success) {
      return result.data; // Assuming the server response includes user details in 'data'
    } else {
      throw new Error("Error fetching user details: " + result.data);
    }
  } catch (error) {
    console.error("Fetch error:", error.message);
    throw error; // Re-throw to be caught by the caller
  }
}
function populateGradePreviewTable(userDetails) {
  const tableBody = getElement(
    "#gradesPreviewTable tbody",
    "Table body not found in grades preview table"
  );
  if (!tableBody) {
    console.error("Table body not found");
    return;
  }
  tableBody.innerHTML = ""; // Clear existing rows
  console.log("USER DETAILs:", userDetails);

  userDetails.forEach((user) => {
    let rowClass = "";
    let nameCellClass = "";
    let gradeCellClass = "";
    let tooltipText = "";

    if (user.name === "User not found" || user.current_grade === "N/A") {
      rowClass = "highlight-row";
      if (user.name === "User not found") {
        nameCellClass = "error-cell";
        tooltipText = "Email incorrect or user does not exist.";
      }
      if (user.current_grade === "N/A") {
        gradeCellClass = "error-cell";
        tooltipText += user.name !== "User not found" ? " User not enrolled in group." : "";
      }
    }

    if (user.name !== "User not found" && user.current_grade === "N/A") {
      gradeCellClass = "new-grade-cell";
      tooltipText = "New grade being assigned.";
    }

    const row = `
      <tr class="${rowClass}" title="${tooltipText}">
        <td class="${nameCellClass}">${user.name || "Name not found"}</td>
        <td>${user.email}</td>
        <td class="${gradeCellClass}">${user.current_grade || "N/A"}</td>
        <td>${user.new_grade}</td>
      </tr>`;
    tableBody.innerHTML += row;
  });
  updateAlertBox(userDetails);
}
function updateAlertBox(userDetails) {
  const notFound = userDetails.filter((user) => !user.name || user.current_grade === "N/A");
  const alertBox = getElement("#alertBox", "Alert box not found");
  if (!alertBox) {
    console.error("Alert box not found");
    return;
  }

  if (notFound.length > 0) {
    alertBox.innerHTML = `Attention: ${notFound.length} entries could not be fully resolved due to missing information or incorrect emails.`;
    alertBox.style.display = "block";
    alertBox.style.backgroundColor = "#f8d7da"; // Light red background for errors
  } else {
    alertBox.style.display = 'none';
  }
}
function saveGrades() {
  const userDetails = gatherUserDetailsForSaving();
  if (userDetails.length === 0) {
    displayInfoMessage("No updates needed; current grades and new grades are the same.");
    return; // Exit the function if there are no changes
}
  const data = new FormData();
  data.append("action", "save_user_grades");
  data.append("grades_data", JSON.stringify(userDetails));

  fetch(adminAjax.ajaxUrl, {
    method: "POST",
    credentials: "same-origin",
    body: data,
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        if (result.data.results) {
          populateGradePreviewTable(result.data.results);
          displaySuccessMessage(`${result.data.results.length} users updated successfully.`);
      } else {
          displayErrorMessage("No updates were performed.");
      }
      } else {
        displayErrorMessage("Failed to save grades: " + result.data);
      }
    })
    .catch((error) => {
      displayErrorMessage("Error occurred while saving grades: " + error.message);
    });
}
function gatherUserDetailsForSaving() {
  const rows = document.querySelectorAll("#gradesPreviewTable tbody tr");
  return Array.from(rows).map(row => {
      const email = row.cells[1].textContent;
      const current_grade = row.cells[2].textContent;
      const new_grade = row.cells[3].textContent;
      const test_name = document.getElementById("sheetSelect").value;

      return {
          email: email,
          current_grade: current_grade,
          new_grade: new_grade,
          test_name: test_name,
          hasChange: current_grade !== new_grade
      };
  }).filter(user => user.hasChange); // Only include users where there is a change
}

function displayInfoMessage(message) {
  const alertBox = getElement("#alertBox", "Alert box not found");
  alertBox.innerHTML = message;
  alertBox.style.display = "block";
  alertBox.style.backgroundColor = "#fefeb8"; // Light yellow background for informational messages
}


function displaySuccessMessage(message) {
  const alertBox = getElement("#alertBox", "Alert box not found");
  alertBox.innerHTML = message;
  alertBox.style.display = "block";
  alertBox.style.backgroundColor = "#d4edda"; // Light green background
}

function displayErrorMessage(message) {
  const alertBox = getElement("#alertBox", "Alert box not found");
  alertBox.innerHTML = message;
  alertBox.style.display = "block";
  alertBox.style.backgroundColor = "#f8d7da"; // Light red background
}

function triggerSearch(elements) {
  const params = {
    searchTerm: elements["#user-search-input"].value.trim(),
    searchType: elements["#search-type"].value,
  };
  debounceSearch(params);
}

function fetchUserTable(params) {
  console.log("Entered fetchUserTable with params:", params);
  const { searchTerm, searchType } = params;
  let queryURL = `${adminAjax.ajaxUrl}?action=fetch_users_admin_grades_page`;
  queryURL += `&s=${encodeURIComponent(searchTerm)}&type=${encodeURIComponent(searchType)}`;
  console.log("QUERY URL: ", queryURL);

  fetch(queryURL, {
    method: "GET",
    credentials: "same-origin",
  })
    .then(validateResponse)
    .then((data) => updateTable(data.data))
    .catch(handleError);
}

function updateTable(data) {
  const { courses, users } = data;

  if (courses) {
    updateCoursesTable(courses);
  }

  if (users) {
    updateUsersTable(users);
  }
}
function updateCoursesTable(courses) {
  console.log("Entered updateCoursesTable");
  const tableBody = getElement(
    "#coursesTableContainer tbody",
    "Table body not found in courses table container"
  );
  tableBody.innerHTML = courses.length
    ? generateCourseRows(courses)
    : '<tr><td colspan="3" style="text-align:center;">No courses found.</td></tr>';
}
function generateCourseRows(courses) {
  return courses
    .map(
      (course) => `
  <tr>
    <td><button class="button view-course" data-course-id="${course.id}"">${course.name}</button></td>
    <td>${course.start_date}</td>
    <td>${course.end_date}</td>
  </tr>
`
    )
    .join("");
}

function updateUsersTable(users) {
  const tableBody = getElement(
    "#usersTableContainer tbody",
    "Table body not found in users table container"
  );
  tableBody.innerHTML = users.length
    ? generateUserRows(users)
    : '<tr><td colspan="5" style="text-align:center;">No users found.</td></tr>';
}

function generateUserRows(users) {
  return users
    .map(
      (user) => `
    <tr>
      <td>${user.groups || "N/A"}</td>
      <td>${user.display_name || "N/A"}</td>
      <td>${user.email || "N/A"}</td>
      <td>${user.path || "N/A"}</td>
      <td><button class="button view-grades" data-user-id="${user.id}">View Grades</button></td>
    </tr>
  `
    )
    .join("");
}
function handleCourseActions(event) {
  console.log("Entered handleCourseActions");
  if (event.target.classList.contains("view-course")) {
    event.preventDefault();
    console.log("EVENT-TARGET : ", event.target);
    fetchCourseStudents(event.target.dataset.courseId);
  }
}
function fetchCourseStudents(courseId) {
  console.log("Entered fetchCourseStudents with courseID", courseId);
  const spinner = getElement("#spinner", "Spinner element not found");
  spinner.style.display = "block";
  const request = {
    method: "POST",
    credentials: "same-origin",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=fetch_students_by_course&course_id=${encodeURIComponent(courseId)}`,
  };
  console.log("fetchCourseStudents before fetch with request: ", request);
  fetch(adminAjax.ajaxUrl, request)
    .then(validateResponse)
    .then((data) => {
      console.log(
        "fetchCourseStudents returned data from ajax handler(fetch_students_by_course): ",
        data
      );
      updateUsersTable(data.data);
    })
    .catch(handleError)
    .finally(() => (spinner.style.display = "none"));
}

function handleUserActions(event) {
  if (event.target.classList.contains("view-grades")) {
    event.preventDefault();
    fetchGrades(event.target.dataset.userId);
  }
}

function fetchGrades(userId) {
  const spinner = getElement("#spinner", "Spinner element not found");
  spinner.style.display = "block";
  const request = {
    method: "POST",
    credentials: "same-origin",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=fetch_grades&user_id=${userId}`,
  };

  fetch(adminAjax.ajaxUrl, request)
    .then(validateResponse)
    .then((data) => showGrades(data.data, userId))
    .catch(handleError)
    .finally(() => (spinner.style.display = "none"));
}

function showGrades(grades, userId) {
  const tableBody = getElement("#gradesTable tbody", "Grades table body not found");
  tableBody.innerHTML = generateGradesRows(grades, userId);
  document.getElementById("gradesModal").style.display = "block";
}

function generateGradesRows(grades, userId) {
  return grades
    .map(
      (grade, index) => `
    <tr data-user-id=${userId} data-index=${index}>
      <td data-label="grade-name">${grade.grade_name}</td>
      <td data-label="grade-type">${grade.grade_type}</td>
      <td data-label="grade-status">${grade.grade_status}</td>
      <td data-label="grade-score">${grade.grade_score || "N/A"}</td>
      <td data-label="grade-deadline">${grade.grade_deadline}</td>
      <td data-label="grade-feedback">${grade.grade_feedback || "None"}</td>
      <td data-label="last-modified">${grade.last_modified || "Unknown"}</td>
      <td data-label="actions"><button class="button edit-grade" data-index="${index}" data-user-id="${userId}">Edit</button></td>
    </tr>
  `
    )
    .join("");
}

function getElement(selector, error) {
  const element = document.querySelector(selector);
  if (!element) {
    console.error(error);
    throw new Error(error);
  }
  return element;
}

function getElements(selectors) {
  const elements = {};
  selectors.forEach((selector) => {
    const element = document.querySelector(selector);
    if (!element) {
      console.error(`Element not found: ${selector}`);
      throw new Error(`Element not found: ${selector}`);
    }
    elements[selector] = element;
  });
  return elements;
}

function validateResponse(response) {
  if (!response.ok) throw new Error("Network response was not ok");
  return response.json();
}

function handleError(error) {
  console.error("Error: ", error.message);
}

function handleGradeActions(event) {
  const target = event.target;
  if (!target) return;

  const row = target.closest("tr");
  if (!row) {
    console.error("Failed to find the row associated with the action");
    return;
  }

  const userId = row.dataset.userId;
  const index = row.dataset.index;

  if (target.classList.contains("edit-grade")) {
    console.log(`Editing grade for user ID: ${userId}, index: ${index}`);
    editGrade(row, index, userId);
  } else if (target.classList.contains("save-grade")) {
    console.log(`Saving grade for user ID: ${userId}, index: ${index}`);
    saveGrade(row, index, userId);
  }
}

function editGrade(row, index, userId) {
  const statusCell = row.querySelector('[data-label="grade-status"]');
  const scoreCell = row.querySelector('[data-label="grade-score"]');
  const feedbackCell = row.querySelector('[data-label="grade-feedback"]');
  const deadlineCell = row.querySelector('[data-label="grade-deadline"]');

  if (!statusCell.querySelector("input")) {
    statusCell.innerHTML = `<input type="text" value="${statusCell.textContent.trim()}" />`;
  }
  if (!scoreCell.querySelector("input")) {
    scoreCell.innerHTML = `<input type="text" value="${scoreCell.textContent.trim()}" />`;
  }
  if (!feedbackCell.querySelector("input")) {
    feedbackCell.innerHTML = `<input type="text" value="${feedbackCell.textContent.trim()}" />`;
  }
  if (!deadlineCell.querySelector("select")) {
    const currentDeadline = deadlineCell.textContent.trim();
    deadlineCell.innerHTML = `
      <select>
        <option value="A" ${currentDeadline === "A" ? "selected" : ""}>A</option>
        <option value="B" ${currentDeadline === "B" ? "selected" : ""}>B</option>
        <option value="C" ${currentDeadline === "C" ? "selected" : ""}>C</option>
        <option value="D" ${currentDeadline === "D" ? "selected" : ""}>D</option>
        <option value="E" ${currentDeadline === "E" ? "selected" : ""}>E</option>
        <option value="F" ${currentDeadline === "F" ? "selected" : ""}>F</option>
        <option value="G" ${currentDeadline === "G" ? "selected" : ""}>G</option>
      </select>
    `;
  }

  const saveButton = document.createElement("button");
  saveButton.textContent = "Save";
  saveButton.className = "button save-grade";
  saveButton.dataset.index = index;
  saveButton.dataset.userId = userId;

  const actionsCell = row.querySelector('[data-label="actions"]');
  actionsCell.innerHTML = "";
  actionsCell.appendChild(saveButton);
}

function saveGrade(row, index, userId) {
  const status = row.querySelector('[data-label="grade-status"] input').value;
  const score = row.querySelector('[data-label="grade-score"] input').value;
  const feedback = row.querySelector('[data-label="grade-feedback"] input').value;
  const deadline = row.querySelector('[data-label="grade-deadline"] select').value;

  fetch(adminAjax.ajaxUrl, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=save_grades&user_id=${userId}&index=${index}&status=${status}&score=${score}&feedback=${feedback}&deadline=${deadline}`,
  })
    .then(validateResponse)
    .then((data) => {
      if (data.success) {
        console.log("Grade saved successfully:", data);
        row.querySelector('[data-label="grade-status"]').textContent = status;
        row.querySelector('[data-label="grade-score"]').textContent = score;
        row.querySelector('[data-label="grade-deadline"]').textContent = deadline;
        row.querySelector('[data-label="grade-feedback"]').textContent = feedback;
        row.querySelector('[data-label="last-modified"]').textContent =
          data.data.updated_grade.last_modified;

        const editButton = document.createElement("button");
        editButton.textContent = "Edit";
        editButton.className = "button edit-grade";
        editButton.dataset.index = index;
        editButton.dataset.userId = userId;
        const actionsCell = row.querySelector('[data-label="actions"]');
        actionsCell.innerHTML = "";
        actionsCell.appendChild(editButton);
      } else {
        console.error("Error saving grade:", data);
      }
    })
    .catch((error) => console.error(`Error saving grade for user ID ${userId}:`, error));
}

function shouldTriggerSearch({ searchTerm }) {
  // Basic checks
  if (searchTerm.length > 0 && searchTerm.length < 3) return false;
  return searchTerm.length >= 3 || searchTerm === "";
}

function debounceSearch(params) {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    if (shouldTriggerSearch(params)) {
      fetchUserTable(params);
    }
  }, 300);
}
