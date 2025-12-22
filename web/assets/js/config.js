/**
 * eDonation Web Configuration
 * ใช้สำหรับ JavaScript ในการเรียก API
 */
(function () {
    // ตรวจสอบ base path จาก meta tag หรือใช้ default
    const metaBase = document.querySelector('meta[name="base-path"]');
    const basePath = metaBase ? metaBase.getAttribute('content') : '/edonation';

    // กำหนด config
    window.EDONATION_CONFIG = {
        // Base paths
        BASE_PATH: basePath,
        API_BASE: basePath + '/api/v1',

        // URLs
        get WEB_URL() {
            return window.location.origin + this.BASE_PATH;
        },
        get API_URL() {
            return window.location.origin + this.API_BASE;
        },

        // Endpoints
        ENDPOINTS: {
            projects: '/projects',
            donations: '/donations',
            receipts: '/receipts',
            payments: '/payments',
            benefits: '/benefits',
            news: '/news',
            auth: '/auth'
        },

        // Helper function to build API URL
        api: function (endpoint, id = null) {
            let url = this.API_BASE + endpoint;
            if (id) url += '/' + id;
            return url;
        }
    };

    // Shorthand
    window.API_BASE = window.EDONATION_CONFIG.API_BASE;
})();
