let selectedCandidates = {};
let currentCommitteeIndex = 0;
const totalCommittees = 3; // Update this number based on your committees

function nextCommittee(currentIndex) {
    // Hide the current committee
    var currentCommittee = document.getElementById("committee" + currentIndex);
    currentCommittee.style.display = "none";
    
    // Show the next committee
    var nextCommittee = document.getElementById("committee" + (currentIndex + 1));
    if (nextCommittee) {
        nextCommittee.style.display = "block";
        currentCommitteeIndex = currentIndex + 1;
    }

    // Check if it's the last committee
    if (currentIndex === totalCommittees - 1) {
        // Directly skip to the review page
        submitVote();
    }
}

function previousCommittee(currentIndex) {
    // Hide the current committee
    var currentCommittee = document.getElementById("committee" + currentIndex);
    currentCommittee.style.display = "none";
    
    // Show the previous committee
    var previousCommittee = document.getElementById("committee" + (currentIndex - 1));
    if (previousCommittee) {
        previousCommittee.style.display = "block";
        currentCommitteeIndex = currentIndex - 1;
    }
    
    // Hide the "Submit Vote" button if going back before the last committee
    document.getElementById("submit-vote-container").style.display = "none";
}

function submitVote() {
    // Gather selected candidates
    const committees = ['President', 'Vice President', 'Secretary'];
    
    committees.forEach(function(committeeName) {
        var selectedCandidate = document.querySelector('input[name="' + committeeName + '"]:checked');
        if (selectedCandidate) {
            selectedCandidates[committeeName] = selectedCandidate.value;
        }
    });
    
    // Display the voting summary before submitting
    var summaryContainer = document.getElementById("summary-container");
    summaryContainer.innerHTML = ''; // Clear any previous summaries
    
    for (let committeeName in selectedCandidates) {
        var candidateName = selectedCandidates[committeeName];
        var committeeSummary = document.createElement("p");
        committeeSummary.innerHTML = "<strong>" + committeeName + ":</strong> " + candidateName;
        summaryContainer.appendChild(committeeSummary);
    }
    
    // Hide the voting section and show the submit summary
    document.querySelector(".voting-container").style.display = "none";
    document.getElementById("submit-summary").style.display = "block";
}

function finalizeVote() {
    // Here you could add AJAX call to submit data to server
    console.log('Final vote data:', selectedCandidates);
    
    // Hide everything and show the thank you page
    document.getElementById("submit-summary").style.display = "none";
    document.getElementById("thank-you").style.display = "block";
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

// Initialize button visibility on page load
document.addEventListener('DOMContentLoaded', function() {
    updateButtonVisibility();
});