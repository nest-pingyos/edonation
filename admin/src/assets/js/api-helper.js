/**
 * eDonation Admin - API Helper Functions
 * 
 * ฟังก์ชันช่วยเหลือสำหรับเรียก API และจัดการข้อมูล
 */

// API Base URL - detect from current location
// API Base URL - detect from current location
if (typeof API_BASE === 'undefined') {
    var API_BASE = (() => {
        // Get base path from current URL (handles /appdev/edonation, /edonation or root)
        const path = window.location.pathname;
        // Look for /edonation or /appdev/edonation pattern
        const match = path.match(/^(.*?\/edonation)/);
        if (match) {
            return match[1] + '/api/v1';
        }
        // If no /edonation pattern found (Docker root deployment), use /api/v1
        return '/api/v1';
    })();
}

/**
 * HTTP Request Functions
 */
async function apiGet(endpoint, params = {}) {
    const url = new URL(API_BASE + endpoint, window.location.origin);
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

    const response = await fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    });

    const data = await response.json();

    if (!response.ok || data.success === false) {
        throw new Error(data.error?.message || data.message || 'เกิดข้อผิดพลาด');
    }

    return data;
}

async function apiPost(endpoint, body = {}) {
    const response = await fetch(API_BASE + endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(body)
    });

    const data = await response.json();

    if (!response.ok || data.success === false) {
        throw new Error(data.error?.message || data.message || 'เกิดข้อผิดพลาด');
    }

    return data;
}

async function apiPut(endpoint, body = {}) {
    const response = await fetch(API_BASE + endpoint, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(body)
    });

    const data = await response.json();

    if (!response.ok || data.success === false) {
        throw new Error(data.error?.message || data.message || 'เกิดข้อผิดพลาด');
    }

    return data;
}

async function apiDelete(endpoint) {
    const response = await fetch(API_BASE + endpoint, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    });

    const data = await response.json();

    if (!response.ok || data.success === false) {
        throw new Error(data.error?.message || data.message || 'เกิดข้อผิดพลาด');
    }

    return data;
}

/**
 * Notification Functions (using SweetAlert2)
 */
function showSuccess(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: message,
            timer: 2000,
            showConfirmButton: false
        });
    } else {
        alert(message);
    }
}

function showError(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: message
        });
    } else {
        alert(message);
    }
}

function showWarning(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'คำเตือน',
            text: message
        });
    } else {
        alert(message);
    }
}

async function confirmDelete(itemName = 'รายการนี้') {
    if (typeof Swal !== 'undefined') {
        return await Swal.fire({
            icon: 'warning',
            title: 'ยืนยันการลบ?',
            text: `คุณต้องการลบ "${itemName}" ใช่หรือไม่?`,
            showCancelButton: true,
            confirmButtonColor: '#ef5f5f',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก'
        });
    } else {
        return { isConfirmed: confirm(`คุณต้องการลบ "${itemName}" ใช่หรือไม่?`) };
    }
}

async function confirmAction(title, text, confirmText = 'ยืนยัน') {
    if (typeof Swal !== 'undefined') {
        return await Swal.fire({
            icon: 'question',
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonColor: '#1c84ee',
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmText,
            cancelButtonText: 'ยกเลิก'
        });
    } else {
        return { isConfirmed: confirm(text) };
    }
}

/**
 * Formatting Functions
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('th-TH', {
        style: 'currency',
        currency: 'THB',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(amount || 0);
}

function formatNumber(num) {
    return new Intl.NumberFormat('th-TH').format(num || 0);
}

function formatThaiDate(dateStr) {
    if (!dateStr) return '-';
    try {
        const date = new Date(dateStr);
        return date.toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch {
        return dateStr;
    }
}

function formatThaiDateShort(dateStr) {
    if (!dateStr) return '-';
    try {
        const date = new Date(dateStr);
        return date.toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch {
        return dateStr;
    }
}

function formatDateInput(dateStr) {
    if (!dateStr) return '';
    try {
        const date = new Date(dateStr);
        return date.toISOString().split('T')[0];
    } catch {
        return '';
    }
}

/**
 * ID Card & Phone Formatting
 */
function formatIdCard(idCard) {
    if (!idCard) return '-';
    const id = idCard.replace(/\D/g, '');
    if (id.length !== 13) return idCard;
    return `${id[0]}-${id.substring(1, 5)}-${id.substring(5, 10)}-${id.substring(10, 12)}-${id[12]}`;
}

function maskIdCard(idCard) {
    if (!idCard || idCard.length < 13) return idCard || '-';
    return idCard.substring(0, 3) + '-****-*****-' + idCard.substring(10);
}

function formatPhone(phone) {
    if (!phone) return '-';
    const p = phone.replace(/\D/g, '');
    if (p.length === 10) {
        return `${p.substring(0, 3)}-${p.substring(3, 6)}-${p.substring(6)}`;
    }
    return phone;
}

/**
 * Utility Functions
 */
function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function truncateText(text, maxLength = 50) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

/**
 * Status Badge Functions
 */
function getDonationStatusBadge(status) {
    const badges = {
        'CONFIRMED': '<span class="badge badge-soft-success">ยืนยันแล้ว</span>',
        'PENDING': '<span class="badge badge-soft-warning">รอยืนยัน</span>',
        'CANCELLED': '<span class="badge badge-soft-danger">ยกเลิก</span>',
        'EXPIRED': '<span class="badge badge-soft-secondary">หมดอายุ</span>'
    };
    return badges[status] || badges['PENDING'];
}

function getProjectStatusBadge(status) {
    const badges = {
        'active': '<span class="badge badge-soft-success">กำลังดำเนินการ</span>',
        'inactive': '<span class="badge badge-soft-warning">ปิดรับบริจาค</span>',
        'completed': '<span class="badge badge-soft-secondary">เสร็จสิ้น</span>'
    };
    return badges[status] || badges['active'];
}

function getReceiptStatusBadge(status) {
    const badges = {
        'issued': '<span class="badge badge-soft-success">ออกแล้ว</span>',
        'pending': '<span class="badge badge-soft-warning">รอออก</span>',
        'cancelled': '<span class="badge badge-soft-danger">ยกเลิก</span>'
    };
    return badges[status] || badges['issued'];
}

function getBooleanBadge(value, trueText = 'เปิด', falseText = 'ปิด') {
    return value ?
        `<span class="badge badge-soft-success">${trueText}</span>` :
        `<span class="badge badge-soft-secondary">${falseText}</span>`;
}

/**
 * Pagination Helper
 */
function renderPagination(containerId, currentPage, totalPages, onPageChange) {
    const container = document.getElementById(containerId);
    if (!container || totalPages <= 1) {
        if (container) container.innerHTML = '';
        return;
    }

    let html = '<ul class="pagination pagination-sm mb-0">';

    // Previous
    html += `<li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="${onPageChange}(${currentPage - 1})">&laquo;</a>
    </li>`;

    // Pages
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="${onPageChange}(1)">1</a></li>`;
        if (startPage > 2) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="${onPageChange}(${i})">${i}</a>
        </li>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="${onPageChange}(${totalPages})">${totalPages}</a></li>`;
    }

    // Next
    html += `<li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="${onPageChange}(${currentPage + 1})">&raquo;</a>
    </li>`;

    html += '</ul>';
    container.innerHTML = html;
}

/**
 * Loading States
 */
function showTableLoading(tableId, colspan = 5) {
    const tbody = document.getElementById(tableId);
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="${colspan}" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                </td>
            </tr>
        `;
    }
}

function showTableEmpty(tableId, colspan = 5, message = 'ไม่พบข้อมูล') {
    const tbody = document.getElementById(tableId);
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="${colspan}" class="text-center py-4 text-muted">
                    <iconify-icon icon="iconamoon:file-search-duotone" class="fs-48 d-block mb-2"></iconify-icon>
                    ${message}
                </td>
            </tr>
        `;
    }
}

function showTableError(tableId, colspan = 5, error = 'เกิดข้อผิดพลาด') {
    const tbody = document.getElementById(tableId);
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="${colspan}" class="text-center py-4 text-danger">
                    <iconify-icon icon="iconamoon:alert-circle-duotone" class="fs-32 d-block mb-2"></iconify-icon>
                    ${escapeHtml(error)}
                </td>
            </tr>
        `;
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        apiGet, apiPost, apiPut, apiDelete,
        showSuccess, showError, showWarning, confirmDelete, confirmAction,
        formatCurrency, formatNumber, formatThaiDate, formatThaiDateShort,
        formatIdCard, maskIdCard, formatPhone,
        escapeHtml, debounce, truncateText,
        getDonationStatusBadge, getProjectStatusBadge, getReceiptStatusBadge,
        renderPagination, showTableLoading, showTableEmpty, showTableError
    };
}
