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
    const inputs = row.querySelectorAll('input');

    // Collect input values
    const student_number = inputs[0].value;
    const first_name = inputs[1].value;
    const middle_name = inputs[2].value;
    const last_name = inputs[3].value;
    const suffix = inputs[4].value;
    const year_level = inputs[5].value;
    const section = inputs[6].value;
    const email = inputs[7].value;

    // You need to get class_group_id from year_level and section
    // For demo, let's send year_level and section as is
    // You should implement a lookup to get class_group_id from backend

    $.ajax({
        url: '/ubnhs-voting/php/voters/add_voters.php',
        type: 'POST',
        dataType: 'json',
        data: {
            student_number: student_number,
            first_name: first_name,
            middle_name: middle_name,
            last_name: last_name,
            suffix: suffix,
            email: email,
            year_level: year_level,
            section: section,
            status_id: 1 // Default status
        },
        success: function(response) {
            if (response.success) {
                // Convert inputs to text
                inputs.forEach(input => {
                    const td = input.parentElement;
                    td.textContent = input.value;
                });
                // Replace buttons with delete button
                const actionsCell = row.cells[9];
                const studentNumber = row.cells[1].textContent;
                actionsCell.innerHTML = '<button onclick="deleteStudent(\'' + studentNumber + '\')">Delete</button>';
                Swal.fire({
                    icon: 'success',
                    title: 'Student added successfully!',
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to add student.'
                });
            }
        },
        error: function(xhr) {
            let errorMsg = 'Failed to add student.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            console.error('Add Voter Error:', errorMsg, xhr.responseJSON);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg
            });
        }
    });
}

function removeRow(button) {
    const row = button.closest('tr');
    row.remove();
    
    // Update row numbers
    const table = document.getElementById('bsit1-1-table');
    const rows = table.getElementsByTagName('tbody')[0].rows;
    for (let i = 0; i < rows.length; i++) {
        rows[i].cells[0].textContent = i + 1;
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
            // Find and remove the row
            const table = document.getElementById('bsit1-1-table');
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
            const csv = e.target.result;
            const lines = csv.split('\n');
            const table = document.getElementById('bsit1-1-table').getElementsByTagName('tbody')[0];
            
            // Clear existing data (optional)
            // table.innerHTML = '';
            
            // Skip header row if present
            const startIndex = 1;
            
            for (let i = startIndex; i < lines.length; i++) {
                const line = lines[i].trim();
                if (line) {
                    const columns = line.split(',');
                    if (columns.length >= 4) {
                        const row = table.insertRow();
                        row.insertCell(0).textContent = table.rows.length;
                        row.insertCell(1).textContent = columns[0].trim();
                        row.insertCell(2).textContent = columns[1].trim();
                        row.insertCell(3).textContent = columns[2].trim();
                        row.insertCell(4).textContent = columns[3].trim();
                        row.insertCell(5).textContent = columns[4].trim();
                        row.insertCell(6).textContent = columns[5].trim();
                        row.insertCell(7).textContent = columns[6].trim();
                        row.insertCell(8).innerHTML = '<button onclick="deleteStudent(\'' + columns[0].trim() + '\')">Delete</button>';
                    }
                }
            }
            
            Swal.fire({
                icon: 'success',
                title: 'CSV uploaded successfully!',
                showConfirmButton: false,
                timer: 2000
            });
            
            // Reset form
            fileInput.value = '';
        };
        
        reader.readAsText(file);
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Please select a valid CSV file',
            showConfirmButton: false,
            timer: 2000
        });
    }
}

document.getElementById("generateQrBtn").addEventListener("click", function() {
    // Simulate QR generation
    Swal.fire({
        icon: 'info',
        title: 'Generating QR codes...',
        showConfirmButton: false,
        timer: 2000
    }).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'QR codes generated successfully!',
            showConfirmButton: false,
            timer: 2000
        });
    });
});

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Voters page loaded successfully');
});