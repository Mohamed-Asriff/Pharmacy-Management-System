
// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    initializeDateTime();
    initializeSidebarToggle();
    initializeSearch();
    initializeFormValidations();
});

// Initialize Date and Time Display
function initializeDateTime() {
    updateDateTime();
    setInterval(updateDateTime, 1000);
}

function updateDateTime() {
    const now = new Date();
    
    // Update greeting
    const hour = now.getHours();
    const greeting = document.getElementById('greeting');
    if (greeting) {
        if (hour < 12) {
            greeting.textContent = 'Good Morning';
        } else if (hour < 18) {
            greeting.textContent = 'Good Afternoon';
        } else {
            greeting.textContent = 'Good Evening';
        }
    }
    
    // Update date and time
    const datetime = document.getElementById('currentDateTime');
    if (datetime) {
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        datetime.textContent = now.toLocaleDateString('en-US', options);
    }
}

// Sidebar Toggle for Mobile
function initializeSidebarToggle() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 1024) {
            if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
}

// Search Functionality
function initializeSearch() {
    const searchInput = document.getElementById('searchMedicines');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const table = document.getElementById('medicinesTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let row of rows) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            }
        });
    }
}

// Form Validations
function initializeFormValidations() {
    const medicineForm = document.getElementById('medicineForm');
    if (medicineForm) {
        medicineForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const price = document.getElementById('price').value;
            const quantity = document.getElementById('quantity').value;
            
            let errors = [];
            
            if (!name) {
                errors.push('Medicine name is required');
            }
            
            if (!price || parseFloat(price) <= 0) {
                errors.push('Valid selling price is required');
            }
            
            if (!quantity || parseInt(quantity) < 0) {
                errors.push('Valid quantity is required');
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                alert('Please fix the following errors:\n' + errors.join('\n'));
            }
        });
    }
}

// POS System Functions
class POSSystem {
    constructor() {
        this.cart = [];
        this.total = 0;
        this.initializePOS();
    }
    
    initializePOS() {
        this.bindEvents();
        this.updateCartDisplay();
    }
    
    bindEvents() {
        // Add to cart buttons
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', (e) => {
                const medicineId = e.target.dataset.medicineId;
                this.addToCart(medicineId);
            });
        });
        
        // Cart quantity changes
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('increase-qty')) {
                this.changeQuantity(e.target.dataset.itemId, 1);
            } else if (e.target.classList.contains('decrease-qty')) {
                this.changeQuantity(e.target.dataset.itemId, -1);
            } else if (e.target.classList.contains('remove-item')) {
                this.removeFromCart(e.target.dataset.itemId);
            }
        });
        
        // Checkout
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', () => this.checkout());
        }
    }
    
    addToCart(medicineId) {
        // Implementation for adding medicine to cart
        console.log('Add to cart:', medicineId);
    }
    
    changeQuantity(itemId, change) {
        // Implementation for changing quantity
        console.log('Change quantity:', itemId, change);
    }
    
    removeFromCart(itemId) {
        // Implementation for removing item
        console.log('Remove from cart:', itemId);
    }
    
    updateCartDisplay() {
        // Update cart UI
    }
    
    checkout() {
        // Process checkout
    }
}

// Utility Functions
function formatCurrency(amount) {
    return 'Rs. ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Initialize POS system if on POS page
if (document.querySelector('.pos-system')) {
    const pos = new POSSystem();
}

// Export functions for global access
window.POSSystem = POSSystem;
window.formatCurrency = formatCurrency;
window.showNotification = showNotification;

// User Dropdown Functionality
function initializeUserDropdown() {
    const userDropdown = document.getElementById('userDropdown');
    const userTrigger = document.querySelector('.user-menu-trigger');
    
    if (userDropdown && userTrigger) {
        // Toggle dropdown on click
        userTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });
        
        // Close dropdown when clicking on dropdown items
        const dropdownItems = document.querySelectorAll('.dropdown-item');
        dropdownItems.forEach(item => {
            item.addEventListener('click', function() {
                userDropdown.classList.remove('active');
            });
        });
    }
}

// Update the existing DOMContentLoaded function
document.addEventListener('DOMContentLoaded', function() {
    updateDateTime();
    updateGreeting();
    initializeSidebarToggle();
    initializeSearch();
    initializeFormValidations();
    initializeUserDropdown(); // Add this line
    
    // ... rest of existing code
});

