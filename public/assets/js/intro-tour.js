// Intro.js guided tour for first-time users
function initIntroTour() {
    // Skip if user already completed the tour
    if (localStorage.getItem('intro_completed') === 'true') {
        return;
    }

    // Skip on login page
    if (window.location.href.includes('action=login') || document.querySelector('.login-page')) {
        return;
    }

    // Skip if intro.js is not available
    if (typeof introJs !== 'function') {
        console.log('Intro.js not available, skipping tour');
        return;
    }

    try {
        // Initialize intro.js with custom options
        const intro = introJs();
        
        intro.setOptions({
            steps: getTourSteps(),
            showStepNumbers: true,
            showBullets: false,
            exitOnOverlayClick: false,
            exitOnEsc: true,
            nextLabel: 'Next →',
            prevLabel: '← Previous',
            skipLabel: 'Skip Tour',
            doneLabel: 'Get Started!',
            scrollToElement: true,
            scrollPadding: 30,
            overlayOpacity: 0.8,
            tooltipClass: 'customTooltip',
            highlightClass: 'customHighlight',
            helperElementPadding: 10,
            showProgress: true
        });

        // Custom CSS for intro.js
        addIntroStyles();

        // Event handlers
        intro.onbeforechange(function(targetElement) {
            // Add custom animations or logic before step change
            if (targetElement) {
                targetElement.classList.add('intro-highlight');
            }
        });

        intro.onafterchange(function(targetElement) {
            // Remove highlight from previous element
            document.querySelectorAll('.intro-highlight').forEach(el => {
                if (el !== targetElement) {
                    el.classList.remove('intro-highlight');
                }
            });

            // Custom logic for specific steps
            const currentStep = this._currentStep;
            handleStepActions(currentStep, targetElement);
        });

        intro.oncomplete(function() {
            localStorage.setItem('intro_completed', 'true');
            showCompletionMessage();
            cleanup();
        });

        intro.onexit(function() {
            localStorage.setItem('intro_completed', 'true');
            cleanup();
        });

        // Start the tour with a welcome message
        showWelcomeMessage().then(() => {
            intro.start();
        });

    } catch (error) {
        console.error('Error initializing intro tour:', error);
    }
}

function getTourSteps() {
    const steps = [];
    
    // Step 1: Welcome
    if (document.querySelector('.navbar-brand')) {
        steps.push({
            element: '.navbar-brand',
            intro: "<h4>Welcome to CRM RBAC System!</h4><p>This guided tour will show you around the main features. Let's get started!</p>",
            position: 'bottom'
        });
    }

    // Step 2: Sidebar navigation
    if (document.querySelector('.sidebar')) {
        steps.push({
            element: '.sidebar',
            intro: "<h4>Navigation Sidebar</h4><p>Use this sidebar to navigate between different sections. All your main features are organized here for easy access.</p>",
            position: 'right'
        });
    }

    // Step 3: Contacts section
    const contactsLink = document.querySelector('a[href*="contacts.list"]');
    if (contactsLink) {
        steps.push({
            element: contactsLink,
            intro: "<h4>Contacts Management</h4><p>This is where you can view, search, and manage all your contacts. Click here to see your contact list.</p>",
            position: 'right'
        });
    }

    // Step 4: Create contact (if user has permission)
    const createLink = document.querySelector('a[href*="contacts.create"]');
    if (createLink) {
        steps.push({
            element: createLink,
            intro: "<h4>Create New Contacts</h4><p>Add new contacts to your system quickly and easily. All fields are validated to ensure data quality.</p>",
            position: 'right'
        });
    }

    // Step 5: CSV Import
    const importLink = document.querySelector('a[href*="importCsvForm"]');
    if (importLink) {
        steps.push({
            element: importLink,
            intro: "<h4>Bulk Import</h4><p>Need to add many contacts at once? Use the CSV import feature to upload contacts in bulk with preview and validation.</p>",
            position: 'right'
        });
    }

    // Step 6: CSV Export
    const exportLink = document.querySelector('a[href*="exportCsv"]');
    if (exportLink) {
        steps.push({
            element: exportLink,
            intro: "<h4>Export Data</h4><p>Export your contacts to CSV format for backup or use in other applications.</p>",
            position: 'right'
        });
    }

    // Step 7: Audit Logs (admin only)
    const auditLink = document.querySelector('a[href*="audit.list"]');
    if (auditLink) {
        steps.push({
            element: auditLink,
            intro: "<h4>Audit Trail</h4><p>As an admin, you can view all system activities here. Every action is logged with timestamps and change details.</p>",
            position: 'right'
        });
    }

    // Step 8: Search functionality (if on contacts page)
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        steps.push({
            element: searchInput.closest('.card'),
            intro: "<h4>Search & Filter</h4><p>Use the search box to quickly find contacts by name, email, or company. You can also toggle to show deleted contacts.</p>",
            position: 'bottom'
        });
    }

    // Step 9: User info in navbar
    const userInfo = document.querySelector('.navbar-text');
    if (userInfo) {
        steps.push({
            element: userInfo,
            intro: "<h4>Your Account</h4><p>Your current user information and role are displayed here. Different roles have different permissions in the system.</p>",
            position: 'bottom'
        });
    }

    // Step 10: Particles background
    steps.push({
        intro: "<h4>Interactive Background</h4><p>The animated particles in the background respond to your mouse movements. You can interact with them by hovering or clicking!</p>"
    });

    // Final step
    steps.push({
        intro: "<h4>You're All Set!</h4><p>That's the end of our tour. You now know the main features of the CRM system. Start by exploring your contacts or creating new ones.</p><br><p><small>You can restart this tour anytime by clearing your browser's local storage for this site.</small></p>"
    });

    return steps;
}

function handleStepActions(stepIndex, targetElement) {
    // Custom actions for specific steps
    switch (stepIndex) {
        case 1:
            // Highlight sidebar items
            document.querySelectorAll('.sidebar .list-group-item').forEach(item => {
                item.style.transition = 'all 0.3s ease';
            });
            break;
            
        case 9:
            // Trigger particle interaction
            if (window.pJSDom && window.pJSDom.length > 0) {
                try {
                    window.pJSDom[0].pJS.interactivity.modes.push.particles_nb = 10;
                } catch (e) {
                    // Ignore errors
                }
            }
            break;
    }
}

function addIntroStyles() {
    if (document.getElementById('intro-custom-styles')) {
        return; // Already added
    }

    const style = document.createElement('style');
    style.id = 'intro-custom-styles';
    style.textContent = `
        .customTooltip {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.2);
            color: #212529;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        .customTooltip h4 {
            color: #0d6efd;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .customHighlight {
            border: 3px solid #0d6efd !important;
            border-radius: 8px !important;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25) !important;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25); }
            50% { box-shadow: 0 0 0 8px rgba(13, 110, 253, 0.15); }
            100% { box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25); }
        }
        
        .intro-highlight {
            position: relative;
            z-index: 9999999;
        }
        
        .introjs-button {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .introjs-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }
        
        .introjs-skipbutton {
            background: transparent;
            color: #6c757d;
            border: 1px solid #6c757d;
        }
        
        .introjs-skipbutton:hover {
            background: #6c757d;
            color: white;
        }
        
        .introjs-progressbar {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border-radius: 10px;
        }
        
        .introjs-bullets ul li a {
            background: rgba(13, 110, 253, 0.3);
        }
        
        .introjs-bullets ul li a.active {
            background: #0d6efd;
        }
    `;
    
    document.head.appendChild(style);
}

function showWelcomeMessage() {
    return new Promise((resolve) => {
        // Check if SweetAlert2 is available
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '👋 Welcome!',
                html: `
                    <p>Would you like a quick tour of the CRM system?</p>
                    <p><small>This will only take about 2 minutes and will show you the main features.</small></p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, show me around!',
                cancelButtonText: 'Maybe later',
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    resolve();
                } else {
                    localStorage.setItem('intro_completed', 'true');
                }
            });
        } else {
            // Fallback if SweetAlert2 is not available
            const startTour = confirm('Would you like a quick tour of the CRM system features?');
            if (startTour) {
                resolve();
            } else {
                localStorage.setItem('intro_completed', 'true');
            }
        }
    });
}

function showCompletionMessage() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '🎉 Tour Complete!',
            text: 'You\'re now ready to use the CRM system. Happy managing!',
            icon: 'success',
            confirmButtonText: 'Get Started',
            confirmButtonColor: '#198754',
            timer: 3000
        });
    }
}

function cleanup() {
    // Remove custom styles
    const styleElement = document.getElementById('intro-custom-styles');
    if (styleElement) {
        styleElement.remove();
    }

    // Remove highlight classes
    document.querySelectorAll('.intro-highlight').forEach(el => {
        el.classList.remove('intro-highlight');
    });
}

// Function to restart tour (for testing/admin purposes)
function restartIntroTour() {
    localStorage.removeItem('intro_completed');
    setTimeout(initIntroTour, 500);
}

// Function to reset tour progress (for admin/testing)
function resetIntroTour() {
    localStorage.removeItem('intro_completed');
    Swal.fire({
        title: 'Tour Reset',
        text: 'The intro tour has been reset. Refresh the page to see it again.',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

// Auto-initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        // Delay to ensure all other scripts are loaded
        setTimeout(initIntroTour, 1500);
    });
} else {
    setTimeout(initIntroTour, 1500);
}

// Export functions for global access
window.initIntroTour = initIntroTour;
window.restartIntroTour = restartIntroTour;
window.resetIntroTour = resetIntroTour;