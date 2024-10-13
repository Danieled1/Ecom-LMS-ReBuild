import { getElements, validateResponse, debounceSearch, toggleElements } from "./utils.js";

let triggerSearch;

export function setupEditStatusEventListeners() {
  document.querySelectorAll(".edit-status").forEach((button) => {
    button.addEventListener("click", function () {
      handleEditStatus(button);
    });
  });
}

export function setupSaveStatusEventListeners() {
  document.querySelectorAll(".save-status").forEach((button) => {
    button.addEventListener("click", function () {
      handleSaveStatus(button);
    });
  });
}

export function handleEditStatus(button) {
  let userId = button.dataset.userId;
  let select = document.querySelector(`.status-select[data-user-id="${userId}"]`);
  let span = document.querySelector(`.status-text[data-user-id="${userId}"]`);
  let saveButton = document.querySelector(`.save-status[data-user-id="${userId}"]`);
  let notesEdit = document.querySelector(`#notesEdit-${userId}`);

  if (select && span && saveButton) {
    toggleElements([select, span, button, saveButton, notesEdit]);
  } else {
    console.error("One or more elements could not be found for user ID: " + userId);
  }
}

export function handleSaveStatus(button) {
  let userId = button.dataset.userId;
  let select = document.querySelector(`.status-select[data-user-id="${userId}"]`);
  let newStatus = select ? select.value : null;
  let fileInput = document.querySelector(`.placement-notes-input[data-user-id="${userId}"]`);
  let file = fileInput ? fileInput.files[0] : null;

  let formData = new FormData();
  formData.append("action", "update_user_job_status");
  formData.append("user_id", userId);
  formData.append("status", newStatus);
  if (file) {
    formData.append("placement_notes_file", file);
  }

  fetch(adminAjax.ajaxUrl, {
    method: "POST",
    body: formData,
  })
    .then(validateResponse)
    .then((data) => {
      if (data.success) {
        updateStatusUI(userId, select, button, data.data);
      } else {
        alert("Failed to update the status. Please try again.");
      }
    })
    .catch((error) => {
      console.error("Error: ", error);
      alert("An error occurred while updating the status. Please try again.");
    });
}

export function handleEditEmail() {
  console.log("Entered handleEditEmail");
  const emailDisplay = document.querySelector("#emailDisplay");
  const emailEdit = document.querySelector("#emailEdit");

  if (emailDisplay && emailEdit) {
    console.log("Email display and edit elements found");
    toggleElements([emailDisplay, emailEdit]);
    console.log(
      "After toggleElements handleEditEmail",
      emailDisplay.style.display,
      emailEdit.style.display
    );
  } else {
    console.error("Email display or edit element not found");
  }
}

export function handleSaveEmail() {
  const email = document.getElementById("emailInput").value;
  const data = new FormData();
  data.append("action", "update_custom_email");
  data.append("email", email);

  fetch(adminAjax.ajaxUrl, {
    method: "POST",
    body: data,
  })
    .then(validateResponse)
    .then((data) => {
      if (data.success) {
        document.getElementById("emailText").textContent = email;
        toggleElements(["#emailDisplay", "#emailEdit"]);
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

export function attachEventListeners() {
  const elements = getElements([
    "#user-search-input",
    "#job-status-filter",
    "#placement-notes-filter",
    "#date-filter-type",
    "#specific-date-input",
    "#start-date-input",
    "#end-date-input",
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
  elements["#date-filter-type"].addEventListener("change", handleDateFilterChange);
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

  triggerSearch();
}

function updateStatusUI(userId, select, button, data) {
  // Update the status text on success
  let statusText = document.querySelector(`.status-text[data-user-id="${userId}"]`);
  if (statusText) {
    statusText.textContent = select.options[select.selectedIndex].text;
    statusText.classList.remove("hidden");
  }
  select.classList.add("hidden");
  button.classList.add("hidden");
  let editButton = document.querySelector(`.edit-status[data-user-id="${userId}"]`);
  if (editButton) {
    editButton.classList.remove("hidden");
  }

  // Update placement notes UI if file was uploaded successfully
  let notesDisplay = document.querySelector(`#notesDisplay-${userId}`);
  let notesEdit = document.querySelector(`#notesEdit-${userId}`);
  if (notesDisplay && notesEdit) {
    let fileURL = data.file_url; // Assuming the server sends back the file URL
    let lastUpdatedAt = data.last_updated_at; // Assuming this is sent back from the server
    let existingFileName = notesDisplay.querySelector("a")
      ? notesDisplay.querySelector("a").textContent
      : null;

    let updatedText = lastUpdatedAt
      ? `<small>Updated: ${lastUpdatedAt}</small><br>`
      : "<small>Updated: Refresh to see (For now)</small><br>";
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
}
