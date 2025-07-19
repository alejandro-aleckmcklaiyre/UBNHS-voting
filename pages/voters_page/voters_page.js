function openNav() {
  document.getElementById("mySidebar").style.width = "250px";
  document.getElementById("main").style.marginLeft = "250px";
  document.getElementById("main").style.width = "calc(100% - 250px)";
}

function closeNav() {
  document.getElementById("mySidebar").style.width = "0";
  document.getElementById("main").style.marginLeft = "0";
  document.getElementById("main").style.width = "100%";
}

document.getElementById('voters_p').classList.add('active');

function addRow(tableId) {
    const table = document.getElementById(tableId).getElementsByTagName('tbody')[0];
    const rowCount = table.rows.length;

    // Create a new row and cells
    const row = table.insertRow();
    const indexCell = row.insertCell(0);
    const studentNumberCell = row.insertCell(1);
    const firstNameCell = row.insertCell(2);
    const middleNameCell = row.insertCell(3);
    const lastNameCell = row.insertCell(4);
    const suffixCell = row.insertCell(5);
    const yearLevelCell = row.insertCell(6);
    const sectionCell = row.insertCell(7);
    const emailCell = row.insertCell(8);
    const actionsCell = row.insertCell(9);

    // Add index
    indexCell.textContent = rowCount + 1;

    // Add input fields for student number, name, and email
    studentNumberCell.innerHTML = '<input type="text" placeholder="Student Number">';
    firstNameCell.innerHTML = '<input type="text" placeholder=" First Name">';
    middleNameCell.innerHTML = '<input type="text" placeholder=" Middle Name">';
    lastNameCell.innerHTML = '<input type="text" placeholder=" Last Name">'; 
    suffixCell.innerHTML = '<input type="text" placeholder=" Suffix">'; 
    yearLevelCell.innerHTML = '<input type="text" placeholder="Year Level">';  
    sectionCell.innerHTML = '<input type="text" placeholder="Section">';
    emailCell.innerHTML = '<input type="email" placeholder="Email Address">';

    // Add action buttons
    actionsCell.innerHTML = '<button onclick="confirmRow(this)">Confirm</button> <button onclick="removeRow(this)">Remove</button>';
}

function confirmRow(button) {
    const row = button.closest('tr');
    let student_number, first_name, middle_name, last_name, suffix, year_level, section, email;
    const inputs = row.querySelectorAll('input');
    
    // Show loading state
    button.disabled = true;
    button.textContent = 'Adding...';
    
    if (inputs.length > 0) {
        student_number = inputs[0].value.trim();
        first_name = inputs[1].value.trim();
        middle_name = inputs[2].value.trim();
        last_name = inputs[3].value.trim();
        suffix = inputs[4].value.trim();
        year_level = inputs[5].value.trim();
        section = inputs[6].value.trim();
        email = inputs[7].value.trim();
    } else {
        student_number = row.cells[1].textContent.trim();
        first_name = row.cells[2].textContent.trim();
        middle_name = row.cells[3].textContent.trim();
        last_name = row.cells[4].textContent.trim();
        suffix = row.cells[5].textContent.trim();
        year_level = row.cells[6].textContent.trim();
        section = row.cells[7].textContent.trim();
        email = row.cells[8].textContent.trim();
    }

    // Client-side validation before sending
    if (!student_number || !first_name || !last_name || !email || !year_level || !section) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Required Fields',
            text: 'Please fill in all required fields: Student Number, First Name, Last Name, Email, Year Level, and Section.'
        });
        // Reset button
        button.disabled = false;
        button.textContent = 'Confirm';
        return;
    }

    // Validate student number format (12 digits)
    if (!/^\d{12}$/.test(student_number)) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Student Number',
            text: 'Student number must be exactly 12 digits.'
        });
        // Reset button
        button.disabled = false;
        button.textContent = 'Confirm';
        return;
    }

    // Validate email format
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Email',
            text: 'Please enter a valid email address.'
        });
        // Reset button
        button.disabled = false;
        button.textContent = 'Confirm';
        return;
    }

    $.ajax({
        url: '/ubnhs-voting/php/voters/add_voters.php',
        type: 'POST',
        dataType: 'json',
        data: {
            student_number: student_number,
            first_name: first_name,
            middle_name: middle_name || '',
            last_name: last_name,
            suffix: suffix || '',
            email: email,
            year_level: year_level,
            section: section,
            status_id: 1 // Default status
        },
        success: function(response) {
            // Reset button first
            button.disabled = false;
            button.textContent = 'Confirm';
            
            if (response && response.success) {
                // Convert inputs to text content
                if (inputs.length > 0) {
                    inputs.forEach(input => {
                        const td = input.parentElement;
                        td.textContent = input.value || '';
                    });
                }
                
                // Update actions cell
                const actionsCell = row.cells[row.cells.length - 1];
                const studentNumber = row.cells[1].textContent;
                actionsCell.innerHTML = '<button onclick="deleteStudent(\'' + studentNumber + '\')">Delete</button>';
                
                // Show success message with QR status
                let successMessage = 'Student added successfully!';
                if (response.qr_generated === false) {
                    successMessage += '\n\nNote: QR code could not be generated at this time but can be created later.';
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: successMessage,
                    showConfirmButton: true,
                    timer: 3000
                });

                // Log additional info if available
                if (response.student_id) {
                    console.log('Student added with ID:', response.student_id);
                }
                if (response.unique_code) {
                    console.log('Unique code generated:', response.unique_code);
                }
                
                // Refresh the table to get latest data
                if (typeof loadVotersTable === 'function') {
                    setTimeout(() => loadVotersTable(), 1000);
                }
                
            } else {
                // Handle success=false case
                const errorMessage = response && response.message ? response.message : 'Failed to add student.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            }
        },
        error: function(xhr, status, error) {
            // Reset button
            button.disabled = false;
            button.textContent = 'Confirm';
            
            let errorMsg = 'Failed to add student.';
            let errorDetails = '';
            
            // Try to parse JSON response first
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                if (xhr.responseJSON.error) {
                    errorDetails = xhr.responseJSON.error;
                }
            } else if (xhr.responseText) {
                // Try to extract useful info from responseText
                try {
                    const parsed = JSON.parse(xhr.responseText);
                    if (parsed.message) {
                        errorMsg = parsed.message;
                    }
                } catch (e) {
                    // If not JSON, show HTTP status info
                    errorMsg = `Server error (${xhr.status}): ${xhr.statusText || 'Unknown error'}`;
                }
            } else {
                // Network or other error
                errorMsg = `Network error: ${error || 'Unable to connect to server'}`;
            }
            
            // Log detailed error for debugging
            console.error('Add Voter Error Details:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                responseJSON: xhr.responseJSON,
                error: error
            });
            
            Swal.fire({
                icon: 'error',
                title: 'Error Adding Student',
                text: errorMsg,
                footer: errorDetails ? `Details: ${errorDetails}` : ''
            });
        },
        timeout: 30000 // 30 second timeout
    });
}

function removeRow(button) {
    const row = button.closest('tr');
    row.remove();
    
    // Update row numbers
    const table = document.getElementById('bsit1-1-table');
    if (table) {
        const rows = table.getElementsByTagName('tbody')[0].rows;
        for (let i = 0; i < rows.length; i++) {
            rows[i].cells[0].textContent = i + 1;
        }
    }
}

function deleteStudent(studentNumber) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#A8201A',
        cancelButtonColor: '#143642',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // TODO: Add actual database deletion via AJAX
            // For now, just remove from table
            
            // Find and remove the row
            const table = document.getElementById('bsit1-1-table');
            if (table) {
                const rows = table.getElementsByTagName('tbody')[0].rows;
                
                for (let i = 0; i < rows.length; i++) {
                    if (rows[i].cells[1].textContent === studentNumber) {
                        rows[i].remove();
                        break;
                    }
                }
                
                // Update row numbers
                for (let i = 0; i < rows.length; i++) {
                    rows[i].cells[0].textContent = i + 1;
                }
            }
            
            Swal.fire(
                'Deleted!',
                'Student has been deleted.',
                'success'
            );
        }
    });
}

function submitForm(event) {
    event.preventDefault();
    
    const fileInput = document.getElementById('fileUpload-bsit1-1');
    const file = fileInput.files[0];
    
    if (file && file.name.endsWith('.csv')) {
        const reader = new FileReader();

        reader.onload = function(e) {
            try {
                const csv = e.target.result;
                const lines = csv.split('\n');
                const table = document.getElementById('bsit1-1-table');
                
                if (!table) {
                    throw new Error('Table not found');
                }
                
                const tbody = table.getElementsByTagName('tbody')[0];

                // Parse header
                const header = lines[0].split(',').map(h => h.trim());
                const startIndex = 1;
                let addedCount = 0;

                for (let i = startIndex; i < lines.length; i++) {
                    const line = lines[i].trim();
                    if (line) {
                        const columns = line.split(',').map(col => col.trim());
                        
                        // Check if we have the expected number of columns (8 for our CSV format)
                        // student_number,first_name,middle_name,last_name,suffix,email,year_level,section
                        if (columns.length === 8) {
                            const row = tbody.insertRow();
                            row.insertCell(0).textContent = tbody.rows.length;
                            for (let j = 0; j < columns.length; j++) {
                                row.insertCell(j + 1).textContent = columns[j];
                            }
                            row.insertCell(columns.length + 1).innerHTML = '<button onclick="confirmRow(this)">Confirm</button> <button onclick="removeRow(this)">Remove</button>';
                            addedCount++;
                        }
                        // Handle case where Excel might have added an extra index column
                        else if (columns.length === 9 && !isNaN(columns[0])) {
                            const row = tbody.insertRow();
                            row.insertCell(0).textContent = tbody.rows.length;
                            // Skip the first column (Excel index) and use the rest
                            for (let j = 1; j < columns.length; j++) {
                                row.insertCell(j).textContent = columns[j];
                            }
                            row.insertCell(columns.length).innerHTML = '<button onclick="confirmRow(this)">Confirm</button> <button onclick="removeRow(this)">Remove</button>';
                            addedCount++;
                        }
                        // Original logic for backwards compatibility
                        else if (columns.length === header.length) {
                            const row = tbody.insertRow();
                            row.insertCell(0).textContent = tbody.rows.length;
                            for (let j = 0; j < columns.length; j++) {
                                row.insertCell(j + 1).textContent = columns[j];
                            }
                            row.insertCell(header.length + 1).innerHTML = '<button onclick="confirmRow(this)">Confirm</button> <button onclick="removeRow(this)">Remove</button>';
                            addedCount++;
                        }
                        else if (columns.length === header.length + 1 && !isNaN(columns[0])) {
                            const row = tbody.insertRow();
                            row.insertCell(0).textContent = tbody.rows.length;
                            for (let j = 1; j < columns.length; j++) {
                                row.insertCell(j).textContent = columns[j];
                            }
                            row.insertCell(header.length + 1).innerHTML = '<button onclick="confirmRow(this)">Confirm</button> <button onclick="removeRow(this)">Remove</button>';
                            addedCount++;
                        }
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'CSV uploaded successfully!',
                    text: `Added ${addedCount} rows to the table.`,
                    showConfirmButton: true,
                    timer: 3000
                });

                // Reset form
                fileInput.value = '';
                
            } catch (error) {
                console.error('CSV parsing error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error processing CSV',
                    text: 'There was an error processing the CSV file. Please check the format and try again.'
                });
            }
        };

        reader.readAsText(file);
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Invalid File',
            text: 'Please select a valid CSV file.',
            showConfirmButton: true,
            timer: 2000
        });
    }
}

document.getElementById("generateQrBtn")?.addEventListener("click", function() {
    // Show loading state
    const btn = this;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Generating...';
    
    // TODO: Implement actual QR generation for all students
    // For now, simulate the process
    Swal.fire({
        icon: 'info',
        title: 'Generating QR codes...',
        text: 'This may take a few moments...',
        showConfirmButton: false,
        timer: 2000,
        allowOutsideClick: false
    }).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'QR codes generated successfully!',
            showConfirmButton: false,
            timer: 2000
        });
        
        // Reset button
        btn.disabled = false;
        btn.textContent = originalText;
    });
});

// Pagination and filtering variables
let currentPage = 1;
let studentsData = [];
let rowsPerPage = 25;
let filterYear = '';
let filterSection = '';

function renderTablePage() {
    const table = document.getElementById('bsit1-1-table');
    if (!table) return;
    
    const tableBody = table.getElementsByTagName('tbody')[0];
    tableBody.innerHTML = '';
    
    let filtered = studentsData;
    if (filterYear) filtered = filtered.filter(s => s.year_level == filterYear);
    if (filterSection) filtered = filtered.filter(s => s.section == filterSection);
    
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    
    filtered.slice(start, end).forEach((student, idx) => {
        const row = tableBody.insertRow();
        row.insertCell(0).textContent = start + idx + 1;
        row.insertCell(1).textContent = student.student_number || '';
        row.insertCell(2).textContent = student.first_name || '';
        row.insertCell(3).textContent = student.middle_name || '';
        row.insertCell(4).textContent = student.last_name || '';
        row.insertCell(5).textContent = student.suffix || '';
        row.insertCell(6).textContent = student.year_level || '';
        row.insertCell(7).textContent = student.section || '';
        row.insertCell(8).textContent = student.email || '';
        row.insertCell(9).innerHTML = '<button onclick="deleteStudent(\'' + (student.student_number || '') + '\')">Delete</button>';
    });
    
    renderPagination(filtered.length);
}

function renderPagination(totalRows) {
    const paginationDiv = document.getElementById('pagination-controls');
    if (!paginationDiv) return;
    
    paginationDiv.innerHTML = '';
    const totalPages = Math.ceil(totalRows / rowsPerPage);
    
    if (totalPages <= 1) return; // Don't show pagination for single page
    
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = (i === currentPage) ? 'active' : '';
        btn.onclick = function() {
            currentPage = i;
            renderTablePage();
        };
        paginationDiv.appendChild(btn);
    }
}

function loadVotersTable() {
    fetch('/ubnhs-voting/php/voters/display_voter.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success && Array.isArray(data.students)) {
                studentsData = data.students;
                populateFilterOptions();
                currentPage = 1;
                renderTablePage();
                console.log('Loaded', studentsData.length, 'students');
            } else {
                console.warn('No students data received or invalid format');
                studentsData = [];
                renderTablePage();
            }
        })
        .catch(error => {
            console.error('Error loading voters:', error);
            Swal.fire({
                icon: 'warning',
                title: 'Loading Error',
                text: 'Could not load students data. Please refresh the page.',
                showConfirmButton: true
            });
        });
}

function populateFilterOptions() {
    const yearSet = new Set();
    const sectionSet = new Set();
    
    studentsData.forEach(s => {
        if (s.year_level) yearSet.add(s.year_level);
        if (s.section) sectionSet.add(s.section);
    });
    
    const yearSelect = document.getElementById('filter-year');
    const sectionSelect = document.getElementById('filter-section');
    
    if (yearSelect) {
        yearSelect.innerHTML = '<option value="">All Years</option>';
        Array.from(yearSet).sort().forEach(y => {
            yearSelect.innerHTML += `<option value="${y}">${y}</option>`;
        });
    }
    
    if (sectionSelect) {
        sectionSelect.innerHTML = '<option value="">All Sections</option>';
        Array.from(sectionSet).sort().forEach(s => {
            sectionSelect.innerHTML += `<option value="${s}">${s}</option>`;
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    try {
        loadVotersTable();
        
        // Add event listeners with error handling
        const filterYear = document.getElementById('filter-year');
        const filterSection = document.getElementById('filter-section');
        
        if (filterYear) {
            filterYear.addEventListener('change', function() {
                filterYear = this.value;
                currentPage = 1;
                renderTablePage();
            });
        }
        
        if (filterSection) {
            filterSection.addEventListener('change', function() {
                filterSection = this.value;
                currentPage = 1;
                renderTablePage();
            });
        }
        
        console.log('Voters page loaded successfully');
    } catch (error) {
        console.error('Error initializing voters page:', error);
    }
});