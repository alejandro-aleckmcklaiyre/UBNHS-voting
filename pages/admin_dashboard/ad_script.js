function openNav() {
  document.getElementById("mySidebar").style.width = "250px";
  document.getElementById("main").style.marginLeft = "250px";
  document.getElementById("openSidebarBtn").style.display = "none";
}

function closeNav() {
  document.getElementById("mySidebar").style.width = "0";
  document.getElementById("main").style.marginLeft = "0";
  document.getElementById("openSidebarBtn").style.display = "inline-block";
}

document.addEventListener('DOMContentLoaded', function() {
  let votingData = {};
  let chartInstance = null;

  function fetchResultsAndRender(selectedCommittee = null) {
    fetch('php/voting/results.php')
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          votingData = data.results;
          updateStats({
            totalVotes: data.totalVotes,
            turnoutPercent: data.turnoutPercent,
            totalCandidates: data.totalCandidates,
            status: data.status
          });
          renderLeadingCandidatesList(votingData);
          renderCommitteeOptions(votingData, selectedCommittee);
          const select = document.getElementById('committeeSelect');
          const committee = select.value || Object.keys(votingData)[0];
          renderCommitteeRankings(votingData, committee);
          updateChart(votingData, committee);
        }
      });
  }

  function updateStats(data) {
    // You need to calculate these values from your data/results
    document.getElementById('totalVotes').textContent = data.totalVotes;
    document.getElementById('turnoutPercent').textContent = data.turnoutPercent + '%';
    document.getElementById('totalCandidates').textContent = data.totalCandidates;
    document.getElementById('electionStatus').textContent = data.status;
  }

  function renderLeadingCandidatesList(data) {
    const container = document.getElementById('leadingCandidatesList');
    container.innerHTML = '';
    
    // Define the specific committees in the order you want them displayed
    const committeeOrder = [
      'PRESIDENT',
      'VICE PRESIDENT', 
      'SECRETARY',
      'TREASURER',
      'AUDITOR',
      'PIO',
      'PO',
      'GR 12 REPRESENTATIVE',
      'GR 11 REPRESENTATIVE',
      'GR 10 REPRESENTATIVE',
      'GR 9 REPRESENTATIVE',
      'GR 8 REPRESENTATIVE'
    ];
    
    // Loop through the committees in the specified order
    committeeOrder.forEach(committee => {
      if (data[committee] && data[committee].length > 0) {
        const leader = data[committee].reduce((a, b) => (a.votes > b.votes ? a : b));
        container.innerHTML += `
          <div class="leading-candidate-card">
            <div>
              <b>${leader.name}</b>
              <div class="candidate-committee">${committee}</div>
            </div>
            <span class="votes">${leader.votes} votes</span>
          </div>
        `;
      }
    });
  }

  function renderCommitteeOptions(data, selectedCommittee) {
    const select = document.getElementById('committeeSelect');
    select.innerHTML = '';
    // Always add "All Committees" option
    select.innerHTML += `<option value="all">All Committees</option>`;
    for (const committee in data) {
      select.innerHTML += `<option value="${committee}">${committee}</option>`;
    }
    // Set selected value if provided
    if (selectedCommittee && (selectedCommittee === "all" || data[selectedCommittee])) {
      select.value = selectedCommittee;
    }
  }

  function renderCommitteeRankings(data, committee) {
    const panel = document.getElementById('committee-rankings');
    if (!panel) return;
    if (!committee || !data[committee]) {
      panel.innerHTML = '';
      return;
    }
    const sorted = [...data[committee]].sort((a, b) => b.votes - a.votes);
    let html = `<h3>${committee} Rankings</h3><ol>`;
    sorted.forEach(cand => {
      html += `<li><b>${cand.name}</b> - ${cand.votes} votes</li>`;
    });
    html += '</ol>';
    panel.innerHTML = html;
  }

  function getCommitteeKey(selectedCommittee, data) {
    if (data[selectedCommittee]) return selectedCommittee;
    const found = Object.keys(data).find(
      key => key.toLowerCase() === selectedCommittee.toLowerCase()
    );
    return found || Object.keys(data)[0];
  }

  function updateChart(data, selectedCommittee, selectedYearLevel) {
    const canvas = document.getElementById('positionsChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let labels = [];
    let votes = [];
    let colors = [];
    let chartTitle = '';
    let leadingNames = []; // Store leading candidate names for tooltip

    // If "All Committees" and "All Year Level" are selected
    if (
      (!selectedCommittee || selectedCommittee === 'all') &&
      (!selectedYearLevel || selectedYearLevel === 'all')
    ) {
      const committeeOrder = [
        'PRESIDENT',
        'VICE PRESIDENT',
        'SECRETARY',
        'TREASURER',
        'AUDITOR',
        'PIO',
        'PO',
        'GR 12 REPRESENTATIVE',
        'GR 11 REPRESENTATIVE',
        'GR 10 REPRESENTATIVE',
        'GR 9 REPRESENTATIVE',
        'GR 8 REPRESENTATIVE'
      ];

      committeeOrder.forEach(committee => {
        if (data[committee] && data[committee].length > 0) {
          const leader = data[committee].reduce((a, b) => (a.votes > b.votes ? a : b));
          labels.push(committee);
          votes.push(leader.votes);
          colors.push('#A8201A');
          leadingNames.push(leader.name); // Save leading candidate name for tooltip
        }
      });

      chartTitle = 'Leading Candidates per Committee';
    } else {
      const committeeKey = selectedCommittee && selectedCommittee !== 'all'
        ? getCommitteeKey(selectedCommittee, data)
        : null;

      if (committeeKey && data[committeeKey]) {
        let candidates = data[committeeKey];
        if (selectedYearLevel && selectedYearLevel !== 'all') {
          candidates = candidates.filter(c => c.year_level === selectedYearLevel);
        }
        labels = candidates.map(c => c.name);
        votes = candidates.map(c => c.votes);
        colors = candidates.map((_, i) =>
          ['#A8201A', '#143642', '#FBD302', '#7DA2BB', '#64C864', '#C864C8'][i % 6]
        );
        chartTitle = `${committeeKey} Candidates`;
      } else {
        for (const committee in data) {
          const leader = data[committee].reduce((a, b) => (a.votes > b.votes ? a : b));
          labels.push(leader.name);
          votes.push(leader.votes);
          colors.push('#A8201A');
        }
        chartTitle = 'Leading Candidates';
      }
    }

    if (labels.length === 0) {
      labels = ['No Data'];
      votes = [0];
      colors = ['#ccc'];
      leadingNames = ['No Data'];
    }

    if (chartInstance) {
      chartInstance.data.labels = labels;
      chartInstance.data.datasets[0].data = votes;
      chartInstance.data.datasets[0].backgroundColor = colors;
      chartInstance.options.plugins.title.text = chartTitle;
      // Update tooltip callback for all committees/all year level
      chartInstance.options.plugins.tooltip.callbacks.label = function(context) {
        if (
          (!selectedCommittee || selectedCommittee === 'all') &&
          (!selectedYearLevel || selectedYearLevel === 'all')
        ) {
          // Show leading candidate name and votes
          return `${leadingNames[context.dataIndex]}: ${context.parsed.y} votes`;
        } else {
          // Default: show label and votes
          return `${context.label}: ${context.parsed.y} votes`;
        }
      };
      chartInstance.update();
    } else {
      chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Votes',
            data: votes,
            backgroundColor: colors,
            borderColor: colors,
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            title: {
              display: true,
              text: chartTitle,
              font: { size: 18 }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  if (
                    (!selectedCommittee || selectedCommittee === 'all') &&
                    (!selectedYearLevel || selectedYearLevel === 'all')
                  ) {
                    return `${leadingNames[context.dataIndex]}: ${context.parsed.y} votes`;
                  } else {
                    return `${context.label}: ${context.parsed.y} votes`;
                  }
                }
              }
            }
          },
          scales: {
            x: {
              beginAtZero: true,
              ticks: { color: '#2C3E50', font: { weight: 'bold' } },
              grid: { display: false }
            },
            y: {
              beginAtZero: true,
              ticks: {
                color: '#2C3E50',
                font: { weight: 'bold' },
                stepSize: 1,
                callback: function(value) { return Number.isInteger(value) ? value : null; }
              },
              grid: { color: 'rgba(0,0,0,0.1)' }
            }
          }
        }
      });
    }
  } 

  function renderYearLevelOptions(yearLevelVotes, selectedYearLevel) {
    const select = document.getElementById('yearLevelSelect');
    select.innerHTML = '';
    select.innerHTML += `<option value="all">All Year Level</option>`;
    // If you want dynamic year levels from backend:
    for (const year in yearLevelVotes) {
      select.innerHTML += `<option value="${year}">${year}</option>`;
    }
    // Or, if you want static options, you can skip the loop above
    if (selectedYearLevel) {
      select.value = selectedYearLevel;
    }
  }

  // Initial fetch and render
  fetchResultsAndRender();

  // Dropdown event handler (only set once)
  document.getElementById('committeeSelect').addEventListener('change', function() {
    const selectedCommittee = this.value;
    renderCommitteeRankings(votingData, selectedCommittee);
    updateChart(votingData, selectedCommittee);
  });

  // Add event listeners for both filters
  document.getElementById('committeeSelect').addEventListener('change', function() {
    const selectedCommittee = this.value;
    const selectedYearLevel = document.getElementById('yearLevelSelect').value;
    updateChart(votingData, selectedCommittee, selectedYearLevel);
  });

  document.getElementById('yearLevelSelect').addEventListener('change', function() {
    const selectedCommittee = document.getElementById('committeeSelect').value;
    const selectedYearLevel = this.value;
    updateChart(votingData, selectedCommittee, selectedYearLevel);
  });

  setInterval(() => {
    const selectedCommittee = document.getElementById('committeeSelect').value;
    fetchResultsAndRender(selectedCommittee);
  }, 10000);
});

document.getElementById('home').classList.add('active');

// Show modal when Advance Analytics button is clicked
document.querySelectorAll('.quick-action-btn').forEach(btn => {
  if (btn.textContent.includes('Advance Analytics')) {
    btn.addEventListener('click', function() {
      const modal = document.getElementById('analyticsModal');
      modal.classList.add('active');

      // Fetch real-time analytics data
      fetch('php/voting/results.php')
        .then(res => res.json())
        .then(data => {
          // --- Turnout Rate by Grade Level ---
          const turnoutContainer = document.querySelector('#analyticsModal .modal-content > div > div');
          if (turnoutContainer) {
            turnoutContainer.innerHTML = '';
            const yearLevels = ['12', '11', '10', '9'];
            yearLevels.forEach(yl => {
              const percent = data.yearLevelVotes[yl] && data.totalStudents
                ? Math.round((data.yearLevelVotes[yl] / data.totalStudents) * 100)
                : 0;
              const colors = { '12': '#2ecc40', '11': '#3498db', '10': '#f39c12', '9': '#e74c3c' };
              turnoutContainer.innerHTML += `
                <div style="margin-bottom:8px;">Grade ${yl} <span style="float:right;">${percent}%</span>
                  <div style="background:#e9ecef;border-radius:8px;height:8px;overflow:hidden;">
                    <div style="width:${percent}%;background:${colors[yl]};height:8px;"></div>
                  </div>
                </div>
              `;
            });
          }

          // --- Pie Chart: Vote Distribution by Position ---
          if (window.Chart) {
            // Prepare pie chart data
            const committees = Object.keys(data.results);
            const pieLabels = [];
            const pieData = [];
            const pieColors = ['#3b82f6', '#22c55e', '#f59e42', '#e74c3c', '#888', '#7da2bb', '#A8201A'];
            committees.forEach((committee, idx) => {
              pieLabels.push(committee);
              // Sum votes for this committee
              const totalVotes = data.results[committee].reduce((sum, cand) => sum + cand.votes, 0);
              pieData.push(totalVotes);
            });
            // Remove old chart if exists
            if (window.analyticsPieChartInstance) window.analyticsPieChartInstance.destroy();
            window.analyticsPieChartInstance = new Chart(document.getElementById('analyticsPieChart').getContext('2d'), {
              type: 'doughnut',
              data: {
                labels: pieLabels,
                datasets: [{
                  data: pieData,
                  backgroundColor: pieColors
                }]
              },
              options: { plugins: { legend: { display: false } }, cutout: '70%' }
            });
          }

          // --- Line Chart: Vote Trends (Last 2 Hours) ---
          if (window.Chart) {
            // Example: Use top 3 candidates from PRESIDENT committee
            const presidentCandidates = data.results['PRESIDENT'] || [];
            const topCandidates = presidentCandidates.slice(0, 3);
            const lineLabels = ['-2h', '-1.5h', '-1h', '-0.5h', 'Now'];
            // Fake trend data: spread current votes over time
            const lineDatasets = topCandidates.map((cand, idx) => ({
              label: cand.name,
              data: [
                Math.round(cand.votes * 0.6),
                Math.round(cand.votes * 0.7),
                Math.round(cand.votes * 0.8),
                Math.round(cand.votes * 0.9),
                cand.votes
              ],
              borderColor: pieColors[idx % pieColors.length],
              fill: false
            }));
            if (window.analyticsLineChartInstance) window.analyticsLineChartInstance.destroy();
            window.analyticsLineChartInstance = new Chart(document.getElementById('analyticsLineChart').getContext('2d'), {
              type: 'line',
              data: {
                labels: lineLabels,
                datasets: lineDatasets
              },
              options: { plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
            });
          }
        });
    });
  }
});

// Close modal on close button click
document.getElementById('closeAnalyticsModal').onclick = function() {
  document.getElementById('analyticsModal').classList.remove('active');
};

// Optional: close modal when clicking outside modal-content
window.onclick = function(event) {
  const modal = document.getElementById('analyticsModal');
  if (event.target === modal) modal.classList.remove('active');
};