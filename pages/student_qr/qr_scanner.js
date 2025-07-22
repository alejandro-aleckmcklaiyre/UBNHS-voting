const video = document.getElementById('scanner');
const placeholder = document.getElementById('placeholder');
const canvas = document.createElement('canvas');
const context = canvas.getContext('2d');
let cameraActive = false;
let scanning = false;

function startCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("Your browser does not support accessing the camera.");
        return;
    }
    navigator.mediaDevices.getUserMedia({
        video: {
            facingMode: 'environment',
            width: { ideal: 1280 },
            height: { ideal: 720 }
        }
    })
    .then(stream => {
        video.srcObject = stream;
        video.play();
        video.style.display = 'block';
        placeholder.style.display = 'none';
        cameraActive = true;
        document.getElementById('result').textContent = 'Camera started successfully. Position QR code in frame.';
        document.getElementById('startLiveScanBtn').textContent = 'Stop Camera';
    })
    .catch(err => {
        console.error("Error accessing webcam: " + err);
        alert("Error accessing webcam: " + err);
        document.getElementById('result').textContent = 'Error accessing camera. Please check permissions.';
    });
}

function stopCamera() {
    if (video.srcObject) {
        const tracks = video.srcObject.getTracks();
        tracks.forEach(track => track.stop());
        video.srcObject = null;
        video.style.display = 'none';
        placeholder.style.display = 'block';
        cameraActive = false;
        document.getElementById('result').textContent = 'Camera stopped.';
        document.getElementById('startLiveScanBtn').textContent = 'Start Live QR Scan';
    }
}

function scanLoop() {
    if (!cameraActive || !video.srcObject || !scanning) return;

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, canvas.width, canvas.height);

    if (code && code.data) {
        scanning = false;
        document.getElementById('result').textContent = 'QR code detected!';
        handleQRResult(code.data);
    } else {
        requestAnimationFrame(scanLoop); // much faster than setTimeout
    }
}

function showModal(message) {
    document.getElementById('modalMessage').textContent = message;
    document.getElementById('qrModal').style.display = 'block';
}

document.getElementById('closeModalBtn').onclick = function() {
    document.getElementById('qrModal').style.display = 'none';
};

// Optionally close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('qrModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
};

function handleQRResult(qrData) {
    fetch('/ubnhs-voting/php/auth/student_login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ qr_code_data: qrData })
    })
    .then(response => response.json())
    .then(loginData => {
        if (loginData.success) {
            document.getElementById('result').textContent = loginData.message;
            setTimeout(() => {
                window.location.href = loginData.redirect || 'index.php?page=voting_page';
            }, 1200);
        } else {
            document.getElementById('result').textContent = loginData.message;
            // Show popup for used QR code
            if (loginData.message === "Invalid or already used QR code.") {
                showModal("QR code is already used.");
            } else {
                showModal(loginData.message);
            }
        }
    })
    .catch(error => {
        document.getElementById('result').textContent = 'Login failed. Please try again.';
        alert('Login error: ' + error);
    });
}

document.getElementById('scanBtn').onclick = function () {
    if (!cameraActive || !video.srcObject) {
        alert("Please start the camera first.");
        return;
    }
    scanning = true;
    document.getElementById('result').textContent = 'Scanning for QR code...';
    scanLoop();
};

document.getElementById('startLiveScanBtn').onclick = function () {
    const button = this;
    if (!cameraActive) {
        button.classList.add('loading');
        button.textContent = 'Starting Camera...';
        setTimeout(() => {
            startCamera();
            button.classList.remove('loading');
        }, 500);
    } else {
        stopCamera();
    }
};

// Keyboard shortcuts
document.addEventListener('keydown', function(event) {
    if (event.code === 'Space') {
        event.preventDefault();
        if (cameraActive) {
            document.getElementById('scanBtn').click();
        }
    } else if (event.code === 'Enter') {
        event.preventDefault();
        document.getElementById('startLiveScanBtn').click();
    }
});

// Initialize placeholder visibility
placeholder.style.display = "block";

// Clean up camera when page is unloaded
window.addEventListener('beforeunload', function() {
    stopCamera();
});

// Handle visibility change (when user switches tabs)
document.addEventListener('visibilitychange', function() {
    if (document.hidden && cameraActive) {
        // Optionally pause camera when tab is hidden
        // stopCamera();
    }
});

document.getElementById('qrModal').style.display = 'none';