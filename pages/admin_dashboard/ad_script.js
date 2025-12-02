// Updated JavaScript for Top Navigation Bar Admin Dashboard

// ===== NAVIGATION FUNCTIONS =====
// Mobile menu toggle function (for top navigation)
function toggleMobileMenu() {
  const mobileMenu = document.getElementById('mobileNavMenu');
  if (mobileMenu) {
    mobileMenu.classList.toggle('active');
  }
}

// Legacy sidebar functions - Updated to work with both old and new navigation
function openNav() {
  const sidebar = document.getElementById("mySidebar");
  const main = document.getElementById("main");
  const openBtn = document.getElementById("openSidebarBtn");
  
  if (sidebar) sidebar.style.width = "250px";
  if (main) main.style.marginLeft = "250px";
  if (openBtn) openBtn.style.display = "none";
}

function closeNav() {
  const sidebar = document.getElementById("mySidebar");
  const main = document.getElementById("main");
  const openBtn = document.getElementById("openSidebarBtn");
  
  if (sidebar) sidebar.style.width = "0";
  if (main) main.style.marginLeft = "0";
  if (openBtn) openBtn.style.display = "inline-block";
}

// ===== MAIN APPLICATION LOGIC =====
document.addEventListener('DOMContentLoaded', function() {
  // Navigation setup
  setupNavigation();
  
  // Dashboard functionality
  let votingData = {};
  let chartInstance = null;

  // ===== NAVIGATION SETUP =====
  function setupNavigation() {
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
      const mobileMenu = document.getElementById('mobileNavMenu');
      const mobileBtn = document.querySelector('.mobile-menu-btn');
      
      if (mobileMenu && mobileBtn && 
          !mobileMenu.contains(event.target) && 
          !mobileBtn.contains(event.target)) {
        mobileMenu.classList.remove('active');
      }
    });

    // Close mobile menu when window is resized to desktop
    window.addEventListener('resize', function() {
      if (window.innerWidth > 768) {
        const mobileMenu = document.getElementById('mobileNavMenu');
        if (mobileMenu) {
          mobileMenu.classList.remove('active');
        }
      }
    });

    // Set active navigation item based on current page
    setActiveNavItem();
  }

  function setActiveNavItem() {
    const currentPage = window.location.search;
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
      item.classList.remove('active');
      const href = item.getAttribute('href');
      if (href && (href.includes(currentPage) || 
          (currentPage === '' && href.includes('admin_dashboard')))) {
        item.classList.add('active');
      }
    });

    // Also handle legacy sidebar navigation
    const homeLink = document.getElementById('home');
    if (homeLink) {
      homeLink.classList.add('active');
    }
  }

  // ===== DASHBOARD DATA FUNCTIONS =====
  function fetchResultsAndRender(selectedCommittee = null) {
    fetch('php/voting/results.php')
      .then(res => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then(data => {
        if (data && data.success) {
          votingData = data.results || {};
          updateStats({
            totalVotes: data.totalVotes || 0,
            turnoutPercent: data.turnoutPercent || 0,
            totalCandidates: data.totalCandidates || 0,
            status: data.status || 'Unknown'
          });
          renderLeadingCandidatesList(votingData);
          renderCommitteeOptions(votingData, selectedCommittee);
          const select = document.getElementById('committeeSelect');
          const committee = select ? (select.value || Object.keys(votingData)[0]) : null;
          if (committee) {
            renderCommitteeRankings(votingData, committee);
            updateChart(votingData, committee);
          }
        } else {
          console.error('API returned error:', data);
        }
      })
      .catch(error => {
        console.error('Error fetching results:', error);
        // Set default values on error
        updateStats({
          totalVotes: 0,
          turnoutPercent: 0,
          totalCandidates: 0,
          status: 'Offline'
        });
      });
  }

  function updateStats(data) {
    const elements = {
      totalVotes: document.getElementById('totalVotes'),
      turnoutPercent: document.getElementById('turnoutPercent'),
      totalCandidates: document.getElementById('totalCandidates'),
      electionStatus: document.getElementById('electionStatus')
    };

    if (elements.totalVotes) elements.totalVotes.textContent = data.totalVotes;
    if (elements.turnoutPercent) elements.turnoutPercent.textContent = data.turnoutPercent + '%';
    if (elements.totalCandidates) elements.totalCandidates.textContent = data.totalCandidates;
    if (elements.electionStatus) elements.electionStatus.textContent = data.status;
  }

  function renderLeadingCandidatesList(data) {
    const container = document.getElementById('leadingCandidatesList');
    if (!container) return;
    
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
      if (data[committee] && Array.isArray(data[committee]) && data[committee].length > 0) {
        const leader = data[committee].reduce((a, b) => ((a.votes || 0) > (b.votes || 0) ? a : b));
        container.innerHTML += `
          <div class="leading-candidate-card">
            <div class="candidate-info">
              <div class="candidate-name">${leader.name || 'Unknown'}</div>
              <div class="candidate-position">${committee}</div>
            </div>
            <span class="votes">${leader.votes || 0} votes</span>
          </div>
        `;
      }
    });
  }

  function renderCommitteeOptions(data, selectedCommittee) {
    const select = document.getElementById('committeeSelect');
    if (!select) return;
    
    select.innerHTML = '';
    // Always add "All Committees" option
    select.innerHTML += `<option value="all">All Committees</option>`;
    
    Object.keys(data).forEach(committee => {
      select.innerHTML += `<option value="${committee}">${committee}</option>`;
    });
    
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
    
    const sorted = [...data[committee]].sort((a, b) => (b.votes || 0) - (a.votes || 0));
    let html = `<h3>${committee} Rankings</h3><ol>`;
    sorted.forEach(cand => {
      html += `<li><b>${cand.name || 'Unknown'}</b> - ${cand.votes || 0} votes</li>`;
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
    let leadingNames = [];

    try {
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
          if (data[committee] && Array.isArray(data[committee]) && data[committee].length > 0) {
            const leader = data[committee].reduce((a, b) => ((a.votes || 0) > (b.votes || 0) ? a : b));
            labels.push(committee);
            votes.push(leader.votes || 0);
            colors.push('#A8201A');
            leadingNames.push(leader.name || 'Unknown');
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
          labels = candidates.map(c => c.name || 'Unknown');
          votes = candidates.map(c => c.votes || 0);
          colors = candidates.map((_, i) =>
            ['#A8201A', '#143642', '#FBD302', '#7DA2BB', '#64C864', '#C864C8'][i % 6]
          );
          chartTitle = `${committeeKey} Candidates`;
        } else {
          Object.keys(data).forEach(committee => {
            if (Array.isArray(data[committee]) && data[committee].length > 0) {
              const leader = data[committee].reduce((a, b) => ((a.votes || 0) > (b.votes || 0) ? a : b));
              labels.push(leader.name || 'Unknown');
              votes.push(leader.votes || 0);
              colors.push('#A8201A');
            }
          });
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
        
        // Update tooltip callback
        chartInstance.options.plugins.tooltip.callbacks.label = function(context) {
          if (
            (!selectedCommittee || selectedCommittee === 'all') &&
            (!selectedYearLevel || selectedYearLevel === 'all')
          ) {
            return `${leadingNames[context.dataIndex] || context.label}: ${context.parsed.y} votes`;
          } else {
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
                      return `${leadingNames[context.dataIndex] || context.label}: ${context.parsed.y} votes`;
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
    } catch (error) {
      console.error('Error updating chart:', error);
    }
  }

  function renderYearLevelOptions(yearLevelVotes, selectedYearLevel) {
    const select = document.getElementById('yearLevelSelect');
    if (!select) return;
    
    select.innerHTML = '';
    select.innerHTML += `<option value="all">All Year Level</option>`;
    
    if (yearLevelVotes && typeof yearLevelVotes === 'object') {
      Object.keys(yearLevelVotes).forEach(year => {
        select.innerHTML += `<option value="${year}">${year}</option>`;
      });
    }
    
    if (selectedYearLevel) {
      select.value = selectedYearLevel;
    }
  }

  // ===== EVENT LISTENERS =====
  function setupEventListeners() {
    // Committee select dropdown
    const committeeSelect = document.getElementById('committeeSelect');
    if (committeeSelect) {
      committeeSelect.addEventListener('change', function() {
        const selectedCommittee = this.value;
        const selectedYearLevel = document.getElementById('yearLevelSelect')?.value;
        renderCommitteeRankings(votingData, selectedCommittee);
        updateChart(votingData, selectedCommittee, selectedYearLevel);
      });
    }

    // Year level select dropdown
    const yearLevelSelect = document.getElementById('yearLevelSelect');
    if (yearLevelSelect) {
      yearLevelSelect.addEventListener('change', function() {
        const selectedCommittee = document.getElementById('committeeSelect')?.value;
        const selectedYearLevel = this.value;
        updateChart(votingData, selectedCommittee, selectedYearLevel);
      });
    }

    // Advanced Analytics modal
    setupAnalyticsModal();
  }

  function setupAnalyticsModal() {
    const analyticsButtons = document.querySelectorAll('.quick-action-btn');
    analyticsButtons.forEach(btn => {
      if (btn.textContent.includes('Advance Analytics')) {
        btn.addEventListener('click', function() {
          const modal = document.getElementById('analyticsModal');
          if (modal) {
            modal.classList.add('active');
            loadAnalyticsData();
          }
        });
      }
    });

    // Close modal handlers
    const closeBtn = document.getElementById('closeAnalyticsModal');
    if (closeBtn) {
      closeBtn.addEventListener('click', function() {
        const modal = document.getElementById('analyticsModal');
        if (modal) {
          modal.classList.remove('active');
        }
      });
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
      const modal = document.getElementById('analyticsModal');
      if (event.target === modal) {
        modal.classList.remove('active');
      }
    });
  }

  function loadAnalyticsData() {
    fetch('php/voting/results.php')
      .then(res => res.json())
      .then(data => {
        if (data && data.success) {
          updateAnalyticsCharts(data);
        }
      })
      .catch(error => {
        console.error('Error loading analytics data:', error);
      });
  }

  function updateAnalyticsCharts(data) {
    try {
      // Update turnout rate by grade level
      updateTurnoutRates(data);
      
      // Update pie chart
      updatePieChart(data);
      
      // Update line chart
      updateLineChart(data);
    } catch (error) {
      console.error('Error updating analytics charts:', error);
    }
  }

  function updateTurnoutRates(data) {
    const turnoutContainer = document.querySelector('#analyticsModal .turnout-bars');
    if (turnoutContainer) {
      turnoutContainer.innerHTML = '';
      const yearLevels = ['12', '11', '10', '9'];
      yearLevels.forEach(yl => {
        const percent = data.yearLevelVotes && data.yearLevelVotes[yl] && data.totalStudents
          ? Math.round((data.yearLevelVotes[yl] / data.totalStudents) * 100)
          : Math.floor(Math.random() * 30) + 70; // Fallback to demo data
        const colors = { '12': '#2ecc40', '11': '#3498db', '10': '#f39c12', '9': '#e74c3c' };
        turnoutContainer.innerHTML += `
          <div class="turnout-bar">
            Grade ${yl} <span>${percent}%</span>
            <div class="bar-container">
              <div class="bar" style="width:${percent}%; background:${colors[yl]};"></div>
            </div>
          </div>
        `;
      });
    }
  }

  function updatePieChart(data) {
    if (typeof Chart !== 'undefined') {
      const pieCanvas = document.getElementById('analyticsPieChart');
      if (pieCanvas) {
        const committees = Object.keys(data.results || {});
        const pieLabels = [];
        const pieData = [];
        const pieColors = ['#3b82f6', '#22c55e', '#f59e42', '#e74c3c', '#888', '#7da2bb', '#A8201A'];
        
        committees.forEach((committee) => {
          pieLabels.push(committee);
          const totalVotes = (data.results[committee] || []).reduce((sum, cand) => sum + (cand.votes || 0), 0);
          pieData.push(totalVotes);
        });

        if (window.analyticsPieChartInstance) {
          window.analyticsPieChartInstance.destroy();
        }
        
        window.analyticsPieChartInstance = new Chart(pieCanvas.getContext('2d'), {
          type: 'doughnut',
          data: {
            labels: pieLabels,
            datasets: [{
              data: pieData,
              backgroundColor: pieColors
            }]
          },
          options: { 
            plugins: { legend: { display: false } }, 
            cutout: '70%' 
          }
        });
      }
    }
  }

  function updateLineChart(data) {
    if (typeof Chart !== 'undefined') {
      const lineCanvas = document.getElementById('analyticsLineChart');
      if (lineCanvas) {
        const presidentCandidates = data.results['PRESIDENT'] || [];
        const topCandidates = presidentCandidates.slice(0, 3);
        const lineLabels = ['-2h', '-1.5h', '-1h', '-0.5h', 'Now'];
        const pieColors = ['#3b82f6', '#22c55e', '#f59e42', '#e74c3c', '#888'];
        
        const lineDatasets = topCandidates.map((cand, idx) => ({
          label: cand.name || 'Unknown',
          data: [
            Math.round((cand.votes || 0) * 0.6),
            Math.round((cand.votes || 0) * 0.7),
            Math.round((cand.votes || 0) * 0.8),
            Math.round((cand.votes || 0) * 0.9),
            cand.votes || 0
          ],
          borderColor: pieColors[idx % pieColors.length],
          fill: false
        }));

        if (window.analyticsLineChartInstance) {
          window.analyticsLineChartInstance.destroy();
        }
        
        window.analyticsLineChartInstance = new Chart(lineCanvas.getContext('2d'), {
          type: 'line',
          data: {
            labels: lineLabels,
            datasets: lineDatasets
          },
          options: { 
            plugins: { legend: { display: false } }, 
            scales: { x: { display: false }, y: { display: false } } 
          }
        });
      }
    }
  }

  // ===== INITIALIZATION =====
  // Initial fetch and render
  fetchResultsAndRender();
  
  // Setup all event listeners
  setupEventListeners();

  // Auto-refresh every 10 seconds
  setInterval(() => {
    const committeeSelect = document.getElementById('committeeSelect');
    const selectedCommittee = committeeSelect ? committeeSelect.value : null;
    fetchResultsAndRender(selectedCommittee);
  }, 10000);
});

// ===== GLOBAL FUNCTIONS (for compatibility) =====
// Make functions available globally for HTML onclick handlers
window.toggleMobileMenu = toggleMobileMenu;
window.openNav = openNav;
window.closeNav = closeNav;