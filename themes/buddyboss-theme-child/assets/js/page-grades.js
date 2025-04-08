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
        
        requestAnimationFrame(() => populateGradesTable2(data.data));
        
      } else {
        console.error("Error fetching grades:", data.data);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}
const populateGradesTable2 = (grades) => {
  const table = document.getElementById("gradesTable");
  table.innerHTML = ''; // Clear the table content
  const gradesInfo = document.getElementById("completed-text");

  // Define headers once and use keys for row data mapping
  const headers = [
    { key: "grade_name", label: "שם", transform: (val) => val.split(" ").slice(0, -1).join(" ") || val },
    { key: "grade_type", label: "סוג" },
    { key: "grade_score", label: "ציון", default: "N/A" },
    { key: "grade_status", label: "סטטוס", transform: (val) => (val === "Not Submitted" ? "מחכה להגשה" : val) },
    { key: "grade_deadline", label: "מועד הגשה", default: "א" },
    { key: "grade_feedback", label: "משוב", default: "אין" },
    { key: "last_modified", label: "עדכון אחרון", default: "לא ידוע",  className: "modified_time" }
  ];

  // Create table head in one step
  const thead = document.createElement('thead');
  thead.innerHTML = `<tr>${headers.map(h => `<th>${h.label}</th>`).join('')}</tr>`;
  table.appendChild(thead);

  const tbody = document.createElement("tbody");
  let counter = 0;

  grades.forEach((grade) => {
    if (grade.grade_score) counter++;
    const row = document.createElement("tr");

    headers.forEach(({ key, label, default: defaultValue, transform, className }) => {
      const cell = document.createElement("td");
      let value = grade[key] ?? defaultValue; // Use default if missing
      if (transform) value = transform(value); // Apply transformation if needed
      if (className) cell.classList.add(className);
        
      cell.innerText = value;
      cell.setAttribute("data-colname", label); // Add column name for mobile view
      

      row.appendChild(cell);
    });

    tbody.appendChild(row);
  });

  gradesInfo.innerHTML = `${counter}/${grades.length}`;
  table.appendChild(tbody);
};

const populateGradesTable = (grades) => {
  const table = document.getElementById("gradesTable");
  table.innerHTML = ''; // Clear the table content
  const thead = document.createElement('thead');
  const headerRow = document.createElement("tr");

  const headers = [
    { key: "grade_name", label: "שם" },
    { key: "grade_type", label: "סוג" },
    { key: "grade_score", label: "ציון" },
    { key: "grade_status", label: "סטטוס" },
    { key: "grade_deadline", label: "מועד הגשה" },
    { key: "grade_feedback", label: "משוב" },
    { key: "last_modified", label: "עדכון אחרון" }
  ];
  headers.forEach((header) => {
    const th = document.createElement("th");
    th.innerText = header.label;
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

    const rowData = [
      { value: nameWithoutLastWord, label: "שם" },
      { value: grade.grade_type, label: "סוג" },
      { value: grade.grade_score || "N/A", label: "ציון" },
      { value: grade.grade_status === "Not Submitted" ? "מחכה להגשה" : grade.grade_status, label: "סטטוס" },
      { value: grade.grade_deadline || "א", label: "מועד הגשה" },
      { value: grade.grade_feedback || "אין", label: "משוב" },
      { value: grade.last_modified || "לא ידוע", label: "עדכון אחרון" }
    ];
    
    rowData.forEach((data) => {
      const cell = document.createElement("td");
      cell.innerText = data.value;
      cell.setAttribute("data-colname", data.label); // Add column name for mobile view

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
