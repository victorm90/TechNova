const showTableLoading = (element) => {
    const tableCard = element.closest('[data-table-card]');

    if (tableCard) {
        tableCard.classList.add('is-loading');
    }
};

const syncTableScrollShadow = (container) => {
    container.classList.toggle('is-scrolled', container.scrollTop > 0);
};

document.addEventListener('submit', (event) => {
    if (event.target.matches('form[data-table-search]')) {
        showTableLoading(event.target);
    }
});

document.addEventListener('click', (event) => {
    if (event.target.closest('nav[aria-label="Paginación"] a')) {
        showTableLoading(event.target);
    }
});

const registerTableScrollShadows = () => {
    document.querySelectorAll('.nova-table-scroll').forEach((container) => {
        if (container.dataset.scrollBound === 'true') {
            return;
        }

        container.dataset.scrollBound = 'true';
        container.addEventListener('scroll', () => syncTableScrollShadow(container), { passive: true });
        syncTableScrollShadow(container);
    });
};

document.addEventListener('DOMContentLoaded', registerTableScrollShadows);

window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-table-card].is-loading').forEach((card) => {
        card.classList.remove('is-loading');
    });

    registerTableScrollShadows();
});

const SIDEBAR_STORAGE_KEY = 'nova-sidebar-size';

const sidebarElements = {
    root: document.documentElement,
    panel: document.querySelector('.app-menu'),
    overlay: document.getElementById('sidebar-overlay'),
    toggle: document.getElementById('topnav-hamburger-icon'),
};

const desktopSidebar = window.matchMedia('(min-width: 768px)');

const isSidebarCollapsed = () => sidebarElements.root.getAttribute('data-sidebar-size') === 'sm';

const isMobileSidebarOpen = () => sidebarElements.panel?.classList.contains('is-open') ?? false;

const updateSidebarAria = () => {
    if (!sidebarElements.toggle) {
        return;
    }

    const expanded = desktopSidebar.matches ? !isSidebarCollapsed() : isMobileSidebarOpen();

    sidebarElements.toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
};

const setDesktopSidebar = (collapsed, persist = true) => {
    sidebarElements.root.setAttribute('data-sidebar-size', collapsed ? 'sm' : 'lg');

    if (persist) {
        sessionStorage.setItem(SIDEBAR_STORAGE_KEY, collapsed ? 'sm' : 'lg');
    }

    updateSidebarAria();
};

const setMobileSidebar = (open) => {
    if (!sidebarElements.panel) {
        return;
    }

    if (open) {
        sidebarElements.root.setAttribute('data-sidebar-size', 'lg');
    }

    sidebarElements.panel.classList.toggle('is-open', open);
    sidebarElements.overlay?.classList.toggle('hidden', !open);
    sidebarElements.overlay?.setAttribute('aria-hidden', open ? 'false' : 'true');
    document.body.classList.toggle('overflow-hidden', open && !desktopSidebar.matches);

    updateSidebarAria();
};

const toggleSidebar = () => {
    if (desktopSidebar.matches) {
        setMobileSidebar(false);
        setDesktopSidebar(!isSidebarCollapsed());

        return;
    }

    setMobileSidebar(!isMobileSidebarOpen());
};

const syncSidebarWithViewport = () => {
    if (desktopSidebar.matches) {
        setMobileSidebar(false);
        setDesktopSidebar(sessionStorage.getItem(SIDEBAR_STORAGE_KEY) === 'sm', false);

        return;
    }

    sidebarElements.root.setAttribute('data-sidebar-size', 'lg');
    setMobileSidebar(isMobileSidebarOpen());
};

const registerSidebar = () => {
    if (!sidebarElements.panel) {
        return;
    }

    if (desktopSidebar.matches) {
        setDesktopSidebar(sessionStorage.getItem(SIDEBAR_STORAGE_KEY) === 'sm', false);
    }

    updateSidebarAria();

    sidebarElements.toggle?.addEventListener('click', toggleSidebar);
    sidebarElements.overlay?.addEventListener('click', () => setMobileSidebar(false));

    sidebarElements.panel.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (!desktopSidebar.matches) {
                setMobileSidebar(false);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isMobileSidebarOpen()) {
            setMobileSidebar(false);
            sidebarElements.toggle?.focus();
        }
    });

    desktopSidebar.addEventListener('change', syncSidebarWithViewport);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', registerSidebar);
} else {
    registerSidebar();
}
