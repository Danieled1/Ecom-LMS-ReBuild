console.log("Script initialized");
document.addEventListener("DOMContentLoaded", init);

function init() {
  getStudentGrades();
}

function getStudentGrades() {
  console.log("Fetching grades for user ID:", userInfo.userId);
  fetch(userInfo.ajaxUrl, {
    method: "POST",
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=fetch_grades&user_id=${userInfo.userId}`,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Server returned an error response");
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        console.log("Data fetched successfully");
        populateGradesTable(data.data);
      } else {
        console.error("Error fetching grades:", data.data);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

function populateGradesTable(grades) {
  const tableBody = document
    .getElementById("gradesTable")
    .getElementsByTagName("tbody")[0];
  tableBody.innerHTML = ""; // Clear the table first

  grades.forEach((grade) => {
    let row = tableBody.insertRow();
    row.insertCell(0).innerText = grade.grade_name;
    row.insertCell(1).innerText = grade.grade_type;
    row.insertCell(2).innerText = grade.grade_score || "N/A";
    row.insertCell(3).innerText = grade.grade_status;
    row.insertCell(4).innerText = grade.grade_deadline || "N/A";
    row.insertCell(5).innerText = grade.grade_feedback || "None";
    row.insertCell(6).innerText = grade.last_modified || "Unknown";  
  });
}
