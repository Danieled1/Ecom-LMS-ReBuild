document.addEventListener("DOMContentLoaded", function () {
    let currentStep = 1;
    const totalSteps = 4;
    const formSections = document.querySelectorAll(".form-section");
    const stepCounter = document.getElementById("step-counter");

    function showStep(step) {
        formSections.forEach((section, index) => {
            section.classList.toggle("active", index === step - 1);
        });
        stepCounter.textContent = step;
    }

    document.querySelectorAll(".next-button").forEach(button => {
        button.addEventListener("click", () => {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            }
        });
    });

    document.querySelectorAll(".prev-button").forEach(button => {
        button.addEventListener("click", () => {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });
    });

    showStep(currentStep); // Show the initial step
});