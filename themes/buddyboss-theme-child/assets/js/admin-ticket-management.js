document.addEventListener("DOMContentLoaded", function () {
  initialize();
  setupSearchInput();
});

function initialize() {
  setupButtonEventHandlers(".edit-status", handleEditStatusClick);
  setupButtonEventHandlers(".save-status", handleSaveStatusClick);
  setupSectorEmailsForm();
  setupModalClickOutsideClose();
}
// Real-time user filtering based on search input
// const searchInput = document.getElementById("user-search-input");
// searchInput.addEventListener("input", function () {
//   const searchTerm = this.value.toLowerCase();
//   const userRows = document.querySelectorAll(".wp-list-table tbody tr");

//   userRows.forEach((row) => {
//     const usernameCell = row.querySelector(".user_display_name");
//     const sectorCell = row.querySelector(".sector");
//     const subjectCell = row.querySelector(".sector-subject");
//     const titleCell = row.querySelector(".title");

//     const username = usernameCell.textContent.toLowerCase();
//     const sector = sectorCell.textContent.toLowerCase();
//     const subject = subjectCell.textContent.toLowerCase();
//     const title = titleCell.textContent.toLowerCase();

//     if (
//       username.includes(searchTerm) ||
//       sector.includes(searchTerm) ||
//       subject.includes(searchTerm) ||
//       title.includes(searchTerm)
//     ) {
//       row.style.display = "table-row";
//     } else {
//       row.style.display = "none";
//     }
//   });
// });
function setupSearchInput() {
  const searchInput = document.getElementById("user-search-input");
  searchInput.addEventListener("input", debounce(handleSearchInput, 300));
}

function debounce(func, delay) {
  let debounceTimer;
  return function () {
      const context = this;
      const args = arguments;
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => func.apply(context, args), delay);
  };
}

function handleSearchInput(event) {
  const searchTerm = event.target.value.trim();
  if (searchTerm.length >= 3) {
      fetchFilteredUsers(searchTerm);
  } else if (searchTerm.length === 0) {
      fetchFilteredUsers(""); // Fetch all users when search term is cleared
  }
}



function setupButtonEventHandlers(selector, handlerFunction) {
  document.querySelectorAll(selector).forEach((button) => {
    button.addEventListener("click", handlerFunction);
  });
}
function handleEditStatusClick() {
  const ticketId = this.dataset.ticketId;

  toggleVisibility(`.status-select[data-ticket-id="${ticketId}"]`);
  toggleVisibility(`.status-view[data-ticket-id="${ticketId}"]`);
  toggleVisibility(`#feedbackDisplay-${ticketId}`);
  toggleVisibility(`#feedbackEdit-${ticketId}`);

  document
    .querySelector(`.save-status[data-ticket-id="${ticketId}"]`)
    .classList.remove("hidden");
  this.classList.add("hidden");
}

function handleSaveStatusClick() {
  const ticketId = this.dataset.ticketId;
  updateTicketStatus(ticketId);
}

function updateTicketStatus(ticketId) {
  const select = document.querySelector(
    `.status-select[data-ticket-id="${ticketId}"]`
  );
  const newStatus = select ? select.value : null;
  const feedbackInput = document.getElementById(`feedback-${ticketId}`);
  const newFeedback = feedbackInput ? feedbackInput.value : null;
  const formData = new FormData();
  formData.append("action", "update_ticket_status");
  formData.append("ticket_id", ticketId);
  formData.append("status", newStatus);
  formData.append("feedback", newFeedback);
  if (newStatus) {
    fetch(adminAjax.ajaxUrl, { method: "POST", body: formData })
      .then((response) =>
        response.ok
          ? response.json()
          : Promise.reject("Network response was not ok")
      )
      .then((data) =>
        handleTicketUpdateResponse(data, ticketId, select, newFeedback)
      )
      .catch(console.error);
  }
}

function handleTicketUpdateResponse(data, ticketId, select, newFeedback) {
  if (data.success) {
    updateUIAfterTicketStatusChange(ticketId, select, newFeedback);
    alert("Ticket updated successfully.");
  } else {
    console.error("Failed to update the status: ", data);
    alert("An error occurred while updating the ticket. Please try again.");
  }
}

function updateUIAfterTicketStatusChange(ticketId, select, newFeedback) {
  const statusView = document.querySelector(
    `.status-view[data-ticket-id="${ticketId}"]`
  );
  if (statusView) {
    statusView.textContent = select.options[select.selectedIndex].text;
    console.log("StatusView after Update: ", statusView.textContent);
    statusView.classList.remove("hidden");
  }
  const feedbackDisplay = document.getElementById(
    `feedbackDisplay-${ticketId}`
  );
  if (feedbackDisplay) {
    feedbackDisplay.textContent = newFeedback;
  }
  select.classList.add("hidden");
  document.getElementById(`feedback-${ticketId}`).classList.add("hidden");
  statusView.classList.remove("hidden");
  feedbackDisplay.classList.remove("hidden");
  document
    .querySelector(`.edit-status[data-ticket-id="${ticketId}"]`)
    .classList.remove("hidden");
  document
    .querySelector(`.save-status[data-ticket-id="${ticketId}"]`)
    .classList.add("hidden");
}

function toggleVisibility(selector) {
  document.querySelector(selector).classList.toggle("hidden");
}

function setupSectorEmailsForm() {
  const sectorEmailsForm = document.getElementById("sectorEmailsForm");
  sectorEmailsForm.addEventListener("submit", handleSectorEmailsFormSubmit);
}

function handleSectorEmailsFormSubmit(event) {
  console.log("Entered handleSectorEmailsFormSubmit");
  event.preventDefault();
  const sectorNames = Array.from(
    document.querySelectorAll(".sector-email-row .sector-name")
  ).map((span) => span.textContent);
  const sectorEmails = Array.from(
    document.querySelectorAll('[name="sectorEmail[]"]')
  ).map((input) => input.value);
  console.log("\nSector Names: ", sectorNames);
  console.log("\nSector Emails: ", sectorEmails);
  const data = new FormData(this);

  data.append("action", "update_sector_emails");
  data.append("sectorNames", JSON.stringify(sectorNames));
  data.append("sectorEmails", JSON.stringify(sectorEmails));
  data.forEach((item, index) => {
    console.log(`DATA  row - `, item, index);
  });
  fetch(ajaxurl, { method: "POST", credentials: "same-origin", body: data })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Sector emails updated successfully.");
        populateSectorEmailsForm();
      } else {
        alert("An error occurred: " + data.data.message);
      }
    })
    .catch((error) => {
      console.error("Error updating sector emails:", error);
      alert("An error occurred while updating sector emails.");
    });
}

function populateSectorEmailsForm() {
  fetch(ajaxurl, {
    method: "POST",
    credentials: "same-origin",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "action=fetch_sector_emails",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data.sectors) {
        const formContainer = document.getElementById("sectorEmailsForm");
        formContainer.innerHTML =
          data.data.sectors
            .map((sector) => sectorEmailRowTemplate(sector))
            .join("") + sectorEmailFormFooter();
        setupSectorEmailEditAndSave(formContainer);
        document
          .getElementById("addSectorEmail")
          .addEventListener("click", addNewSectorRow);
      }
    })
    .catch(console.error);
}

function setupSectorEmailEditAndSave(formContainer) {
  formContainer
    .querySelectorAll(".edit-sector-email")
    .forEach((button) => button.addEventListener("click", editSectorEmail));
  formContainer
    .querySelectorAll(".save-sector-email")
    .forEach((button) => button.addEventListener("click", saveSectorEmail));
  formContainer
    .querySelectorAll(".remove-sector-email")
    .forEach((button) => button.addEventListener("click", removeSectorEmail));
}

function editSectorEmail() {
  const parentDiv = this.parentNode;
  parentDiv.querySelector(".sector-email-display").classList.add("hidden");
  const editInput = parentDiv.querySelector(".sector-email-edit");
  editInput.classList.remove("hidden");
  editInput.required = true;
  this.classList.add("hidden");
  parentDiv.querySelector(".save-sector-email").classList.remove("hidden");
  parentDiv.querySelector(".remove-sector-email").classList.remove("hidden");
}

function saveSectorEmail() {
  const parentDiv = this.parentNode;
  const editInput = parentDiv.querySelector(".sector-email-edit");
  parentDiv.querySelector(".sector-email-display").textContent =
    editInput.value;
  toggleEditSaveVisibility(parentDiv, editInput, false);
}

function toggleEditSaveVisibility(parentDiv, editInput, isEditing) {
  parentDiv
    .querySelector(".sector-email-display")
    .classList.toggle("hidden", isEditing);
  editInput.classList.toggle("hidden", !isEditing);
  editInput.required = isEditing;
  parentDiv
    .querySelector(".edit-sector-email")
    .classList.toggle("hidden", isEditing);
  parentDiv
    .querySelector(".save-sector-email")
    .classList.toggle("hidden", !isEditing);
  parentDiv
    .querySelector(".remove-sector-email")
    .classList.toggle("hidden", !isEditing);
}

function sectorEmailRowTemplate(sector) {
  return `
        <div class="sector-email-row">
            <span class="sector-name">${sector.name}</span>
            <span class="sector-email-display">${sector.email}</span>
            <input type="email" class="sector-email-edit hidden" name="sectorEmail[]" value="${sector.email}">
            <button type="button" class="edit-sector-email button">Edit</button>
            <button type="button" class="save-sector-email button hidden">Save</button>
            <button type="button" class="remove-sector-email button hidden">Remove</button>
        </div>
    `;
}

function sectorEmailFormFooter() {
  return `
        <button type="button" id="addSectorEmail" class="button">Add New Sector Email</button>
        <button type="submit" class="button">Save Changes</button>
        `;
}
function addNewSectorRow() {
  const formContainer = document.getElementById("sectorEmailsForm");
  const addButton = document.getElementById("addSectorEmail");
  const newSectorRow = document.createElement("div");
  newSectorRow.classList.add("sector-email-row");
  console.log("Add New Sector Triggered");
  newSectorRow.innerHTML = `
      <input type="text" placeholder="Sector Name" class="sector-name new-sector-name" required>
      <input type="email" placeholder="Sector Email" class="sector-email-edit new-sector-email" required>
      <button type="button" class="save-new-sector-email button">Save</button>
      <button type="button" class="cancel-new-sector-email button">Cancel</button>
    `;
  formContainer.insertBefore(newSectorRow, addButton);
  newSectorRow
    .querySelector(".save-new-sector-email")
    .addEventListener("click", saveNewSectorEmail);
  newSectorRow
    .querySelector(".cancel-new-sector-email")
    .addEventListener("click", function () {
      newSectorRow.remove();
    });
}
function saveNewSectorEmail() {
  const parentDiv = this.parentNode;
  const newSectorNameInput = parentDiv.querySelector(".new-sector-name");
  const newSectorEmailInput = parentDiv.querySelector(".new-sector-email");

  if (newSectorNameInput.value && newSectorEmailInput.value) {
    // Construct the new sector object to be saved
    const sector = {
      name: newSectorNameInput.value,
      email: newSectorEmailInput.value,
    };
    console.log("Saving new sector:", sector);
    parentDiv.outerHTML = sectorEmailRowTemplate(sector);
    // populateSectorEmailsForm();
    // Optionally, remove the row after saving
    // parentDiv.remove();
  } else {
    alert("Please fill out both the sector name and email.");
  }
}
function removeSectorEmail() {
  const parentDiv = this.parentNode;
  parentDiv.remove();
}
function setupModalClickOutsideClose() {
  window.onclick = function (event) {
    if (event.target.classList.contains("modal")) {
      event.target.style.display = "none";
    }
  };
}

function toggleModal(modalId, shouldShow) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = shouldShow ? "block" : "none";
  }
}

function openModal(ticketId) {
  toggleModal("contentModal-" + ticketId, true);
}

function closeModal(ticketId) {
  toggleModal("contentModal-" + ticketId, false);
}

function openEmailSettingsModal() {
  toggleModal("sector-emails-modal", true);
  populateSectorEmailsForm();
}

function closeEmailSettingsModal() {
  toggleModal("sector-emails-modal", false);
}
