const autoCompleteFormInputs = () => {
  const defaultName = "<?php echo esc_js($current_user->display_name); ?>";
  const defaultEmail = "<?php echo esc_js($current_user->user_email); ?>";

  // Check if the fields exist
  const nameField = document.querySelector('input[name="your-name"]');
  const emailField = document.querySelector('input[name="your-email"]');

  if (nameField && emailField) {
    nameField.value = defaultName;
    emailField.value = defaultEmail;
  }
};
const logFormSubmissions = () => {
  const form = document.querySelector("#acf-form"); // Replace with your form's ID
  if (form) {
    form.addEventListener("submit", function (event) {
      // alert("test");
      // If you want to prevent the actual submission for testing, uncomment the next line
      // event.preventDefault();
    });
  }
};
// Add event listener to call the function
document.addEventListener("DOMContentLoaded", autoCompleteFormInputs);
document.addEventListener("DOMContentLoaded", logFormSubmissions);
