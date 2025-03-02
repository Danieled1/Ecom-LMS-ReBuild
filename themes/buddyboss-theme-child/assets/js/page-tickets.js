const sectors = {
    'customer_service': ['שאלה כללית', 'העברת זכאות', 'מעבר למסלול חדש', 'אחר'],
    'technical_support': ['שאלה כללית ', 'שגיאה בקוד', 'התקנת תוכנה', 'אחר'],
    'accounting': ['1 הנהלת חשבונות', '2 הנהלת חשבונות', '3 הנהלת חשבונות', '4 הנהלת חשבונות'],
    'course_management': ['שאלה כללית', 'הקפאת מסלול', 'מעבר כיתה', 'חומרים לדיגיטל', 'אחר']
};

function setupModalClickOutsideClose() {
    window.onclick = function(event) {
        if (event.target.classList.contains("modal")) {
            event.target.style.display = "none";
        }
    };
}

function toggleVisibility(elementId) {
    // Hide all ticket contents
    document.querySelectorAll('.ticket-content').forEach(function(content) {
        content.classList.add('hidden');
    });
    document.querySelectorAll('.ticket-history').forEach(function(content) {
        content.classList.add('hidden');
    });

    // Show the specific element
    var element = document.getElementById(elementId);
    if (element) {
        element.classList.remove('hidden');
    }
}

function openModal(ticketId) {
    console.log("Opened Modal for ticket ID:", ticketId);
    toggleModal("contentModal-" + ticketId, true);
}

function closeModal(ticketId) {
    console.log("Closed Modal for ticket ID:", ticketId);
    toggleModal("contentModal-" + ticketId, false);
}

function toggleModal(modalId, shouldShow) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = shouldShow ? "block" : "none";
    } else {
        console.log("Modal not found for ID:", modalId);
    }
}

function setupButtonEventHandlers(selector, handlerFunction) {
    // Use a closure to pass the right parameter to the handler
    var btn = document.getElementById(selector);
    if (btn) {
        btn.addEventListener("click", function() {
            handlerFunction(selector);
        });
    } else {
        console.log("Button not found for selector:", selector);
    }
}

function handleTicketHistoryBtnClick() {
    toggleVisibility('ticketHistory');
}

function handleTicketCreateBtnClick() {
    toggleVisibility('contactContent');
}

function renderSubSectors () {
    const sectorSelect = document.querySelector('select[name="acf[field_65f06082a4add]"]');
    console.log("sectorSelect - ", sectorSelect);
    sectorSelect.addEventListener('change', updateSubSectorOptions);
    updateSubSectorOptions(); // Initialize sub-sector options on page load
}

function updateSubSectorOptions() {
    const sectorSelect = document.querySelector('select[name="acf[field_65f06082a4add]"]');
    const subSectorSelect = document.querySelector('select[name="acf[field_65f064c9a4ae1]"]');
    const selectedSector = sectorSelect.value;
    subSectorSelect.innerHTML = '<option value="">בחרו תת נושא</option>';
    if (selectedSector && sectors[selectedSector]) {
        subSectorSelect.disabled = false;
        sectors[selectedSector].forEach(subSector => {
            const option = document.createElement('option');
            option.value = subSector;
            option.textContent = subSector;
            subSectorSelect.appendChild(option);
        });
    } else {
        subSectorSelect.disabled = true;
    }
}


function initialize() {
    setupModalClickOutsideClose();
    setupButtonEventHandlers('ticketHistoryBtn', handleTicketHistoryBtnClick);
    setupButtonEventHandlers('createTicketBtn', handleTicketCreateBtnClick);
    renderSubSectors();
    toggleVisibility('ticketHistory');
}

// Wait for the DOM to be fully loaded before running the initialize function
document.addEventListener('DOMContentLoaded', initialize);