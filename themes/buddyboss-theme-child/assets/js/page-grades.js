console.log("Script initialized");
document.addEventListener("DOMContentLoaded", init);

function init() {
  getStudentGrades();
}

function getStudentGrades() {
  console.log("Fetching grades for user ID:", userInfo.userId);
  const startTime = performance.now(); // Start timing
  console.log(userInfo.ajaxUrl);
  
  fetch(userInfo.ajaxUrl, {
    method: "POST",
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=fetch_client_grades&user_id=${userInfo.userId}`,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Server returned an error response");
      }
      return response.json();
    })
    .then((data) => {
      const endTime = performance.now(); // End timing
      console.log(`AJAX request took ${endTime - startTime}ms`);

      if (data.success) {
        console.log("Data fetched successfully");
        console.log(data.data);
        
        requestAnimationFrame(() => populateGradesTableNew(data.data));
        
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
    const nameWithoutLastWord = grade.grade_name.split(" ").slice(0, -1).join(" ");

    row.insertCell(0).innerText = grade.grade_name;
    row.insertCell(1).innerText = grade.grade_type;
    row.insertCell(2).innerText = grade.grade_score || "N/A";
    row.insertCell(3).innerText = grade.grade_status;
    row.insertCell(4).innerText = grade.grade_deadline || "N/A";
    row.insertCell(5).innerText = grade.grade_feedback || "None";
    row.insertCell(6).innerText = grade.last_modified || "Unknown";
  });
}
const populateGradesTableNew = (grades) => {
  const table = document.getElementById("gradesTable");
  table.innerHTML = ''; // Clear the table content
  const thead = document.createElement('thead');
  const headerRow = document.createElement("tr");

  const headers = [
    "שם",
    "סוג",
    "ציון",
    "סטטוס",
    "מועד הגשה",
    "משוב",
    "עדכון אחרון"
  ];
  headers.forEach((header) => {
    const th = document.createElement("th");
    th.innerText = header;
    headerRow.appendChild(th);
  });
  thead.appendChild(headerRow);
  table.appendChild(thead);

  const tbody = document.createElement("tbody");
  const total = grades.length;
  let counter = 0;
  const gradesInfo = document.getElementById("completed-text");
   
  grades.forEach((grade) => {
    if(grade.grade_score) counter++;
    const row = document.createElement("tr");
    const nameWithoutLastWord = grade.grade_name.split(" ").slice(0, -1).join(" ");

    // Define the row data
    const rowData = [
      // {
      //   icon: "link-solid", // Replace with dynamic icon if needed
      //   text: grade.grade_name,
      // },
      nameWithoutLastWord,
      grade.grade_type,
      grade.grade_score || "N/A",
      grade.grade_status === "Not Submitted" ? "מחכה להגשה" : grade.grade_status,
      grade.grade_deadline || "א",
      grade.grade_feedback || "אין",
      grade.last_modified || "לא ידוע"
    ];
    
    rowData.forEach((data) => {
      const cell = document.createElement("td");
      cell.innerText = data;

      row.appendChild(cell);
    });


    tbody.appendChild(row);
  });
  
  gradesInfo.innerHTML = `${counter}/${total}`;
  table.appendChild(tbody);
};

// Helper function for getting stylesheet directory URI
function getStylesheetDirectoryUri() {
  return userInfo.stylesheetDirectoryUri; // Use the localized PHP variable
}
