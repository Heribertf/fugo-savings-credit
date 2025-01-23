// Dark Mode Handler with Cross-page Consistency
(function() {
    // Initialize as soon as possible to prevent flash of wrong theme
    const initializeTheme = () => {
        const darkMode = localStorage.getItem('darkMode');
        if (darkMode === 'enabled') {
            document.body.classList.add('dark-mode');
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    };
    
    // Run initialization immediately
    initializeTheme();

    document.addEventListener('DOMContentLoaded', () => {
        // Get required elements
        const darkModeSwitch = document.querySelector('.dark-mode-switch');
        const body = document.body;

        // Update checkbox state based on current mode
        if (darkModeSwitch) {
            darkModeSwitch.checked = localStorage.getItem('darkMode') === 'enabled';
        }

        // Handle dark mode toggle
        const setupDarkModeToggle = () => {
            if (!darkModeSwitch) return;
            
            darkModeSwitch.addEventListener('change', () => {
                if (darkModeSwitch.checked) {
                    enableDarkMode();
                } else {
                    disableDarkMode();
                }
            });
        };

        // Function to enable dark mode
        function enableDarkMode() {
            body.classList.add('dark-mode');
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('darkMode', 'enabled');
            updateAllSwitches(true);
            
            // Dispatch event for other scripts that might need to know about theme changes
            window.dispatchEvent(new CustomEvent('themeChanged', { 
                detail: { theme: 'dark' }
            }));
        }

        // Function to disable dark mode
        function disableDarkMode() {
            body.classList.remove('dark-mode');
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('darkMode', 'disabled');
            updateAllSwitches(false);
            
            // Dispatch event for other scripts that might need to know about theme changes
            window.dispatchEvent(new CustomEvent('themeChanged', { 
                detail: { theme: 'light' }
            }));
        }

        // Update all dark mode switches on the page
        function updateAllSwitches(checked) {
            document.querySelectorAll('.dark-mode-switch').forEach(switchElement => {
                switchElement.checked = checked;
            });
        }

        // Set up mutation observer to handle dynamically added content
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                    // Check if any new dark mode switches were added
                    mutation.addedNodes.forEach((node) => {
                        if (node.classList && node.classList.contains('dark-mode-switch')) {
                            node.checked = localStorage.getItem('darkMode') === 'enabled';
                        }
                    });
                }
            });
        });

        // Start observing the document with the configured parameters
        observer.observe(document.body, { 
            childList: true, 
            subtree: true 
        });

        // Initialize theme toggle
        setupDarkModeToggle();

        // Add prefers-color-scheme media query support
        const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
        
        // Handle system theme changes
        prefersDarkScheme.addEventListener('change', (e) => {
            if (localStorage.getItem('darkMode') === null) { // Only auto-switch if user hasn't manually set preference
                if (e.matches) {
                    enableDarkMode();
                } else {
                    disableDarkMode();
                }
            }
        });

        // Export functions for external use
        window.themeHandler = {
            enable: enableDarkMode,
            disable: disableDarkMode,
            toggle: () => {
                if (localStorage.getItem('darkMode') === 'enabled') {
                    disableDarkMode();
                } else {
                    enableDarkMode();
                }
            }
        };


    });
})();