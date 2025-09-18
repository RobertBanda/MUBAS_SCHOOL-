// script.js

// Tab navigation logic
function showTab(tabId) {
    // Hide all tabs
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    // Show the selected tab
    const tab = document.getElementById(tabId);
    if (tab) tab.classList.add('active');
    // Remove active from all buttons
    document.querySelectorAll('nav button').forEach(btn => btn.classList.remove('active'));
    // Add active to the correct button
    const btn = document.querySelector(`nav button[data-tab="${tabId}"]`);
    if (btn) btn.classList.add('active');
    
    // Close mobile menu after selection
    if (window.innerWidth <= 768) {
        document.getElementById('navMenu').classList.remove('active');
    }
    
    // Update URL with tab parameter
    if (history.pushState) {
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.pushState({}, '', url);
    }
}

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
    
    // Show appropriate tab on page load
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    
    if (tabParam && document.getElementById(tabParam)) {
        showTab(tabParam);
    } else {
        showTab('dashboard'); // Default to dashboard
    }
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768 && navMenu.classList.contains('active')) {
            if (!navToggle.contains(event.target) && !navMenu.contains(event.target)) {
                navMenu.classList.remove('active');
            }
        }
    });
    
    // Auto-calculate amount based on units consumed
    const unitsInput = document.getElementById('units_consumed');
    const amountInput = document.getElementById('amount');
    
    if (unitsInput && amountInput) {
        unitsInput.addEventListener('input', function() {
            const units = parseFloat(this.value);
            let amount = 0;
            if (!isNaN(units)) {
                if (units <= 5) {
                    amount = 3802;
                } else if (units <= 9) {
                    amount = 7 * 790;
                } else {
                    amount = 10 * 1431;
                }
            }
            amountInput.value = amount;
        });
    }
    
    // Customer selection for new customer
    const customerSelect = document.getElementById('customer_id');
    const newCustomerName = document.getElementById('new_customer_name');
    
    if (customerSelect && newCustomerName) {
        customerSelect.addEventListener('change', function() {
            const show = this.value === 'new';
            newCustomerName.style.display = show ? 'block' : 'none';
            newCustomerName.required = show;
        });
    }
    
    // Store transaction validation
    const quantityInput = document.getElementById('quantity');
    const transactionTypeSelect = document.getElementById('transaction_type');
    const storeSelect = document.getElementById('store_id');
    
    if (quantityInput && transactionTypeSelect && storeSelect) {
        quantityInput.addEventListener('input', function() {
            if (transactionTypeSelect.value === 'OUT') {
                const selectedOption = storeSelect.options[storeSelect.selectedIndex];
                if (selectedOption && selectedOption.dataset.balance) {
                    const currentBalance = parseInt(selectedOption.dataset.balance);
                    const quantity = parseInt(this.value);
                    
                    if (quantity > currentBalance) {
                        this.setCustomValidity('Cannot remove more than available items');
                    } else {
                        this.setCustomValidity('');
                    }
                }
            }
        });
        
        transactionTypeSelect.addEventListener('change', function() {
            quantityInput.setCustomValidity('');
        });
    }
});

// Handle browser resize
window.addEventListener('resize', function() {
    const navMenu = document.getElementById('navMenu');
    if (window.innerWidth > 768 && navMenu) {
        navMenu.classList.remove('active');
    }
});


