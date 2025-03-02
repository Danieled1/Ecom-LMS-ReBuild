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
  const form = document.querySelector("#acf-form"); 
  if (form) {
    form.addEventListener("submit", function (event) {
      // alert("test");
      // If you want to prevent the actual submission for testing, uncomment the next line
      // event.preventDefault();
    });
  }
};
// function editDetail(key) {
//   const valueSpan = document.getElementById('value-' + key);
//   const inputField = document.getElementById('input-' + key);
//   const editButton = document.getElementById('edit-button-' + key);
//   const saveButton = document.querySelector('.save-button');

//   valueSpan.classList.add('hidden');
//   inputField.classList.remove('hidden');
//   editButton.classList.add('hidden');
//   saveButton.classList.remove('hidden');
// }

// function saveDetails() {
//   // Get all the input fields with a data-user attribute
//   const inputFields = document.querySelectorAll('input[data-user]');

//   // Create a FormData object to send the data
//   const data = new FormData();
//   data.append('action', 'update_interview_detail');
  
//   // Loop through each input field and add key/value pairs to the FormData
//   inputFields.forEach(inputField => {
//     const key = inputField.id.replace('input-', ''); // Assuming input IDs are of the form 'input-key'
//     const value = inputField.value;
//     const userId = inputField.dataset.user;

//     // Append the data for this field
//     data.append('key[]', key);   // Use an array to store multiple keys
//     data.append('value[]', value); // Use an array to store multiple values
//     data.append('user_id', userId); // Assuming user_id remains the same for all fields
//   });

//   console.log("Current data object = ", data);

//   // Send the data to the server using fetch
//   fetch(adminAjax.ajaxUrl, {
//       method: 'POST',
//       body: data,
//   })
//   .then(response => response.json())
//   .then(data => {
//       console.log("Response came back with: ", data);
//       if (data.success) {
//           // Iterate over each field and update the value
//           inputFields.forEach(inputField => {
//               const key = inputField.id.replace('input-', '');
//               const valueSpan = document.getElementById('value-' + key);
//               valueSpan.textContent = inputField.value;
              
//               // Hide the input field, show the value span and buttons
//               valueSpan.classList.remove('hidden');
//               inputField.classList.add('hidden');
//               document.getElementById('edit-button-' + key).classList.remove('hidden');
//               document.querySelector('.save-button').classList.add('hidden');
//           });
//           alert('Details updated successfully.');
//       } else {
//           alert('Failed to update details. Please try again.');
//       }
//   })
//   .catch(error => {
//       console.error('Error:', error);
//       alert('An error occurred. Please try again.');
//   });
// }


// Add event listener to call the function
document.addEventListener("DOMContentLoaded", autoCompleteFormInputs);
document.addEventListener("DOMContentLoaded", logFormSubmissions);
