import { handleEditEmail, handleSaveEmail } from "./eventHandlers.js";
export function initEmailManagement() {
    console.log("Entered initEmailManagement");
    const editEmailBtn = document.getElementById("editEmailBtn");
    const saveEmailBtn = document.getElementById("saveEmailBtn");

    if (editEmailBtn && saveEmailBtn) {
        editEmailBtn.addEventListener("click", handleEditEmail);
        saveEmailBtn.addEventListener("click", handleSaveEmail);
        console.log("initEmailManagement loaded with listeners");
    } else {
        console.error("Edit or Save email button not found");
    }
}
window.editEmail = handleEditEmail;
window.saveEmail = handleSaveEmail;