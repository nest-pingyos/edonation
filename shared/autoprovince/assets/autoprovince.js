/**
 * AutoProvince JavaScript Module
 * ระบบเลือกจังหวัด อำเภอ ตำบล อัตโนมัติ
 * 
 * @version 2.0 - eDonation Edition
 * 
 * Usage:
 * 1. Include this script
 * 2. Add HTML selects with ids: province, district, subdistrict, postcode
 * 3. Initialize: AutoProvince.init({ apiPath: '/shared/autoprovince/api.php' });
 */

/**
 * AutoProvince JavaScript Module
 * ระบบเลือกจังหวัด อำเภอ ตำบล อัตโนมัติ
 * 
 * @version 2.1 - Multiple Instance Support
 */

class AutoProvince {
    constructor(options = {}) {
        // Detect base path (handles /edonation, /appdev/edonation or root)
        const basePath = (() => {
            const path = window.location.pathname;
            const match = path.match(/^(.*?\/edonation)/);
            return match ? match[1] : '';
        })();

        // Default configuration
        this.config = {
            apiPath: basePath + '/shared/autoprovince/api.php',
            provinceSelector: '#province',
            districtSelector: '#district',
            subdistrictSelector: '#subdistrict',
            postcodeSelector: '#postcode',
            loaderSelector: '#loader',
            useSelect2: true,
            select2Theme: 'bootstrap-5',
            placeholders: {
                province: 'เลือกจังหวัด',
                district: 'เลือกอำเภอ',
                subdistrict: 'เลือกตำบล'
            },
            onProvinceChange: null,
            onDistrictChange: null,
            onSubdistrictChange: null,
            onAddressComplete: null
        };

        // Merge options
        Object.assign(this.config, options);

        // Support short keys
        if (options.province) this.config.provinceSelector = options.province;
        if (options.district) this.config.districtSelector = options.district;
        if (options.subdistrict) this.config.subdistrictSelector = options.subdistrict;
        if (options.amphure) this.config.districtSelector = options.amphure; // Alias for Thai context
        if (options.postcode) this.config.postcodeSelector = options.postcode;
        if (options.arrange) this.config.arrange = options.arrange;

        this.isInitialized = false;

        // Auto setup if DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        const $province = $(this.config.provinceSelector);
        const $district = $(this.config.districtSelector);
        const $subdistrict = $(this.config.subdistrictSelector);

        if (!$province.length) {
            return;
        }

        // Initialize Select2 if enabled
        if (this.config.useSelect2 && typeof $.fn.select2 !== 'undefined') {
            this.initSelect2($province, this.config.placeholders.province);
            if ($district.length) this.initSelect2($district, this.config.placeholders.district);
            if ($subdistrict.length) this.initSelect2($subdistrict, this.config.placeholders.subdistrict);
        }

        // Load provinces
        this.loadProvinces();

        // Bind events
        $province.on('change', () => this.handleProvinceChange());
        if ($district.length) $district.on('change', () => this.handleDistrictChange());
        if ($subdistrict.length) $subdistrict.on('change', () => this.handleSubdistrictChange());

        this.isInitialized = true;
    }

    initSelect2($element, placeholder) {
        $element.select2({
            theme: this.config.select2Theme,
            width: '100%',
            allowClear: true,
            placeholder: placeholder
        });
    }

    toggleLoader(show) {
        const $loader = $(this.config.loaderSelector);
        if ($loader.length) {
            if (show) $loader.css('display', 'flex');
            else $loader.fadeOut(200);
        }
    }

    loadProvinces() {
        this.toggleLoader(true);

        $.get(`${this.config.apiPath}?action=get_provinces`)
            .done((response) => {
                if (response.status === 'success') {
                    const $province = $(this.config.provinceSelector);
                    $province.empty().append('<option value=""></option>');

                    response.data.forEach((item) => {
                        $province.append(new Option(item.name, item.id, false, false));
                    });

                    if (this.config.useSelect2) {
                        $province.trigger('change.select2');
                    }
                }
            })
            .always(() => {
                this.toggleLoader(false);
            });
    }

    handleProvinceChange() {
        const $province = $(this.config.provinceSelector);
        const provinceId = $province.val();
        const $district = $(this.config.districtSelector);
        const $subdistrict = $(this.config.subdistrictSelector);
        const $postcode = $(this.config.postcodeSelector);

        // Reset dependent fields
        this.resetSelect($district, this.config.placeholders.district);
        this.resetSelect($subdistrict, this.config.placeholders.subdistrict);
        if ($postcode.length) $postcode.val('');

        if (provinceId) {
            this.fetchData('get_districts', { province_id: provinceId }, this.config.districtSelector);
        }

        // Callback
        if (typeof this.config.onProvinceChange === 'function') {
            const selectedText = $province.find('option:selected').text();
            this.config.onProvinceChange({ id: provinceId, name: selectedText });
        }
    }

    handleDistrictChange() {
        const $district = $(this.config.districtSelector);
        const districtId = $district.val();
        const $subdistrict = $(this.config.subdistrictSelector);
        const $postcode = $(this.config.postcodeSelector);

        // Reset dependent fields
        this.resetSelect($subdistrict, this.config.placeholders.subdistrict);
        if ($postcode.length) $postcode.val('');

        if (districtId) {
            this.fetchData('get_subdistricts', { district_id: districtId }, this.config.subdistrictSelector);
        }

        // Callback
        if (typeof this.config.onDistrictChange === 'function') {
            const selectedText = $district.find('option:selected').text();
            this.config.onDistrictChange({ id: districtId, name: selectedText });
        }
    }

    handleSubdistrictChange() {
        const $subdistrict = $(this.config.subdistrictSelector);
        const $postcode = $(this.config.postcodeSelector);

        if (this.config.useSelect2) {
            const selectedData = $subdistrict.select2('data')[0];
            if (selectedData && selectedData.element) {
                const postcode = $(selectedData.element).data('postcode');
                if (postcode && postcode !== '0' && $postcode.length) {
                    $postcode.val(postcode);
                }
            }
        } else {
            const postcode = $subdistrict.find('option:selected').data('postcode');
            if (postcode && $postcode.length) {
                $postcode.val(postcode);
            }
        }

        // Callback
        if (typeof this.config.onSubdistrictChange === 'function') {
            const selectedText = $subdistrict.find('option:selected').text();
            const postcode = $subdistrict.find('option:selected').data('postcode');
            this.config.onSubdistrictChange({
                id: $subdistrict.val(),
                name: selectedText,
                postcode: postcode
            });
        }

        // Complete callback
        if (typeof this.config.onAddressComplete === 'function') {
            const address = this.getAddress();
            this.config.onAddressComplete(address);
        }
    }

    fetchData(action, params, targetSelector) {
        this.toggleLoader(true);

        $.ajax({
            url: `${this.config.apiPath}?action=${action}`,
            type: 'POST',
            data: params,
            dataType: 'json'
        })
            .done((response) => {
                if (response.status === 'success') {
                    const $target = $(targetSelector);
                    $target.prop('disabled', false);

                    response.data.forEach((item) => {
                        const option = new Option(item.name, item.id, false, false);
                        if (item.postcode) $(option).data('postcode', item.postcode);
                        $target.append(option);
                    });

                    if (this.config.useSelect2) {
                        $target.trigger('change.select2');
                    }
                }
            })
            .always(() => {
                this.toggleLoader(false);
            });
    }

    resetSelect(selector, placeholder) {
        const $el = $(selector);
        if (!$el.length) return;

        $el.empty()
            .append('<option value=""></option>')
            .prop('disabled', true);

        if (this.config.useSelect2) {
            $el.trigger('change.select2');
        }
    }

    getAddress() {
        const $province = $(this.config.provinceSelector);
        const $district = $(this.config.districtSelector);
        const $subdistrict = $(this.config.subdistrictSelector);
        const $postcode = $(this.config.postcodeSelector);

        return {
            province: {
                id: $province.val(),
                name: $province.find('option:selected').text()
            },
            district: {
                id: $district.val(),
                name: $district.find('option:selected').text()
            },
            subdistrict: {
                id: $subdistrict.val(),
                name: $subdistrict.find('option:selected').text()
            },
            postcode: $postcode.length ? $postcode.val() : ''
        };
    }

    formatAddress() {
        const addr = this.getAddress();
        if (!addr.province.id) return '';

        let parts = [];
        if (addr.subdistrict.id) parts.push('ต.' + addr.subdistrict.name);
        if (addr.district.id) parts.push('อ.' + addr.district.name);
        if (addr.province.id) parts.push('จ.' + addr.province.name);
        if (addr.postcode) parts.push(addr.postcode);

        return parts.join(' ');
    }

    /**
     * set(province, district, subdistrict)
     * Supports both IDs and Names
     */
    async set(p, d, s) {
        if (!p && !d && !s) return;
        return this.setValues({ province: p, district: d, subdistrict: s });
    }

    /**
     * Set values programmatically
     * @param {Object} values - { province, district, subdistrict } (Can be ID or Name)
     */
    async setValues(values) {
        const setSelectValue = ($el, val) => {
            if (!val) return false;

            // Try ID first
            $el.val(val);
            if ($el.val() === val) {
                if (this.config.useSelect2) $el.trigger('change.select2');
                $el.trigger('change');
                return true;
            }

            // Try Name matching
            let foundVal = null;
            const cleanPrefix = (s) => s.replace(/^(จ\.|อ\.|ต\.|จังหวัด|อำเภอ|ตำบล|เขต|แขวง)\s*/, '').trim();
            const targetClean = cleanPrefix(val);

            $el.find('option').each(function () {
                const optText = $(this).text().trim();
                // Match exact or cleaned name
                if (optText === val.trim() || (targetClean && cleanPrefix(optText) === targetClean)) {
                    foundVal = $(this).val();
                    return false;
                }
            });

            if (foundVal) {
                $el.val(foundVal);
                if (this.config.useSelect2) $el.trigger('change.select2');
                $el.trigger('change');
                return true;
            }
            return false;
        };

        const waitForOptions = ($el) => {
            return new Promise((resolve) => {
                let attempts = 0;
                const check = () => {
                    if ($el.find('option').length > 1 || attempts > 20) {
                        resolve();
                    } else {
                        attempts++;
                        setTimeout(check, 100);
                    }
                };
                check();
            });
        };

        const $province = $(this.config.provinceSelector);
        await waitForOptions($province);
        if (setSelectValue($province, values.province)) {
            const $district = $(this.config.districtSelector);
            if ($district.length) {
                await waitForOptions($district);
                if (setSelectValue($district, values.district)) {
                    const $subdistrict = $(this.config.subdistrictSelector);
                    if ($subdistrict.length) {
                        await waitForOptions($subdistrict);
                        setSelectValue($subdistrict, values.subdistrict);
                    }
                }
            }
        }
    }

    /**
     * Search addresses
     * @param {string} keyword
     * @param {number} limit
     * @returns {Promise}
     */
    search(keyword, limit = 20) {
        return $.get(`${this.config.apiPath}?action=search&q=${encodeURIComponent(keyword)}&limit=${limit}`);
    }

    static init(options = {}) {
        return new AutoProvince(options);
    }
}

// Export to global scope
window.AutoProvince = AutoProvince;

// Support legacy static calls
window.AutoProvince.init = (options) => new AutoProvince(options);

// Auto-init if data attribute present
$(document).ready(function () {
    if ($('[data-autoprovince]').length) {
        AutoProvince.init();
    }
});
