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

const AutoProvince = (function () {
    'use strict';

    // Default configuration
    const config = {
        apiPath: '/appdev/edonation/shared/autoprovince/api.php',
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

    let isInitialized = false;

    /**
     * Initialize AutoProvince
     * @param {Object} options - Configuration options
     */
    function init(options = {}) {
        if (isInitialized) {
            console.warn('AutoProvince already initialized');
            return;
        }

        // Merge options
        Object.assign(config, options);

        // Wait for DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setup);
        } else {
            setup();
        }
    }

    function setup() {
        const $province = $(config.provinceSelector);
        const $district = $(config.districtSelector);
        const $subdistrict = $(config.subdistrictSelector);

        if (!$province.length) {
            console.error('AutoProvince: Province selector not found');
            return;
        }

        // Initialize Select2 if enabled
        if (config.useSelect2 && typeof $.fn.select2 !== 'undefined') {
            initSelect2($province, config.placeholders.province);
            if ($district.length) initSelect2($district, config.placeholders.district);
            if ($subdistrict.length) initSelect2($subdistrict, config.placeholders.subdistrict);
        }

        // Load provinces
        loadProvinces();

        // Bind events
        $province.on('change', handleProvinceChange);
        if ($district.length) $district.on('change', handleDistrictChange);
        if ($subdistrict.length) $subdistrict.on('change', handleSubdistrictChange);

        isInitialized = true;
        console.log('AutoProvince initialized');
    }

    function initSelect2($element, placeholder) {
        $element.select2({
            theme: config.select2Theme,
            width: '100%',
            allowClear: true,
            placeholder: placeholder
        });
    }

    function toggleLoader(show) {
        const $loader = $(config.loaderSelector);
        if ($loader.length) {
            if (show) $loader.css('display', 'flex');
            else $loader.fadeOut(200);
        }
    }

    function loadProvinces() {
        toggleLoader(true);

        $.get(`${config.apiPath}?action=get_provinces`)
            .done(function (response) {
                if (response.status === 'success') {
                    const $province = $(config.provinceSelector);
                    $province.empty().append('<option value=""></option>');

                    response.data.forEach(function (item) {
                        $province.append(new Option(item.name, item.id, false, false));
                    });

                    if (config.useSelect2) {
                        $province.trigger('change.select2');
                    }
                }
            })
            .fail(function (xhr) {
                console.error('Failed to load provinces:', xhr.responseText);
            })
            .always(function () {
                toggleLoader(false);
            });
    }

    function handleProvinceChange() {
        const provinceId = $(this).val();
        const $district = $(config.districtSelector);
        const $subdistrict = $(config.subdistrictSelector);
        const $postcode = $(config.postcodeSelector);

        // Reset dependent fields
        resetSelect($district, config.placeholders.district);
        resetSelect($subdistrict, config.placeholders.subdistrict);
        if ($postcode.length) $postcode.val('');

        if (provinceId) {
            fetchData('get_districts', { province_id: provinceId }, config.districtSelector);
        }

        // Callback
        if (typeof config.onProvinceChange === 'function') {
            const selectedText = $(this).find('option:selected').text();
            config.onProvinceChange({ id: provinceId, name: selectedText });
        }
    }

    function handleDistrictChange() {
        const districtId = $(this).val();
        const $subdistrict = $(config.subdistrictSelector);
        const $postcode = $(config.postcodeSelector);

        // Reset dependent fields
        resetSelect($subdistrict, config.placeholders.subdistrict);
        if ($postcode.length) $postcode.val('');

        if (districtId) {
            fetchData('get_subdistricts', { district_id: districtId }, config.subdistrictSelector);
        }

        // Callback
        if (typeof config.onDistrictChange === 'function') {
            const selectedText = $(this).find('option:selected').text();
            config.onDistrictChange({ id: districtId, name: selectedText });
        }
    }

    function handleSubdistrictChange() {
        const $postcode = $(config.postcodeSelector);

        if (config.useSelect2) {
            const selectedData = $(this).select2('data')[0];
            if (selectedData && selectedData.element) {
                const postcode = $(selectedData.element).data('postcode');
                if (postcode && postcode !== '0' && $postcode.length) {
                    $postcode.val(postcode);
                }
            }
        } else {
            const postcode = $(this).find('option:selected').data('postcode');
            if (postcode && $postcode.length) {
                $postcode.val(postcode);
            }
        }

        // Callback
        if (typeof config.onSubdistrictChange === 'function') {
            const selectedText = $(this).find('option:selected').text();
            const postcode = $(this).find('option:selected').data('postcode');
            config.onSubdistrictChange({
                id: $(this).val(),
                name: selectedText,
                postcode: postcode
            });
        }

        // Complete callback
        if (typeof config.onAddressComplete === 'function') {
            const address = getSelectedAddress();
            config.onAddressComplete(address);
        }
    }

    function fetchData(action, params, targetSelector) {
        toggleLoader(true);

        $.ajax({
            url: `${config.apiPath}?action=${action}`,
            type: 'POST',
            data: params,
            dataType: 'json'
        })
            .done(function (response) {
                if (response.status === 'success') {
                    const $target = $(targetSelector);
                    $target.prop('disabled', false);

                    response.data.forEach(function (item) {
                        const option = new Option(item.name, item.id, false, false);
                        if (item.postcode) $(option).data('postcode', item.postcode);
                        $target.append(option);
                    });

                    if (config.useSelect2) {
                        $target.trigger('change.select2');
                    }
                }
            })
            .fail(function (xhr) {
                console.error(`Error fetching ${action}:`, xhr.responseText);
            })
            .always(function () {
                toggleLoader(false);
            });
    }

    function resetSelect(selector, placeholder) {
        const $el = $(selector);
        if (!$el.length) return;

        $el.empty()
            .append('<option value=""></option>')
            .prop('disabled', true);

        if (config.useSelect2) {
            $el.trigger('change.select2');
        }
    }

    /**
     * Get currently selected address
     * @returns {Object} Selected address parts
     */
    function getSelectedAddress() {
        const $province = $(config.provinceSelector);
        const $district = $(config.districtSelector);
        const $subdistrict = $(config.subdistrictSelector);
        const $postcode = $(config.postcodeSelector);

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

    /**
     * Format address as string
     * @returns {string} Formatted address
     */
    function formatAddress() {
        const addr = getSelectedAddress();
        if (!addr.province.id) return '';

        let parts = [];
        if (addr.subdistrict.id) parts.push('ต.' + addr.subdistrict.name);
        if (addr.district.id) parts.push('อ.' + addr.district.name);
        if (addr.province.id) parts.push('จ.' + addr.province.name);
        if (addr.postcode) parts.push(addr.postcode);

        return parts.join(' ');
    }

    /**
     * Set values programmatically
     * @param {Object} values - { provinceId, districtId, subdistrictId }
     */
    function setValues(values) {
        return new Promise(function (resolve) {
            const $province = $(config.provinceSelector);

            if (values.provinceId) {
                $province.val(values.provinceId);
                if (config.useSelect2) $province.trigger('change.select2');
                $province.trigger('change');

                // Wait for districts to load
                setTimeout(function () {
                    if (values.districtId) {
                        const $district = $(config.districtSelector);
                        $district.val(values.districtId);
                        if (config.useSelect2) $district.trigger('change.select2');
                        $district.trigger('change');

                        // Wait for subdistricts to load
                        setTimeout(function () {
                            if (values.subdistrictId) {
                                const $subdistrict = $(config.subdistrictSelector);
                                $subdistrict.val(values.subdistrictId);
                                if (config.useSelect2) $subdistrict.trigger('change.select2');
                                $subdistrict.trigger('change');
                            }
                            resolve();
                        }, 500);
                    } else {
                        resolve();
                    }
                }, 500);
            } else {
                resolve();
            }
        });
    }

    /**
     * Search addresses
     * @param {string} keyword
     * @param {number} limit
     * @returns {Promise}
     */
    function search(keyword, limit = 20) {
        return $.get(`${config.apiPath}?action=search&q=${encodeURIComponent(keyword)}&limit=${limit}`);
    }

    // Public API
    return {
        init: init,
        getAddress: getSelectedAddress,
        formatAddress: formatAddress,
        setValues: setValues,
        search: search,
        reload: loadProvinces
    };
})();

// Auto-init if data attribute present
$(document).ready(function () {
    if ($('[data-autoprovince]').length) {
        AutoProvince.init();
    }
});
