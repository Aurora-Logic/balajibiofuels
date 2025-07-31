// Mobile Navigation Functionality

// Add CSS for hamburger animation and mobile menu
const style = document.createElement('style');
style.textContent = `
    /* Hamburger animation */
    #mobile-menu-btn {
        transition: all 0.3s ease;
    }

    #mobile-menu-btn span {
        display: block;
        transform-origin: center;
        transition: all 0.3s ease;
    }

    #mobile-menu-btn.active span:first-child {
        transform: rotate(45deg) translate(4px, 4px);
    }

    #mobile-menu-btn.active span:nth-child(2) {
        opacity: 0;
        transform: scale(0);
    }

    #mobile-menu-btn.active span:last-child {
        transform: rotate(-45deg) translate(4px, -4px);
    }

    /* Prevent scrolling when menu is open */
    body.overflow-hidden {
        overflow: hidden;
    }

    /* Ensure mobile menu is visible on all screen sizes */
    @media (max-width: 768px) {
        #mobile-menu {
            display: none;
        }
        
        #mobile-menu:not(.hidden) {
            display: block !important;
        }
        
        #mobile-menu-btn {
            display: block !important;
        }
    }

    /* Ensure desktop menu is hidden on mobile */
    @media (max-width: 768px) {
        .md\\:flex {
            display: none !important;
        }
    }
`;
document.head.appendChild(style);

class MobileNavigation {
    constructor() {
        this.mobileMenuBtn = null;
        this.mobileMenu = null;
        this.closeMobileMenu = null;
        this.menuBackdrop = null;
        this.menuContent = null;
        this.isMenuOpen = false;
        this.init();
    }

    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupMobileMenu());
        } else {
            this.setupMobileMenu();
        }
    }

    setupMobileMenu() {
        // Get elements
        this.mobileMenuBtn = document.getElementById('mobile-menu-btn');
        this.mobileMenu = document.getElementById('mobile-menu');
        this.closeMobileMenu = document.getElementById('close-mobile-menu');
        
        // Debug logging
        console.log('Mobile Navigation Setup:', {
            mobileMenuBtn: !!this.mobileMenuBtn,
            mobileMenu: !!this.mobileMenu,
            closeMobileMenu: !!this.closeMobileMenu
        });
        
        if (this.mobileMenu) {
            this.menuBackdrop = this.mobileMenu.querySelector('.bg-black\\/80');
            this.menuContent = this.mobileMenu.querySelector('.absolute.inset-0.flex.flex-col.justify-between');
        }

        // Add event listeners
        this.addEventListeners();
        
        // Initialize navbar scroll behavior
        this.initNavbarScroll();
    }

    addEventListeners() {
        // Mobile menu toggle
        if (this.mobileMenuBtn) {
            this.mobileMenuBtn.addEventListener('click', () => this.toggleMobileMenu());
        }

        // Close button (removed since new design uses backdrop click)
        // if (this.closeMobileMenu) {
        //     this.closeMobileMenu.addEventListener('click', () => this.closeMenu());
        // }

        // Close on backdrop click
        if (this.mobileMenu) {
            this.mobileMenu.addEventListener('click', (e) => {
                if (e.target === this.mobileMenu || e.target === this.menuBackdrop) {
                    this.closeMenu();
                }
            });
        }

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMenuOpen) {
                this.closeMenu();
            }
        });

        // Close on nav link click
        const mobileNavLinks = this.mobileMenu?.querySelectorAll('.mobile-nav-link');
        if (mobileNavLinks) {
            mobileNavLinks.forEach(link => {
                link.addEventListener('click', () => this.closeMenu());
            });
        }
    }

    toggleMobileMenu() {
        if (this.isMenuOpen) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }

    openMenu() {
        this.isMenuOpen = true;
        
        // Update button state
        if (this.mobileMenuBtn) {
            this.mobileMenuBtn.classList.add('active');
        }

        // Show menu
        if (this.mobileMenu) {
            this.mobileMenu.classList.remove('hidden');
        }

        // Prevent body scroll
        document.body.classList.add('overflow-hidden');

        // Animate in
        setTimeout(() => {
            if (this.menuBackdrop) {
                this.menuBackdrop.classList.remove('opacity-0');
            }
            if (this.menuContent) {
                this.menuContent.classList.remove('-translate-y-full');
            }
        }, 10);

        // Update hamburger color
        this.updateHamburgerColor(true);
    }

    closeMenu() {
        this.isMenuOpen = false;
        
        // Update button state
        if (this.mobileMenuBtn) {
            this.mobileMenuBtn.classList.remove('active');
        }

        // Animate out
        if (this.menuBackdrop) {
            this.menuBackdrop.classList.add('opacity-0');
        }
        if (this.menuContent) {
            this.menuContent.classList.add('-translate-y-full');
        }

        // Hide after animation
        setTimeout(() => {
            if (this.mobileMenu) {
                this.mobileMenu.classList.add('hidden');
            }
            document.body.classList.remove('overflow-hidden');
        }, 300);

        // Update hamburger color
        this.updateHamburgerColor(false);
    }

    updateHamburgerColor(isMenuOpen) {
        const menuBtnSpans = document.querySelectorAll('#mobile-menu-btn span');
        
        if (isMenuOpen) {
            // Keep white when menu is open
            menuBtnSpans.forEach(span => {
                span.classList.remove('bg-gray-800');
                span.classList.add('bg-white');
            });
        } else {
            // Check if we're on the home page
            const isHomePage = window.location.pathname === '/' || window.location.pathname === '/index.html' || window.location.pathname.endsWith('/');
            
            if (window.scrollY > 10) {
                // Scrolled state - always dark hamburger
                menuBtnSpans.forEach(span => {
                    span.classList.remove('bg-white');
                    span.classList.add('bg-gray-800');
                });
            } else {
                // Top of page state
                if (isHomePage) {
                    // Home page - white hamburger
                    menuBtnSpans.forEach(span => {
                        span.classList.remove('bg-gray-800');
                        span.classList.add('bg-white');
                    });
                } else {
                    // Other pages - dark hamburger
                    menuBtnSpans.forEach(span => {
                        span.classList.remove('bg-white');
                        span.classList.add('bg-gray-800');
                    });
                }
            }
        }
    }

    initNavbarScroll() {
        const updateNavbarOnScroll = () => {
            const navbar = document.getElementById('main-navbar');
            const navLinks = navbar?.querySelectorAll('.nav-link');
            const navBrand = document.getElementById('nav-brand');
            const menuBtnSpans = document.querySelectorAll('#mobile-menu-btn span');
            
            // Check if we're on the home page
            const isHomePage = window.location.pathname === '/' || window.location.pathname === '/index.html' || window.location.pathname.endsWith('/');
            
            if (window.scrollY > 10) {
                // Scrolled state - always white background with dark text
                navbar?.classList.remove('bg-transparent');
                navbar?.classList.add('bg-white', 'border-b', 'border-gray-100', 'shadow-sm');
                navBrand?.classList.remove('text-white');
                navBrand?.classList.add('text-gray-900');
                
                navLinks?.forEach(link => {
                    link.classList.remove('text-white');
                    link.classList.add('text-gray-900');
                });
                
                if (!this.isMenuOpen) {
                    menuBtnSpans.forEach(span => {
                        span.classList.remove('bg-white');
                        span.classList.add('bg-gray-800');
                    });
                }
            } else {
                // Top of page state
                if (isHomePage) {
                    // Home page - transparent background with white text
                    navbar?.classList.add('bg-transparent');
                    navbar?.classList.remove('bg-white', 'border-b', 'border-gray-100', 'shadow-sm');
                    navBrand?.classList.add('text-white');
                    navBrand?.classList.remove('text-gray-900');
                    
                    navLinks?.forEach(link => {
                        link.classList.add('text-white');
                        link.classList.remove('text-gray-900');
                    });
                    
                    if (!this.isMenuOpen) {
                        menuBtnSpans.forEach(span => {
                            span.classList.remove('bg-gray-800');
                            span.classList.add('bg-white');
                        });
                    }
                } else {
                    // Other pages - always white background with dark text
                    navbar?.classList.remove('bg-transparent');
                    navbar?.classList.add('bg-white', 'border-b', 'border-gray-100', 'shadow-sm');
                    navBrand?.classList.remove('text-white');
                    navBrand?.classList.add('text-gray-900');
                    
                    navLinks?.forEach(link => {
                        link.classList.remove('text-white');
                        link.classList.add('text-gray-900');
                    });
                    
                    if (!this.isMenuOpen) {
                        menuBtnSpans.forEach(span => {
                            span.classList.remove('bg-white');
                            span.classList.add('bg-gray-800');
                        });
                    }
                }
            }
        };
        
        window.addEventListener('scroll', updateNavbarOnScroll);
        updateNavbarOnScroll(); // Call immediately to set initial state
    }
}

// Initialize mobile navigation when nav.html is loaded
function initializeMobileNavigation() {
    new MobileNavigation();
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MobileNavigation;
} 