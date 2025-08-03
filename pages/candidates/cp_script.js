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

document.getElementById('candi').classList.add('active');

document.getElementById('addCandidateForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const committee = document.getElementById('committee').value;
  const candidateName = document.getElementById('candidateName').value;
  const candidatePic = document.getElementById('candidatePic').files[0];

  const formData = new FormData();
  formData.append('committee', committee);
  formData.append('name', candidateName);
  formData.append('picture', candidatePic);

  fetch('php/candidate/add_candidate.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        Swal.fire('Success', data.message, 'success');
        document.getElementById('addCandidateForm').reset();
        loadCandidates(); // Refresh the table
      } else {
        Swal.fire('Error', data.message, 'error');
      }
    })
    .catch(() => {
      Swal.fire('Error', 'Failed to add candidate.', 'error');
    });
});

function loadCandidates() {
  fetch('php/candidate/display_candidate.php')
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const tableBody = document.querySelector('#candidateTable tbody');
        tableBody.innerHTML = '';
        data.candidates.forEach(candidate => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${candidate.committee}</td>
            <td>${candidate.name}</td>
            <td><img src="../../${candidate.picture}" alt="Candidate" class="candidate-pic"></td>
            <td><button class="delete-btn" data-id="${candidate.id}">Delete</button></td>
          `;
          tableBody.appendChild(row);
        });

        // Attach delete event listeners with SweetAlert confirmation
        document.querySelectorAll('.delete-btn').forEach(button => {
          button.addEventListener('click', function () {
            const candidateId = this.getAttribute('data-id');
            Swal.fire({
              title: 'Are you sure?',
              text: "This will remove the candidate from the list.",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#A8201A',
              cancelButtonColor: '#143642',
              confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
              if (result.isConfirmed) {
                fetch('php/candidate/delete_candidate.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: 'id=' + encodeURIComponent(candidateId)
                })
                  .then(res => res.json())
                  .then(data => {
                    if (data.success) {
                      Swal.fire('Removed!', 'Candidate has been removed.', 'success');
                      loadCandidates();
                    } else {
                      Swal.fire('Error', data.message, 'error');
                    }
                  })
                  .catch(() => {
                    Swal.fire('Error', 'Failed to delete candidate.', 'error');
                  });
              }
            });
          });
        });
      }
    });
}

document.addEventListener('DOMContentLoaded', loadCandidates);

document.getElementById('votingDurationForm').addEventListener('submit', function (e) {
  e.preventDefault();
  const votingDuration = document.getElementById('votingDuration').value;
  alert("Voting will end at: " + votingDuration);
});
