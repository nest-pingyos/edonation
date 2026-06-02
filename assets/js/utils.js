/**
 * Shared Frontend Utilities
 * Used across all public-facing pages.
 */

'use strict';

/**
 * Safely escape text for insertion into HTML.
 * @param {string} text
 * @returns {string}
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Truncate text to a maximum length and append ellipsis.
 * @param {string} text
 * @param {number} maxLength
 * @returns {string}
 */
function truncateText(text, maxLength) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength).trim() + '...';
}

/**
 * Thai display labels for news categories.
 * @type {Object.<string, string>}
 */
const NEWS_CATEGORY_LABELS = {
    general:      'ข่าวทั่วไป',
    announcement: 'ประกาศ',
    thank:        'ขอบคุณ',
    activity:     'กิจกรรม',
    donation:     'การรับบริจาค',
};

/**
 * Read the API base URL injected by PHP into a <meta> tag.
 * Falls back to the conventional path if the tag is absent.
 * @type {string}
 */
const API_BASE = document.querySelector('meta[name="api-base"]')?.content || '/edonation/api/v1';
