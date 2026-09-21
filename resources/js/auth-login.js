document.addEventListener('alpine:init', () => {
    Alpine.data('loginComponent', () => ({
        activeTab: 'cs',
        
        switchTab(tab) {
            this.activeTab = tab;
        }
    }));
});