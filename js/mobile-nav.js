// Mobile Navigation Functionality

// Add CSS for hamburger animation and mobile menu
const style = document.createElement('style');
style.textContent = `
    /* Hamburger button */
    #mobile-menu-btn {
        transition: all 0.3s ease;
    }

    #mobile-menu-btn span {
        display: block;
        transition: all 0.3s ease;
    }

    /* Hide hamburger when menu is open */
    #mobile-menu-btn.menu-open {
        opacity: 0 !important;
        pointer-events: none !important;
        visibility: hidden !important;
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

    /* Ensure social icons are visible */
    #mobile-menu .flex.space-x-6 {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    #mobile-menu .flex.space-x-6 a {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        border-radius: 50% !important;
        background-color: rgba(255, 255, 255, 0.1) !important;
        padding: 12px !important;
        width: 48px !important;
        height: 48px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #mobile-menu .flex.space-x-6 svg {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    #mobile-menu .flex.space-x-6 i {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
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

        // Close button
        if (this.closeMobileMenu) {
            this.closeMobileMenu.addEventListener('click', () => this.closeMenu());
        }

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
        
        // Hide hamburger button
        if (this.mobileMenuBtn) {
            this.mobileMenuBtn.classList.add('menu-open');
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
    }

    closeMenu() {
        this.isMenuOpen = false;
        
        // Show hamburger button
        if (this.mobileMenuBtn) {
            this.mobileMenuBtn.classList.remove('menu-open');
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