let selectedCandidates = {};
let currentCommitteeIndex = 0;
const totalCommittees = 3; // Update this number based on your committees

function nextCommittee(currentIndex) {
    var currentCommittee = document.getElementById("committee" + currentIndex);
    currentCommittee.style.display = "none";

    if (currentIndex + 1 < window.totalCommittees) {
        var nextCommittee = document.getElementById("committee" + (currentIndex + 1));
        if (nextCommittee) {
            nextCommittee.style.display = "block";
            currentCommitteeIndex = currentIndex + 1;
        }
    } else {
        // Last committee, show review/submit
        submitVote();
    }
}

function previousCommittee(currentIndex) {
    var currentCommittee = document.getElementById("committee" + currentIndex);
    currentCommittee.style.display = "none";

    if (currentIndex - 1 >= 0) {
        var previousCommittee = document.getElementById("committee" + (currentIndex - 1));
        if (previousCommittee) {
            previousCommittee.style.display = "block";
            currentCommitteeIndex = currentIndex - 1;
        }
    }
    document.getElementById("submit-vote-container").style.display = "none";
}

function submitVote() {
    // Gather selected candidates
    let selected = {};
    // Dynamically get all committee names from the DOM
    document.querySelectorAll('.committee').forEach(committeeDiv => {
        const committeeName = committeeDiv.querySelector('h2').textContent;
        const selectedCandidate = committeeDiv.querySelector('input[type="radio"]:checked');
        if (selectedCandidate) {
            selected[committeeName] = selectedCandidate.value;
        }
    });
    selectedCandidates = selected;

    // Display the voting summary in a table
    var summaryContainer = document.getElementById("summary-container");
    summaryContainer.innerHTML = ''; // Clear any previous summaries

    // Build table
    const table = document.createElement('table');
    table.className = 'review-table';
    const thead = document.createElement('thead');
    thead.innerHTML = `
        <tr>
            <th>Committee</th>
            <th>Candidate Name</th>
            <th>Picture</th>
            <th>Actions</th>
        </tr>
    `;
    table.appendChild(thead);

    const tbody = document.createElement('tbody');

    // For each selected committee, find candidate info from DOM
    Object.keys(selectedCandidates).forEach((committeeName, idx) => {
        const candidateName = selectedCandidates[committeeName];
        // Find the candidate's image src from the original committee div
        let imgSrc = '';
        document.querySelectorAll('.committee').forEach(committeeDiv => {
            if (committeeDiv.querySelector('h2').textContent === committeeName) {
                committeeDiv.querySelectorAll('.candidate-box').forEach(box => {
                    const radio = box.querySelector('input[type="radio"]');
                    if (radio && radio.value === candidateName) {
                        imgSrc = box.querySelector('img').src;
                    }
                });
            }
        });

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${committeeName}</td>
            <td>${candidateName}</td>
            <td><img src="${imgSrc}" alt="${candidateName}" style="height:48px;max-width:64px;border-radius:6px;"></td>
            <td>
                <button type="button" class="change-btn" data-committee-index="${idx}">Change</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    table.appendChild(tbody);
    summaryContainer.appendChild(table);

    // Hide the voting section and show the submit summary
    document.querySelector(".voting-container").style.display = "none";
    document.getElementById("submit-summary").style.display = "block";

    // Add event listeners for change buttons
    document.querySelectorAll('.change-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Find the committee name from the row
            const row = this.closest('tr');
            const committeeName = row.children[0].textContent;
            // Find the committee index by searching for the committee div
            let targetIndex = -1;
            document.querySelectorAll('.committee').forEach((div, idx) => {
                if (div.querySelector('h2').textContent === committeeName) {
                    targetIndex = idx;
                }
            });
            // Hide summary, show voting, show the correct committee
            document.getElementById("submit-summary").style.display = "none";
            document.querySelector(".voting-container").style.display = "block";
            document.querySelectorAll('.committee').forEach((div, idx) => {
                div.style.display = (idx === targetIndex) ? 'block' : 'none';
            });
            currentCommitteeIndex = targetIndex;
            updateButtonVisibility();
        });
    });
}

function finalizeVote() {
    // Send selectedCandidates to the backend
    fetch('/ubnhs-voting/php/voting/submit_vote.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ votes: Object.values(selectedCandidates) })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Hide everything and show the thank you page
            document.getElementById("submit-summary").style.display = "none";
            document.getElementById("thank-you").style.display = "block";
        } else {
            alert('Failed to submit votes: ' + data.message);
        }
    })
    .catch(() => {
        alert('An error occurred while submitting your vote.');
    });
}

function goBackToVoting() {
    // Hide the current summary page
    document.getElementById("submit-summary").style.display = "none";
    
    // Show the voting page and reset to the committee where the user was last
    document.querySelector(".voting-container").style.display = "block";
    
    // Display the committee based on the currentCommitteeIndex
    var committee = document.getElementById("committee" + currentCommitteeIndex);
    if (committee) {
        committee.style.display = "block";
    }
    
    // Ensure the "Submit Vote" button is hidden
    document.getElementById("submit-vote-container").style.display = "none";
    
    // Update button visibility based on current index
    updateButtonVisibility();
}

function updateButtonVisibility() {
    for (let i = 0; i < totalCommittees; i++) {
        let committee = document.getElementById("committee" + i);
        if (committee) {
            let backButton = committee.querySelector(".buttons-container button:first-child");
            let nextButton = committee.querySelector(".buttons-container button:last-child");
            
            if (i === 0) {
                backButton.style.display = "none";
            } else {
                backButton.style.display = "inline-block";
            }
            
            nextButton.style.display = "inline-block";
        }
    }
}

// Add this at the top or after your variable declarations

document.addEventListener('DOMContentLoaded', function() {
    loadCandidatesFromDB();
    updateButtonVisibility();
});

function loadCandidatesFromDB() {
    fetch('/ubnhs-voting/php/candidate/display_candidate.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Group candidates by committee
                const committees = {};
                data.candidates.forEach(candidate => {
                    if (!committees[candidate.committee]) {
                        committees[candidate.committee] = [];
                    }
                    committees[candidate.committee].push(candidate);
                });

                // Remove existing committee sections
                document.querySelectorAll('.committee').forEach(el => el.remove());

                // Render committees dynamically
                const votingContainer = document.querySelector('.voting-container form');
                let idx = 0;
                Object.keys(committees).forEach(committeeName => {
                    const committeeDiv = document.createElement('div');
                    committeeDiv.id = 'committee' + idx;
                    committeeDiv.className = 'committee';
                    committeeDiv.style.display = idx === 0 ? 'block' : 'none';
                    committeeDiv.innerHTML = `
                        <h2>${committeeName}</h2>
                        <div class="candidates-row"></div>
                        <div class="buttons-container">
                            <button type="button" onclick="previousCommittee(${idx})" style="display: ${idx === 0 ? 'none' : 'inline-block'};">Back</button>
                            <button type="button" onclick="nextCommittee(${idx})">${idx === Object.keys(committees).length - 1 ? 'Review' : 'Next'}</button>
                        </div>
                    `;
                    // Add candidates
                    const candidatesRow = committeeDiv.querySelector('.candidates-row');
                    committees[committeeName].forEach(candidate => {
                        const candidateBox = document.createElement('div');
                        candidateBox.className = 'candidate-box';
                        candidateBox.innerHTML = `
                            <img src="/ubnhs-voting/${candidate.picture}" alt="${candidate.name}">
                            <label>
                                <input type="radio" name="${committeeName}" value="${candidate.name}"> ${candidate.name}
                            </label>
                        `;
                        candidatesRow.appendChild(candidateBox);
                    });
                    votingContainer.appendChild(committeeDiv);
                    idx++;
                });

                // Update totalCommittees and button visibility
                window.totalCommittees = Object.keys(committees).length;
                updateButtonVisibility();
            }
        });
}