document.addEventListener("DOMContentLoaded", function () {
  initStatusManagement();
  initEmailManagement();
  attachEventListeners();
});
let triggerSearch;
let debounceTimeout = null; 

function initStatusManagement() {
  setupEditStatusEventListeners();
  setupSaveStatusEventListeners();
}

function attachEventListeners() {
  const elements = getElements([
    "#user-search-input",
    "#job-status-filter",
    "#placement-notes-filter",
    "#date-filter-type",
    "#specific-date-input",
    "#start-date-input",
    "#end-date-input"
  ]);

  triggerSearch = () => {
    const params = {
      searchTerm: elements["#user-search-input"].value.trim(),
      jobStatus: elements["#job-status-filter"].value,
      placementNotes: elements["#placement-notes-filter"].value,
      specificDate: elements["#specific-date-input"].value,
      startDate: elements["#start-date-input"].value,
      endDate: elements["#end-date-input"].value,
    };
    debounceSearch(params);
  };

  elements["#user-search-input"].addEventListener("input", triggerSearch);
  elements["#job-status-filter"].addEventListener("change", triggerSearch);
  elements["#placement-notes-filter"].addEventListener("change", triggerSearch);
  elements["#date-filter-type"].addEventListener("change", () => handleDateFilterChange(elements));
  elements["#specific-date-input"].addEventListener("change", triggerSearch);
  elements["#start-date-input"].addEventListener("change", triggerSearch);
  elements["#end-date-input"].addEventListener("change", triggerSearch);
}

function handleDateFilterChange() {
  const dateFilterType = document.getElementById("date-filter-type");
  const specificDateInput = document.getElementById("specific-date-input");
  const startDateInput = document.getElementById("start-date-input");
  const endDateInput = document.getElementById("end-date-input");

  specificDateInput.value = "";
  startDateInput.value = "";
  endDateInput.value = "";

  specificDateInput.style.display = "none";
  startDateInput.style.display = "none";
  endDateInput.style.display = "none";

  switch (dateFilterType.value) {
    case "specific-date":
      specificDateInput.style.display = "block";
      break;
    case "date-range":
      startDateInput.style.display = "block";
      endDateInput.style.display = "block";
      break;
  }

  // Trigger a search update to apply any changes made to date filter settings
  triggerSearch();
}

function setupEditStatusEventListeners() {
  console.log("Setting up edit status event listeners"); // Debugging

  document.querySelectorAll(".edit-status").forEach((button) => {
    button.addEventListener("click", function () {
      console.log("Edit button clicked"); // Debugging

      handleEditStatus(button);
    });
  });
}

function setupSaveStatusEventListeners() {
  console.log("Setting up save status event listeners"); // Debugging

  document.querySelectorAll(".save-status").forEach((button) => {
    button.addEventListener("click", function () {
      console.log("Save button clicked"); // Debugging

      handleSaveStatus(button);
    });
  });
}

function handleEditStatus(button) {
  let userId = button.dataset.userId;
  console.log(`Handling edit status for user ID: ${userId}`);

  let select = document.querySelector(`.status-select[data-user-id="${userId}"]`);
  let span = document.querySelector(`.status-text[data-user-id="${userId}"]`);
  let saveButton = document.querySelector(`.save-status[data-user-id="${userId}"]`);
  let notesEdit = document.querySelector(`#notesEdit-${userId}`);

  console.log({ select, span, saveButton, notesEdit });

  if (select && span && saveButton) {
    select.classList.remove("hidden");
    span.classList.add("hidden");
    button.classList.add("hidden");
    saveButton.classList.remove("hidden");
    if (notesEdit) {
      notesEdit.classList.remove("hidden");
    }
  } else {
    console.error("One or more elements could not be found for user ID: " + userId);
  }
}

function handleSaveStatus(button) {
  let userId = button.dataset.userId;
  let select = document.querySelector(`.status-select[data-user-id="${userId}"]`);
  let newStatus = select ? select.value : null;
  console.log("NEW_STATUS:", newStatus);
  let fileInput = document.querySelector(`.placement-notes-input[data-user-id="${userId}"]`);
  let file = fileInput ? fileInput.files[0] : null;

  let formData = new FormData();
  formData.append("action", "update_user_job_status");
  formData.append("user_id", userId);
  formData.append("status", newStatus);
  if (file) {
    formData.append("placement_notes_file", file);
  }
  for (let [key, value] of formData.entries()) {
    console.log(key, value);
  }
  fetch(adminAjax.ajaxUrl, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        if (response.status === 413) {
          alert("File too large. Please upload a smaller file. MAX: 67108KB ");
        }
        console.log(response);
        throw new Error("HTTP status " + response.status);
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        // Update the status text on success
        console.log(data.data.message);
        let statusText = document.querySelector(`.status-text[data-user-id="${userId}"]`);
        if (statusText) {
          statusText.textContent = select.options[select.selectedIndex].text;
          statusText.classList.remove("hidden");
        }
        if (select) {
          select.classList.add("hidden");
        }
        button.classList.add("hidden");
        let editButton = document.querySelector(`.edit-status[data-user-id="${userId}"]`);
        if (editButton) {
          editButton.classList.remove("hidden");
        }
        // Update placement notes UI if file was uploaded successfully
        let notesDisplay = document.querySelector(`#notesDisplay-${userId}`);
        let notesEdit = document.querySelector(`#notesEdit-${userId}`);
        if (notesDisplay && notesEdit) {
          let fileURL = data.data.file_url; // Assuming the server sends back the file URL
          let lastUpdatedAt = data.data.last_updated_at; // Assuming this is sent back from the server
          let existingFileName = notesDisplay.querySelector("a")
            ? notesDisplay.querySelector("a").textContent
            : null;

          let updatedText = lastUpdatedAt
            ? `<small>Updated: ${lastUpdatedAt}</small><br>`
            : "<small>Updated: Refresh to see(ForNow)</small><br>";
          let fileLink;

          if (fileURL) {
            // If a new file was uploaded, update the link and name
            let fileName = fileURL.split("/").pop(); // Extract file name
            fileLink = `<a href="${fileURL}" target="_blank">${fileName}</a>`;
          } else if (existingFileName) {
            // If no new file was uploaded but an existing file name is found, keep the current file name and link
            let existingFileURL = notesDisplay.querySelector("a").href;
            fileLink = `<a href="${existingFileURL}" target="_blank">${existingFileName}</a>`;
          } else {
            // If no file was uploaded and no existing file is found, indicate no notes are uploaded
            fileLink = "<span>No Notes Uploaded</span>";
          }

          notesDisplay.innerHTML = updatedText + fileLink;
          notesEdit.classList.add("hidden"); // Hide the file input after successful upload or if no new file is selected
        }
      } else {
        console.error("Failed to update the status: ", data);
        alert("Failed to update the status. Please try again.");
      }
    })
    .catch((error) => {
      console.error("Error: ", error);
      alert("An error occurred while updating the status. Please try again.");
    });
}

function initEmailManagement() {
  document.getElementById("editEmailBtn").addEventListener("click", editEmail);
  document.getElementById("saveEmailBtn").addEventListener("click", saveEmail);
}

function editEmail() {
  document.getElementById("emailDisplay").style.display = "none";
  document.getElementById("emailEdit").style.display = "block";
}

function saveEmail() {
  const email = document.getElementById("emailInput").value;
  const data = new FormData();
  data.append("action", "update_custom_email");
  data.append("email", email);

  fetch(adminAjax.ajaxUrl, {
    method: "POST",
    body: data,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        document.getElementById("emailText").textContent = email;
        document.getElementById("emailDisplay").style.display = "block";
        document.getElementById("emailEdit").style.display = "none";
        alert("Email updated successfully.");
      } else {
        alert("Failed to update email. Please try again.");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred. Please try again.");
    });
}

function fetchUserTable(params) {
  const { searchTerm, jobStatus, placementNotes, specificDate, startDate, endDate } = params;
  let queryURL = `${adminAjax.ajaxUrl}?action=fetch_user_data_resume`;
  queryURL += `&s=${encodeURIComponent(searchTerm)}`;
  queryURL += `&job_status=${encodeURIComponent(jobStatus)}`;
  queryURL += `&placement_notes=${encodeURIComponent(placementNotes)}`;
  queryURL += `&specific_date=${encodeURIComponent(specificDate)}`;
  queryURL += `&start_date=${encodeURIComponent(startDate)}`;
  queryURL += `&end_date=${encodeURIComponent(endDate)}`;
  console.log("QUERY URL: ", queryURL);
  fetch(queryURL, {
    method: "GET",
    credentials: "same-origin",
  })
    .then(validateResponse)
    .then((data) => {
      console.log(data); // Log the data for debugging
      updateUsersTable(data.data.users);
    })
    .catch(handleError);
}

async function updateUsersTable(users) {
  const tableBody = getElement(
    "#usersTableContainer tbody",
    "Table body not found in users table container"
  );
  tableBody.innerHTML = users.length
    ? generateUserRows(users)
    : '<tr><td colspan="5" style="text-align:center;">No users found.</td></tr>';
  await new Promise((resolve) => setTimeout(resolve, 0));
  initStatusManagement();
}

function generateUserRows(users) {
  console.log("TEST TEST TEST", users);
  return users
    .map(
      (user) => `
  <tr>
      ${renderUsername(user)}
      ${renderEmail(user)}
      ${renderGroup(user)}
      ${renderJobStatus(user)}
      ${renderResume(user)}
      ${renderPlacementNotes(user)}
      ${renderLastUpdated(user)}
      ${renderActions(user)}
  </tr>
`
    )
    .join("");
}

function renderUsername(user) {
  return `<td class="username column-username" data-colname="Username">${
    user.display_name || "N/A"
  }</td>`;
}

function renderEmail(user) {
  return `<td class="email column-email" data-colname="Email">${user.email || "N/A"}</td>`;
}

function renderGroup(user) {
  return `<td class="group column-group" data-colname="Group">${user.group || "N/A"}</td>`;
}

function renderJobStatus(user) {
  return `
      <td class="job_status column-job_status" data-colname="Job Status">
          <small>Updated: ${user.job_status.updated_at}</small>
          <br>
          <span class="status-text" data-user-id="${user.id}">
              ${user.job_status.label || "No Status"}
          </span>
          <select class="status-select hidden" data-user-id="${user.id}">
              <option value="review">Review</option>
              <option value="published">Published</option>
              <option value="interview">Interview</option>
              <option value="hired">Hired</option>
          </select>
      </td>`;
}

function renderResume(user) {
  return `
    <td class="resume column-resume" data-colname="Resume">${
      user.resume.url
        ? `<small>Updated: ${user.resume.updated_at}</small>
            <br>
            <a href="${user.resume.url}" download=${user.resume.filename}>${user.resume.filename}</a>`
        : `<span>No Resume Uploaded</span>`
    }
  `;
}

function renderPlacementNotes(user) {
  return `
      <td class="placement_notes column-placement_notes" data-colname="Placement Notes">
          <div id="notesDisplay-${user.id}" class="notes-display">${
    user.placement_notes.url
      ? `<small>Updated: ${user.placement_notes.updated_at}</small>
                      <br>
                      <a href="${user.placement_notes.url}" download="${
          user.placement_notes.filename
        }">${user.placement_notes.filename || "No Filename"}<a>`
      : "No Notes Yet"
  }</div>
          <div id="notesEdit-${user.id}" class="notes-edit hidden">
              <input type="file" class="placement-notes-input" data-user-id="${user.id}">
          </div>
      </td>`;
}

function renderLastUpdated(user) {
  return `
  <td class="last_updated column-last_updated" data-colname="Last Updated">${user.last_updated || "N/A"}</td>`;
}

function renderActions(user) {
  return `
      <td class="actions column-actions" data-colname="Actions">
          <button class="button edit-status" data-user-id="${user.id}">Edit Status</button>
          <button class="button save-status hidden" data-user-id="${user.id}">Save</button>
      </td>`;
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

function shouldTriggerSearch({
  searchTerm,
  jobStatus,
  placementNotes,
  specificDate,
  startDate,
  endDate,
  dateFilterType,
}) {
  // Basic checks
  if (searchTerm.length > 0 && searchTerm.length < 3) return false;
  if (dateFilterType === "specific-date" && !specificDate) return false;
  if (dateFilterType === "date-range" && (!startDate || !endDate)) return false;
  return searchTerm.length >= 3 || jobStatus || placementNotes || specificDate || startDate || endDate || searchTerm === "";

}


function debounceSearch(params) {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    if (shouldTriggerSearch(params)) {
      fetchUserTable(params);
    }
  }, 300);
}
