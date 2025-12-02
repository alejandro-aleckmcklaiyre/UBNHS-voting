// Add smooth animations and enhanced interactions
document.addEventListener('DOMContentLoaded', function() {
    const presidentItems = document.querySelectorAll('.president-item');
    
    presidentItems.forEach((item, index) => {
        // Add staggered entrance animation
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            item.style.transition = 'all 0.6s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 100);
        
        // Enhanced hover interactions
        item.addEventListener('mouseenter', function() {
            this.style.zIndex = '100';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });
});

// Function to add background image to circles
function setPresidentPhoto(index, imageUrl) {
    const circles = document.querySelectorAll('.president-circle');
    if (circles[index]) {
        circles[index].style.backgroundImage = `url('${imageUrl}')`;
        circles[index].querySelector('.placeholder-text').style.display = 'none';
    }
}

// Function to set background image for the entire page
function setPageBackground(imageUrl) {
    document.body.style.backgroundImage = `url('${imageUrl}')`;
    document.body.style.backgroundSize = 'cover';
    document.body.style.backgroundPosition = 'center';
    document.body.style.backgroundAttachment = 'fixed';
}

// Example usage (uncomment and modify when you have images):
// setPresidentPhoto(0, 'images/president1.jpg');
// setPresidentPhoto(1, 'images/president2.jpg');
// setPresidentPhoto(2, 'images/president3.jpg');
// ... and so on

// To set a background image for the page:
// setPageBackground('images/school-background.png');