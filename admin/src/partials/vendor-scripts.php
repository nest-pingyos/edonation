<?php
/**
 * eDonation Admin - Vendor Scripts
 * 
 * ตรวจสอบว่ามี local JS files หรือไม่
 * ถ้า build แล้ว จะใช้ local files
 * ถ้ายังไม่ build จะใช้ CDN fallback
 */

$localJsExists = file_exists(__DIR__ . '/../assets/js/app.js');
$basePath = '';
?>

<?php if ($localJsExists): ?>
    <!-- jQuery (required for Select2 and other plugins) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- SweetAlert2 (Ensure it is loaded) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.11.0/dist/sweetalert2.all.min.js"></script>

    <!-- Local JS Files -->
    <script src="<?php echo $basePath; ?>assets/js/vendor.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/app.js"></script>

    <!-- Select2 (Must be loaded AFTER local vendor.js if it contains jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<?php else: ?>
    <!-- CDN Fallback (Before Build) -->

    <!-- jQuery (for Select2 and other plugins) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Simplebar -->
    <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.6/dist/simplebar.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.11.0/dist/sweetalert2.all.min.js"></script>

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>

    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Choices.js -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <!-- Clipboard -->
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.11/dist/clipboard.min.js"></script>

    <!-- Prism.js -->
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/prism.min.js"></script>

<?php endif; ?>

<!-- App JavaScript -->
<script>
    (function () {
        'use strict';

        // Initialize Simplebar
        if (typeof SimpleBar !== 'undefined') {
            document.querySelectorAll('[data-simplebar]').forEach(function (el) {
                new SimpleBar(el);
            });
        }

        // Theme Configuration
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-bs-theme', savedTheme);

        // Toggle Theme
        window.toggleTheme = function () {
            const current = html.getAttribute('data-bs-theme');
            const newTheme = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        };

        // Mobile Menu Toggle
        window.toggleMenu = function () {
            const nav = document.querySelector('.main-nav');
            if (nav) nav.classList.toggle('show');
        };

        // Close mobile menu when clicking outside
        document.addEventListener('click', function (e) {
            const nav = document.querySelector('.main-nav');
            // Check if clicked element is (or is inside) the toggle button
            const isToggleBtn = e.target.closest('.menu-toggle');

            // If clicked on toggle button, let the toggle function handle it (do nothing here)
            if (isToggleBtn) return;

            // If menu is open and click is outside menu -> close it
            if (nav && nav.classList.contains('show') && !nav.contains(e.target)) {
                nav.classList.remove('show');
            }
        });

        // Active menu item
        const currentPath = window.location.pathname.split('/').pop();
        document.querySelectorAll('.nav-link, .sub-nav-link').forEach(function (link) {
            const href = link.getAttribute('href');
            if (href === currentPath) {
                link.classList.add('active');
                // Expand parent menu
                const parentCollapse = link.closest('.collapse');
                if (parentCollapse) {
                    parentCollapse.classList.add('show');
                    const parentLink = document.querySelector('[href="#' + parentCollapse.id + '"], [data-bs-target="#' + parentCollapse.id + '"]');
                    if (parentLink) {
                        parentLink.setAttribute('aria-expanded', 'true');
                        parentLink.classList.remove('collapsed');
                    }
                }
            }
        });

        // Initialize tooltips
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(function (el) {
                new bootstrap.Tooltip(el);
            });

            // Initialize popovers
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            popoverTriggerList.forEach(function (el) {
                new bootstrap.Popover(el);
            });
        }

    })();

    // ============================================
    // Utility Functions
    // ============================================

    // Format number with commas
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Format currency (Thai Baht)
    function formatCurrency(num) {
        return '฿' + formatNumber(parseFloat(num).toFixed(2));
    }

    // Copy to clipboard with toast
    function copyToClipboard(text, message) {
        navigator.clipboard.writeText(text).then(function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: message || 'คัดลอกแล้ว!',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        });
    }

    // Confirm dialog
    function confirmAction(options) {
        if (typeof Swal === 'undefined') return Promise.resolve({ isConfirmed: true });

        return Swal.fire({
            title: options.title || 'ยืนยันการดำเนินการ?',
            text: options.text || 'คุณต้องการดำเนินการนี้หรือไม่?',
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1c84ee',
            cancelButtonColor: '#ef5f5f',
            confirmButtonText: options.confirmText || 'ยืนยัน',
            cancelButtonText: options.cancelText || 'ยกเลิก'
        });
    }

    // Delete confirm dialog
    function confirmDelete(itemName) {
        return confirmAction({
            title: 'ยืนยันการลบ?',
            text: 'คุณต้องการลบ "' + itemName + '" หรือไม่? การดำเนินการนี้ไม่สามารถย้อนกลับได้',
            icon: 'warning',
            confirmText: 'ลบ',
            cancelText: 'ยกเลิก'
        });
    }

    // Toast notification
    function showToast(type, message) {
        if (typeof Swal === 'undefined') {
            alert(message);
            return;
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    // Success toast
    function showSuccess(message) {
        showToast('success', message);
    }

    // Error toast
    function showError(message) {
        showToast('error', message);
    }

    // Warning toast
    function showWarning(message) {
        showToast('warning', message);
    }

    // Info toast
    function showInfo(message) {
        showToast('info', message);
    }

    // ============================================
    // API Request Helper
    // ============================================

    if (typeof API_BASE_URL === 'undefined') {
        var API_BASE_URL = '<?php echo defined("API_URL") ? API_URL : "/edonation/api/v1"; ?>';
    }

    async function apiRequest(endpoint, options = {}) {
        const url = API_BASE_URL + endpoint;

        try {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...options.headers
                },
                ...options
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error?.message || data.message || 'เกิดข้อผิดพลาด');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // API GET request
    async function apiGet(endpoint) {
        return apiRequest(endpoint, { method: 'GET' });
    }

    // API POST request
    async function apiPost(endpoint, data) {
        return apiRequest(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    // API PUT request
    async function apiPut(endpoint, data) {
        return apiRequest(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    // API DELETE request
    async function apiDelete(endpoint) {
        return apiRequest(endpoint, { method: 'DELETE' });
    }

    // ============================================
    // Form Helpers
    // ============================================

    // Serialize form to object
    function serializeForm(form) {
        const formData = new FormData(form);
        const obj = {};
        formData.forEach((value, key) => {
            if (obj[key]) {
                if (!Array.isArray(obj[key])) {
                    obj[key] = [obj[key]];
                }
                obj[key].push(value);
            } else {
                obj[key] = value;
            }
        });
        return obj;
    }

    // Set form loading state
    function setFormLoading(form, loading) {
        const submitBtn = form.querySelector('[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = loading;
            if (loading) {
                submitBtn.dataset.originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังดำเนินการ...';
            } else {
                submitBtn.innerHTML = submitBtn.dataset.originalText || 'บันทึก';
            }
        }
    }

    // ============================================
    // Table Helpers
    // ============================================

    // Check/Uncheck all checkboxes
    function toggleAllCheckboxes(masterCheckbox, selector) {
        const checkboxes = document.querySelectorAll(selector);
        checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
    }

    // Get selected checkbox values
    function getSelectedValues(selector) {
        const checkboxes = document.querySelectorAll(selector + ':checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    // ============================================
    // Date Helpers (Thai Buddhist Year)
    // ============================================

    // Convert AD year to Thai Buddhist year
    function toThaiYear(year) {
        return parseInt(year) + 543;
    }

    // Convert Thai Buddhist year to AD year
    function toADYear(thaiYear) {
        return parseInt(thaiYear) - 543;
    }

    // Format date to Thai format
    function formatThaiDate(dateStr) {
        const date = new Date(dateStr);
        const thaiMonths = [
            'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน',
            'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม',
            'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
        ];

        const day = date.getDate();
        const month = thaiMonths[date.getMonth()];
        const year = toThaiYear(date.getFullYear());

        return `${day} ${month} ${year}`;
    }

    // Format date short
    function formatThaiDateShort(dateStr) {
        const date = new Date(dateStr);
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const year = toThaiYear(date.getFullYear());

        return `${day}/${month}/${year}`;
    }
</script>