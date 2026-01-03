/**
 * eDonation Web Configuration
 * ใช้สำหรับ JavaScript ในการเรียก API
 * 
 * รองรับ API แยก domain
 * 
 * @version 2.0
 */
(function () {
    'use strict';

    // ตรวจสอบ config จาก meta tags
    const metaBase = document.querySelector('meta[name="base-path"]');
    const metaApiBase = document.querySelector('meta[name="api-base"]');
    const metaApiUrl = document.querySelector('meta[name="api-url"]');

    // Base paths
    const basePath = metaBase ? metaBase.getAttribute('content') : '/edonation';

    // API URL - รองรับ full URL (cross-domain) หรือ relative path (same domain)
    let apiBase;
    if (metaApiUrl) {
        // ใช้ full API URL (สำหรับ API แยก domain)
        apiBase = metaApiUrl.getAttribute('content');
    } else if (metaApiBase) {
        // ใช้ relative API base path
        apiBase = metaApiBase.getAttribute('content');
    } else {
        // Default: same domain
        apiBase = basePath + '/api/v1';
    }

    // ตรวจสอบว่า API อยู่คนละ domain หรือไม่
    const isApiCrossDomain = apiBase.startsWith('http') &&
        !apiBase.startsWith(window.location.origin);

    // กำหนด config
    window.EDONATION_CONFIG = {
        // Base paths
        BASE_PATH: basePath,
        API_BASE: apiBase,
        IS_API_CROSS_DOMAIN: isApiCrossDomain,

        // URLs
        get WEB_URL() {
            return window.location.origin + this.BASE_PATH;
        },
        get API_URL() {
            // ถ้า API_BASE เป็น full URL อยู่แล้ว ให้ใช้เลย
            if (this.API_BASE.startsWith('http')) {
                return this.API_BASE;
            }
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
            auth: '/auth',
            members: '/members',
            signatures: '/signatures',
            notifications: '/notifications'
        },

        // Helper function to build API URL
        api: function (endpoint, id = null) {
            let url = this.API_BASE + endpoint;
            if (id) url += '/' + id;
            return url;
        },

        /**
         * Fetch wrapper with CORS support for cross-domain API
         * @param {string} endpoint - API endpoint (e.g., '/projects')
         * @param {object} options - fetch options
         * @returns {Promise}
         */
        fetch: async function (endpoint, options = {}) {
            const url = this.api(endpoint);

            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            };

            // Add CORS mode if cross-domain
            if (this.IS_API_CROSS_DOMAIN) {
                defaultOptions.mode = 'cors';
                defaultOptions.credentials = 'include';
            }

            const finalOptions = {
                ...defaultOptions,
                ...options,
                headers: {
                    ...defaultOptions.headers,
                    ...(options.headers || {})
                }
            };

            const response = await fetch(url, finalOptions);
            return response.json();
        }
    };

    // Shorthand
    window.API_BASE = window.EDONATION_CONFIG.API_BASE;
    window.API_URL = window.EDONATION_CONFIG.API_URL;

    // Helper function for quick API calls
    window.fetchApi = window.EDONATION_CONFIG.fetch.bind(window.EDONATION_CONFIG);

    // Log config in development
    if (window.location.hostname === 'localhost') {
        console.log('eDonation Config:', {
            basePath: window.EDONATION_CONFIG.BASE_PATH,
            apiBase: window.EDONATION_CONFIG.API_BASE,
            isApiCrossDomain: window.EDONATION_CONFIG.IS_API_CROSS_DOMAIN
        });
    }
})();
