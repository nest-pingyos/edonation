<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="index.php" class="logo-dark">
            <img
                src="assets/images/logo-sm.png"
                class="logo-sm"
                alt="logo sm"
            />
            <img
                src="assets/images/logo-dark.png"
                class="logo-lg"
                alt="logo dark"
            />
        </a>

        <a href="index.php" class="logo-light">
            <img
                src="assets/images/logo-sm.png"
                class="logo-sm"
                alt="logo sm"
            />
            <img
                src="assets/images/logo-light.png"
                class="logo-lg"
                alt="logo light"
            />
        </a>
    </div>

    <!-- Menu Toggle Button (sm-hover) -->
    <button
        type="button"
        class="button-sm-hover"
        aria-label="Show Full Sidebar"
    >
        <iconify-icon
            icon="iconamoon:arrow-left-4-square-duotone"
            class="button-sm-hover-icon"
        ></iconify-icon>
    </button>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">
            <li class="menu-title">General</li>

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarDashboards"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarDashboards"
                >
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:home-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Dashboards </span>
                </a>
                <div class="collapse" id="sidebarDashboards">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="index.php"
                                >Analytics</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="dashboard-finance.php"
                                >Finance</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="dashboard-sales.php"
                                >Sales</a
                            >
                        </li>
                    </ul>
                </div>
            </li>

            <li class="menu-title">Apps</li>

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarEcommerce"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarEcommerce"
                >
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:shopping-bag-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Ecommerce </span>
                </a>
                <div class="collapse" id="sidebarEcommerce">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="apps-ecommerce-product-list.php"
                                >Products</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="apps-ecommerce-product-detail.php"
                                >Product Details</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="apps-ecommerce-product-add.php"
                                >Create Product</a
                            >
                        </li>

                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="apps-ecommerce-customer-list.php"
                                >Customers</a
                            >
                        </li>

                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="apps-ecommerce-seller-list.php"
                                >Sellers</a
                            >
                        </li>

                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="apps-ecommerce-order-list.php"
                                >Orders</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="apps-ecommerce-order-detail.php"
                                >Order Details</a
                            >
                        </li>

                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="apps-ecommerce-inventory.php"
                                >Inventory</a
                            >
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="apps-chat.php">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:comment-dots-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Chat </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="apps-email.php">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:email-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Email </span>
                </a>
            </li>

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarCalendar"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarCalendar"
                >
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:calendar-1-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Calendar </span>
                </a>
                <div class="collapse" id="sidebarCalendar">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="apps-calendar-schedule.php"
                                >Schedule</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="apps-calendar-integration.php"
                                >Integration</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="apps-calendar-help.php"
                                >Help</a
                            >
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="apps-todo.php">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:ticket-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Todo </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="apps-social.php">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:squinting-face-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Social </span>
                    <span class="badge badge-pill text-end bg-danger">Hot</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="apps-contacts.php">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:profile-circle-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Contacts </span>
                </a>
            </li>

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarInvoice"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarInvoice"
                >
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:invoice-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Invoices </span>
                </a>
                <div class="collapse" id="sidebarInvoice">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="apps-invoices.php"
                                >Invoices</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="apps-invoice-details.php"
                                >Invoice Details</a
                            >
                        </li>
                    </ul>
                </div>
            </li>

            <li class="menu-title">Custom</li>

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarPages"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarPages"
                >
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:copy-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Pages </span>
                </a>
                <div class="collapse" id="sidebarPages">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="pages-starter.php"
                                >Welcome</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="pages-faqs.php"
                                >FAQs</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="pages-profile.php"
                                >Profile</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="pages-comingsoon.php"
                                >Coming Soon</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="pages-contact-us.php"
                                >Contact Us</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="pages-about-us.php"
                                >About Us</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="pages-team.php"
                                >Our Team</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="pages-timeline.php"
                                >Timeline</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="pages-pricing.php"
                                >Pricing</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="pages-maintenance.php"
                                >Maintenance</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="pages-404.php"
                                >404 Error</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="pages-404-2.php"
                                >404 Error 2</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="pages-404-alt.php"
                                >404 Error (alt)</a
                            >
                        </li>
                    </ul>
                </div>
            </li>
            <!-- end Pages Menu -->

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarAuthentication"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarAuthentication"
                >
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:lock-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Authentication </span>
                </a>
                <div class="collapse" id="sidebarAuthentication">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="auth-signin.php"
                                >Sign In</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="auth-signin2.php"
                                >Sign In 2</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="auth-signup.php"
                                >Sign Up</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="auth-signup2.php"
                                >Sign Up 2</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="auth-password.php"
                                >Reset Password</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="auth-password2.php"
                                >Reset Password 2</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="auth-lock-screen.php"
                                >Lock Screen</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="auth-lock-screen2.php"
                                >Lock Screen 2</a
                            >
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="widgets.php">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:gift-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text">Widgets</span>
                    <span class="badge bg-info badge-pill text-end">9+</span>
                </a>
            </li>
            <!-- end Demo Menu Item -->

            <li class="menu-title">Components</li>

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarBaseUI"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarBaseUI"
                >
                    <span class="nav-icon"
                        ><iconify-icon
                            icon="iconamoon:briefcase-duotone"
                        ></iconify-icon
                    ></span>
                    <span class="nav-text"> Base UI </span>
                </a>
                <div class="collapse" id="sidebarBaseUI">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-accordion.php"
                                >Accordion</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-alerts.php"
                                >Alerts</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-avatar.php"
                                >Avatar</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-badge.php"
                                >Badge</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-breadcrumb.php"
                                >Breadcrumb</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-buttons.php"
                                >Buttons</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-card.php">Card</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-carousel.php"
                                >Carousel</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-collapse.php"
                                >Collapse</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-dropdown.php"
                                >Dropdown</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-list-group.php"
                                >List Group</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-modal.php"
                                >Modal</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-tabs.php">Tabs</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-offcanvas.php"
                                >Offcanvas</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-pagination.php"
                                >Pagination</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-placeholders.php"
                                >Placeholders</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-popovers.php"
                                >Popovers</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-progress.php"
                                >Progress</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-scrollspy.php"
                                >Scrollspy</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-spinners.php"
                                >Spinners</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-toasts.php"
                                >Toasts</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="ui-tooltips.php"
                                >Tooltips</a
                            >
                        </li>
                    </ul>
                </div>
            </li>
            <!-- end Base UI Menu -->

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarExtendedUI"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarExtendedUI"
                >
                    <span class="nav-icon"
                        ><iconify-icon
                            icon="iconamoon:component-duotone"
                        ></iconify-icon
                    ></span>
                    <span class="nav-text"> Advanced UI </span>
                </a>
                <div class="collapse" id="sidebarExtendedUI">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="extended-ratings.php"
                                >Ratings</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="extended-sweetalert.php"
                                >Sweet Alert</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="extended-swiper-silder.php"
                                >Swiper Slider</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="extended-scrollbar.php"
                                >Scrollbar</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="extended-toastify.php"
                                >Toastify</a
                            >
                        </li>
                    </ul>
                </div>
            </li>
            <!-- end Extended UI Menu -->

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarCharts"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarCharts"
                >
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:3d-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Charts </span>
                </a>
                <div class="collapse" id="sidebarCharts">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="charts-apex-area.php"
                                >Area</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="charts-apex-bar.php"
                                >Bar</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="charts-apex-bubble.php"
                                >Bubble</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="charts-apex-candlestick.php"
                                >Candlestick</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="charts-apex-column.php"
                                >Column</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="charts-apex-heatmap.php"
                                >Heatmap</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="charts-apex-line.php"
                                >Line</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="charts-apex-mixed.php"
                                >Mixed</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="charts-apex-timeline.php"
                                >Timeline</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="charts-apex-boxplot.php"
                                >Boxplot</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="charts-apex-treemap.php"
                                >Treemap</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="charts-apex-pie.php"
                                >Pie</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="charts-apex-radar.php"
                                >Radar</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="charts-apex-radialbar.php"
                                >RadialBar</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="charts-apex-scatter.php"
                                >Scatter</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="charts-apex-polar-area.php"
                                >Polar Area</a
                            >
                        </li>
                    </ul>
                </div>
            </li>
            <!-- end Chart library Menu -->

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarForms"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarForms"
                >
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:cheque-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Forms </span>
                </a>
                <div class="collapse" id="sidebarForms">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="forms-basic.php"
                                >Basic Elements</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="forms-checkbox-radio.php"
                                >Checkbox &amp; Radio</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="forms-choices.php"
                                >Choice Select</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="forms-clipboard.php"
                                >Clipboard</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="forms-flatepicker.php"
                                >Flatepicker</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="forms-validation.php"
                                >Validation</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="forms-wizard.php"
                                >Wizard</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="forms-fileuploads.php"
                                >File Upload</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="forms-editors.php"
                                >Editors</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="forms-input-mask.php"
                                >Input Mask</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link"
                                href="forms-range-slider.php"
                                >Slider</a
                            >
                        </li>
                    </ul>
                </div>
            </li>
            <!-- end Form Menu -->

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarTables"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarTables"
                >
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:box-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Tables </span>
                </a>
                <div class="collapse" id="sidebarTables">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="tables-basic.php"
                                >Basic Tables</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="tables-gridjs.php"
                                >Grid Js</a
                            >
                        </li>
                    </ul>
                </div>
            </li>
            <!-- end Table Menu -->

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarIcons"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarIcons"
                >
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:lightning-1-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Icons </span>
                </a>
                <div class="collapse" id="sidebarIcons">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="icons-boxicons.php"
                                >Boxicons</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="icons-iconamoon.php"
                                >IconaMoon Icons</a
                            >
                        </li>
                    </ul>
                </div>
            </li>
            <!-- end Icons library Menu -->

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarMaps"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarMaps"
                >
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:location-pin-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Maps </span>
                </a>
                <div class="collapse" id="sidebarMaps">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="maps-google.php"
                                >Google Maps</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="maps-vector.php"
                                >Vector Maps</a
                            >
                        </li>
                    </ul>
                </div>
            </li>
            <!-- end Map Menu -->

            <li class="nav-item">
                <a class="nav-link" href="javascript:void(0);">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:badge-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text">Badge Menu</span>
                    <span class="badge bg-danger badge-pill text-end">1</span>
                </a>
            </li>
            <!-- end Demo Menu Item -->

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarMultiLevelDemo"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarMultiLevelDemo"
                >
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:folder-add-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Menu Item </span>
                </a>
                <div class="collapse" id="sidebarMultiLevelDemo">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="javascript:void(0);"
                                >Menu Item 1</a
                            >
                        </li>
                        <li class="sub-nav-item">
                            <a
                                class="sub-nav-link menu-arrow"
                                href="#sidebarItemDemoSubItem"
                                data-bs-toggle="collapse"
                                role="button"
                                aria-expanded="false"
                                aria-controls="sidebarItemDemoSubItem"
                            >
                                <span> Menu Item 2 </span>
                            </a>
                            <div class="collapse" id="sidebarItemDemoSubItem">
                                <ul class="nav sub-navbar-nav">
                                    <li class="sub-nav-item">
                                        <a
                                            class="sub-nav-link"
                                            href="javascript:void(0);"
                                            >Menu Sub item</a
                                        >
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </li>
            <!-- end Demo Menu Item -->

            <li class="nav-item">
                <a class="nav-link disabled" href="javascript:void(0);">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:unavailable-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Disabled Item </span>
                </a>
            </li>
            <!-- end Demo Menu Item -->
        </ul>
    </div>
</div>
