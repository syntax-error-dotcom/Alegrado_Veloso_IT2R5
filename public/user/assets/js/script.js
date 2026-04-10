// script.js - Interactivity for Discovery Page

document.addEventListener('DOMContentLoaded', function() {
    // Hover animation is handled by CSS, but we can add more JS interactions

    // Add click event to book cards for highlighting
    const bookCards = document.querySelectorAll('.book-card');
    bookCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove highlight from all cards
            bookCards.forEach(c => c.classList.remove('highlighted'));
            // Add highlight to clicked card
            this.classList.add('highlighted');
        });
    });

    // Search functionality (UI only)
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');

    searchBtn.addEventListener('click', function() {
        const query = searchInput.value.trim();
        if (query) {
            alert(`Searching for: ${query}`);
            // In a real app, this would send a request to the server
        } else {
            alert('Please enter a search term.');
        }
    });

    // Allow Enter key in search input
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchBtn.click();
        }
    });

    // See More buttons (placeholder functionality)
    const seeMoreBtns = document.querySelectorAll('.see-more-btn');
    seeMoreBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            alert('Loading more books...');
            // In a real app, this would load more content dynamically
        });
    });

    // Read buttons (placeholder)
    const readBtns = document.querySelectorAll('.read-btn');
    readBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const bookTitle = this.parentElement.querySelector('h3').textContent;
            alert(`Opening: ${bookTitle}`);
            // In a real app, this would open the book or redirect to a reading page
        });
    });
});