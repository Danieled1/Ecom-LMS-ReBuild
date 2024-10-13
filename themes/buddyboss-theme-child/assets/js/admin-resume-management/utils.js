import { initStatusManagement } from './statusManagement.js';

export let debounceTimeout = null;

export function getElement(selector, error) {
    const element = document.querySelector(selector);
    if (!element) {
      console.error(error);
      throw new Error(error);
    }
    return element;
  }
  
  export function getElements(selectors) {
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
  
  export function validateResponse(response) {
    if (!response.ok) throw new Error("Network response was not ok");
    return response.json();
  }
  
  export function handleError(error) {
    console.error("Error: ", error.message);
  }
  
  export function debounceSearch(params) {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
      if (shouldTriggerSearch(params)) {
        fetchUserTable(params);
      }
    }, 300);
  }
  
  export function toggleElements(elements) {
    console.log("toggleElements activated for: ", elements);
    elements.forEach((element) => {
        if (typeof element === "string") {
            element = document.querySelector(element);
        }

        if (element) {
            console.log("Toggling element: ", element);
            element.classList.toggle("hidden");

            // Show or hide based on the presence of the "hidden" class
            if (element.classList.contains("hidden")) {
                element.style.display = "none";
            } else {
                element.style.display = "flex"; // Changed to "flex" to match the CSS display property
            }

        } else {
            console.error("Element not found: ", element);
        }
    });
}


  
  export function shouldTriggerSearch({ searchTerm, jobStatus, placementNotes, specificDate, startDate, endDate, dateFilterType }) {
    if (searchTerm.length > 0 && searchTerm.length < 3) return false;
    if (dateFilterType === "specific-date" && !specificDate) return false;
    if (dateFilterType === "date-range" && (!startDate || !endDate)) return false;
    return searchTerm.length >= 3 || jobStatus || placementNotes || specificDate || startDate || endDate || searchTerm === "";
  }
  
  export async function fetchUserTable(params) {
    const { searchTerm, jobStatus, placementNotes, specificDate, startDate, endDate } = params;
    let queryURL = `${adminAjax.ajaxUrl}?action=fetch_user_data_resume`;
    queryURL += `&s=${encodeURIComponent(searchTerm)}`;
    queryURL += `&job_status=${encodeURIComponent(jobStatus)}`;
    queryURL += `&placement_notes=${encodeURIComponent(placementNotes)}`;
    queryURL += `&specific_date=${encodeURIComponent(specificDate)}`;
    queryURL += `&start_date=${encodeURIComponent(startDate)}`;
    queryURL += `&end_date=${encodeURIComponent(endDate)}`;
  
    fetch(queryURL, {
      method: "GET",
      credentials: "same-origin",
    })
      .then(validateResponse)
      .then((data) => {
        updateUsersTable(data.data.users);
      })
      .catch(handleError);
  }
  
  export async function updateUsersTable(users) {
    const tableBody = getElement("#usersTableContainer tbody", "Table body not found in users table container");
    tableBody.innerHTML = users.length
      ? generateUserRows(users)
      : '<tr><td colspan="5" style="text-align:center;">No users found.</td></tr>';
    await new Promise((resolve) => setTimeout(resolve, 0));
    initStatusManagement();
  }
  
  export function generateUserRows(users) {
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
  
  export function renderUsername(user) {
    return `<td class="username column-username" data-colname="Username">${user.display_name || "N/A"}</td>`;
  }
  
  export function renderEmail(user) {
    return `<td class="email column-email" data-colname="Email">${user.email || "N/A"}</td>`;
  }
  
  export function renderGroup(user) {
    return `<td class="group column-group" data-colname="Group">${user.group || "N/A"}</td>`;
  }
  
  export function renderJobStatus(user) {
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
  
  export function renderResume(user) {
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
  
  export function renderPlacementNotes(user) {
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
  
  export function renderLastUpdated(user) {
    return `<td class="last_updated column-last_updated" data-colname="Last Updated">${user.last_updated || "N/A"}</td>`;
  }
  
  export function renderActions(user) {
    return `
        <td class="actions column-actions" data-colname="Actions">
            <button class="button edit-status" data-user-id="${user.id}">Edit Status</button>
            <button class="button save-status hidden" data-user-id="${user.id}">Save</button>
        </td>`;
  }
  