class AdminHelper {
    static toggleVisibility(selector, shouldShow) {
        document.querySelector(selector).classList.toggle("hidden");
    }

    static updateTicketStatus(ticketId, newStatus, newFeedback) {
        const formData = new FormData();
        formData.append("action", "update_ticket_status");
        formData.append("ticket_id", ticketId);
        formData.append("status", newStatus);
        formData.append("feedback", newFeedback);

        fetch(adminAjax.ajaxUrl, { method: "POST", body: formData })
            .then(response => response.json())
            .then(data => this.handleTicketUpdateResponse(data, ticketId, newStatus, newFeedback))
            .catch(console.error);
    }

    static handleTicketUpdateResponse(data, ticketId, newStatus, newFeedback) {
        if (data.success) {
            this.updateUIAfterTicketStatusChange(ticketId, newStatus, newFeedback);
            alert("Ticket updated successfully.");
        } else {
            console.error("Failed to update the status: ", data);
            alert("An error occurred while updating the ticket. Please try again.");
        }
    }

    static updateUIAfterTicketStatusChange(ticketId, newStatus, newFeedback) {
        this.toggleVisibility(`.status-select[data-ticket-id="${ticketId}"]`, false);
        this.toggleVisibility(`#feedback-${ticketId}`, false);
        const statusView = document.querySelector(`.status-view[data-ticket-id="${ticketId}"]`);
        const feedbackDisplay = document.getElementById(`feedbackDisplay-${ticketId}`);

        if (statusView) {
            statusView.textContent = newStatus;
            this.toggleVisibility(`.status-view[data-ticket-id="${ticketId}"]`, true);
        }
        if (feedbackDisplay && newFeedback !== null) {
            feedbackDisplay.textContent = newFeedback;
            this.toggleVisibility(`#feedbackDisplay-${ticketId}`, true);
        }

        this.toggleVisibility(`.edit-status[data-ticket-id="${ticketId}"]`, true);
        this.toggleVisibility(`.save-status[data-ticket-id="${ticketId}"]`, false);
    }
}
window.AdminHelper = AdminHelper;
