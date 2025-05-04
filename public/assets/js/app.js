/*
Template Name: Shreyu - Responsive Bootstrap 5 Admin Dashboard
Author: CoderThemes
Website: https://coderthemes.com/
Contact: support@coderthemes.com
File: Main Js File
*/



class App {

    // Bootstrap Components
    initComponents() {

        // Feather Icons
        feather.replace()

        // Lucide Icons
        lucide.createIcons();

        // loader - Preloader
        $(window).on('load', function () {
            $('#status').fadeOut();
            $('#preloader').delay(350).fadeOut('slow');
        });

        // Popovers
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

        // Tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

        // offcanvas
        const offcanvasElementList = document.querySelectorAll('.offcanvas')
        const offcanvasList = [...offcanvasElementList].map(offcanvasEl => new bootstrap.Offcanvas(offcanvasEl))

        //Toasts
        var toastPlacement = document.getElementById("toastPlacement");
        if (toastPlacement) {
            document.getElementById("selectToastPlacement").addEventListener("change", function () {
                if (!toastPlacement.dataset.originalClass) {
                    toastPlacement.dataset.originalClass = toastPlacement.className;
                }
                toastPlacement.className = toastPlacement.dataset.originalClass + " " + this.value;
            });
        }

        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
        var toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl)
        })

        // Bootstrap Alert Live Example
        const alertPlaceholder = document.getElementById('liveAlertPlaceholder')
        const alert = (message, type) => {
            const wrapper = document.createElement('div')
            wrapper.innerHTML = [
                `<div class="alert alert-${type} alert-dismissible" role="alert">`,
                `   <div>${message}</div>`,
                '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                '</div>'
            ].join('')

            alertPlaceholder.append(wrapper)
        }

        const alertTrigger = document.getElementById('liveAlertBtn')
        if (alertTrigger) {
            alertTrigger.addEventListener('click', () => {
                alert('Nice, you triggered this alert message!', 'success')
            })
        }

        // RTL Layout
        // if (document.getElementById('app-style').href.includes('rtl.min.css')) {
        //     document.getElementsByTagName('html')[0].dir = "rtl";
        // }
    }

    // Portlet Widget (Card Reload, Collapse, and Delete)
    initPortletCard() {

        var portletIdentifier = ".card"
        var portletCloser = '.card a[data-bs-toggle="remove"]'
        var portletRefresher = '.card a[data-bs-toggle="reload"]'
        let self = this

        // Panel closest
        $(document).on("click", portletCloser, function (ev) {
            ev.preventDefault();
            var $portlet = $(this).closest(portletIdentifier);
            var $portlet_parent = $portlet.parent();
            $portlet.remove();
            if ($portlet_parent.children().length == 0) {
                $portlet_parent.remove();
            }
        });

        // Panel Reload
        $(document).on("click", portletRefresher, function (ev) {
            ev.preventDefault();
            var $portlet = $(this).closest(portletIdentifier);
            // This is just a simulation, nothing is going to be reloaded
            $portlet.append('<div class="card-disabled"><div class="card-portlets-loader"></div></div>');
            var $pd = $portlet.find('.card-disabled');
            setTimeout(function () {
                $pd.fadeOut('fast', function () {
                    $pd.remove();
                });
            }, 500 + 300 * (Math.random() * 5));
        });
    }

    //  Multi Dropdown
    initMultiDropdown() {
        $('.dropdown-menu a.dropdown-toggle').on('click', function () {
            var dropdown = $(this).next('.dropdown-menu');
            var otherDropdown = $(this).parent().parent().find('.dropdown-menu').not(dropdown);
            otherDropdown.removeClass('show')
            otherDropdown.parent().find('.dropdown-toggle').removeClass('show')
            return false;
        });
    }

    // Topbar Fullscreen Button
    initfullScreenListener() {
        var self = this;
        var fullScreenBtn = document.querySelector('[data-toggle="fullscreen"]');

        if (fullScreenBtn) {
            fullScreenBtn.addEventListener('click', function (e) {
                e.preventDefault();
                document.body.classList.toggle('fullscreen-enable')
                if (!document.fullscreenElement && /* alternative standard method */ !document.mozFullScreenElement && !document.webkitFullscreenElement) {  // current working methods
                    if (document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen();
                    } else if (document.documentElement.mozRequestFullScreen) {
                        document.documentElement.mozRequestFullScreen();
                    } else if (document.documentElement.webkitRequestFullscreen) {
                        document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                    }
                } else {
                    if (document.cancelFullScreen) {
                        document.cancelFullScreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.webkitCancelFullScreen) {
                        document.webkitCancelFullScreen();
                    }
                }
            });
        }
    }

    // Form Validation
    initFormValidation() {
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        // Loop over them and prevent submission
        document.querySelectorAll('.needs-validation').forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
    }

    // Form Advance
    initFormAdvance() {
        // Select2
        if (jQuery().select2) {
            $('[data-toggle="select2"]').select2();
        }
    }

    // custom modal
    initCustomModalPlugin() {
        $('[data-plugin="custommodal"]').on('click', function (e) {
            e.preventDefault();
            var modal = new Custombox.modal({
                content: {
                    target: $(this).attr("href"),
                    effect: $(this).attr("data-animation")
                },
                overlay: {
                    color: $(this).attr("data-overlayColor")
                }
            });

            // Open
            modal.open();
        });
    }

    // Counter up
    initCounterUp() {
        var delay = $(this).attr('data-delay') ? $(this).attr('data-delay') : 100; //default is 100
        var time = $(this).attr('data-time') ? $(this).attr('data-time') : 1200; //default is 1200
        $('[data-plugin="counterup"]').each(function (idx, obj) {
            $(this).counterUp({
                delay: 100,
                time: 1200
            });
        });
    }

    // peity charts
    initPeityCharts() {
        $('[data-plugin="peity-pie"]').each(function (idx, obj) {
            var colors = $(this).attr('data-colors') ? $(this).attr('data-colors').split(",") : [];
            var width = $(this).attr('data-width') ? $(this).attr('data-width') : 20; //default is 20
            var height = $(this).attr('data-height') ? $(this).attr('data-height') : 20; //default is 20
            $(this).peity("pie", {
                fill: colors,
                width: width,
                height: height
            });
        });

        // donut
        $('[data-plugin="peity-donut"]').each(function (idx, obj) {
            var colors = $(this).attr('data-colors') ? $(this).attr('data-colors').split(",") : [];
            var width = $(this).attr('data-width') ? $(this).attr('data-width') : 20; //default is 20
            var height = $(this).attr('data-height') ? $(this).attr('data-height') : 20; //default is 20
            $(this).peity("donut", {
                fill: colors,
                width: width,
                height: height
            });
        });

        $('[data-plugin="peity-donut-alt"]').each(function (idx, obj) {
            $(this).peity("donut");
        });

        // line
        $('[data-plugin="peity-line"]').each(function (idx, obj) {
            $(this).peity("line", $(this).data());
        });

        // bar
        $('[data-plugin="peity-bar"]').each(function (idx, obj) {
            var colors = $(this).attr('data-colors') ? $(this).attr('data-colors').split(",") : [];
            var width = $(this).attr('data-width') ? $(this).attr('data-width') : 20; //default is 20
            var height = $(this).attr('data-height') ? $(this).attr('data-height') : 20; //default is 20
            $(this).peity("bar", {
                fill: colors,
                width: width,
                height: height
            });
        });
    }

    initKnob() {
        $('[data-plugin="knob"]').each(function (idx, obj) {
            $(this).knob();
        });
    }


    init() {
        this.initComponents();
        this.initPortletCard();
        this.initMultiDropdown();
        this.initfullScreenListener();
        this.initFormValidation();
        this.initFormAdvance();
        this.initCustomModalPlugin()
        this.initCounterUp()
        this.initPeityCharts();
        this.initKnob();
    }
}

class ThemeCustomizer {

    constructor() {
        this.html = document.getElementsByTagName('html')[0]
        this.config = {};
        this.defaultConfig = window.config;
    }

    // Left Sidebar Menu (Vertical Menu)
    initLeftSidebar() {
        var self = this;

        if ($(".side-menu").length) {
            var navCollapse = $('.side-menu li .collapse');
            var navToggle = $(".side-menu li [data-bs-toggle='collapse']");
            navToggle.on('click', function (e) {
                return false;
            });

            // open one menu at a time only
            navCollapse.on({
                'show.bs.collapse': function (event) {
                    var parent = $(event.target).parents('.collapse.show');
                    $('.side-menu .collapse.show').not(event.target).not(parent).collapse('hide');
                }
            });

            // activate the menu in left side bar (Vertical Menu) based on url
            $(".side-menu a").each(function () {
                var pageUrl = window.location.href.split(/[?#]/)[0];
                if (this.href == pageUrl) {
                    $(this).addClass("active");
                    $(this).parent().addClass("active");
                    $(this).parent().parent().parent().addClass("show");
                    $(this).parent().parent().parent().parent().addClass("active"); // add active to li of the current link

                    var firstLevelParent = $(this).parent().parent().parent().parent().parent().parent();
                    if (firstLevelParent.attr('id') !== 'sidebar-menu') firstLevelParent.addClass("show");

                    $(this).parent().parent().parent().parent().parent().parent().parent().addClass("active");

                    var secondLevelParent = $(this).parent().parent().parent().parent().parent().parent().parent().parent().parent();
                    if (secondLevelParent.attr('id') !== 'wrapper') secondLevelParent.addClass("show");

                    var upperLevelParent = $(this).parent().parent().parent().parent().parent().parent().parent().parent().parent().parent();
                    if (!upperLevelParent.is('body')) upperLevelParent.addClass("active");
                }
            });


            setTimeout(function () {
                var activatedItem = document.querySelector('li.active .active');
                if (activatedItem != null) {
                    var simplebarContent = document.querySelector('.left-side-menu .simplebar-content-wrapper');
                    var offset = activatedItem.offsetTop - 300;
                    if (simplebarContent && offset > 100) {
                        scrollTo(simplebarContent, offset, 600);
                    }
                }
            }, 200);

            // scrollTo (Left Side Bar Active Menu)
            function easeInOutQuad(t, b, c, d) {
                t /= d / 2;
                if (t < 1) return c / 2 * t * t + b;
                t--;
                return -c / 2 * (t * (t - 2) - 1) + b;
            }
            function scrollTo(element, to, duration) {
                var start = element.scrollTop, change = to - start, currentTime = 0, increment = 20;
                var animateScroll = function () {
                    currentTime += increment;
                    var val = easeInOutQuad(currentTime, start, change, duration);
                    element.scrollTop = val;
                    if (currentTime < duration) {
                        setTimeout(animateScroll, increment);
                    }
                };
                animateScroll();
            }
        }


        // handling two columns menu if present
        var twoColSideNav = $("#two-col-sidenav-main");
        if (twoColSideNav.length) {

            var twoColSideNavItems = $("#two-col-sidenav-main .nav-link");
            var sideSubMenus = $(".twocolumn-menu-item");

            var nav = $('.twocolumn-menu-item .nav-second-level');
            var navCollapse = $('#two-col-menu menu-item .collapse');

            // open one menu at a time only
            navCollapse.on({
                'show.bs.collapse': function () {
                    var nearestNav = $(this).closest(nav).closest(nav).find(navCollapse);
                    if (nearestNav.length) nearestNav.not($(this)).collapse('hide'); else navCollapse.not($(this)).collapse('hide');
                }
            });

            twoColSideNavItems.on('click', function (e) {
                var target = $($(this).attr('href'));

                if (target.length) {
                    e.preventDefault();
                    twoColSideNavItems.removeClass('active');
                    $(this).addClass('active');
                    sideSubMenus.removeClass("d-block");
                    target.addClass("d-block");
                    if (window.innerWidth >= 1040) {
                        // self.changeLeftbarSize("default");
                        html.setAttribute("data-sidebar-size", "default");
                    }
                }
                return true;
            });

            // activate menu with no child
            var pageUrl = window.location.href; //.split(/[?#]/)[0];
            twoColSideNavItems.each(function () {
                if (this.href === pageUrl) {
                    $(this).addClass('active');
                }
            });


            // activate the menu in left side bar (Two column) based on url
            $("#two-col-menu a").each(function () {
                if (this.href == pageUrl) {
                    $(this).addClass("active");
                    $(this).parent().addClass("menuitem-active");
                    $(this).parent().parent().parent().addClass("show");
                    $(this).parent().parent().parent().parent().addClass("menuitem-active"); // add active to li of the current link

                    var firstLevelParent = $(this).parent().parent().parent().parent().parent().parent();
                    if (firstLevelParent.attr('id') !== 'sidebar-menu') firstLevelParent.addClass("show");

                    $(this).parent().parent().parent().parent().parent().parent().parent().addClass("menuitem-active");

                    var secondLevelParent = $(this).parent().parent().parent().parent().parent().parent().parent().parent().parent();
                    if (secondLevelParent.attr('id') !== 'wrapper') secondLevelParent.addClass("show");

                    var upperLevelParent = $(this).parent().parent().parent().parent().parent().parent().parent().parent().parent().parent();
                    if (!upperLevelParent.is('body')) upperLevelParent.addClass("menuitem-active");

                    // opening menu
                    var matchingItem = null;
                    var targetEl = '#' + $(this).parents('.twocolumn-menu-item').attr("id");
                    $("#two-col-sidenav-main .nav-link").each(function () {
                        if ($(this).attr('href') === targetEl) {
                            matchingItem = $(this);
                        }
                    });
                    if (matchingItem) matchingItem.trigger('click');
                }
            });
        }
    }

    // Topbar Menu (Horizontal Menu)
    initTopbarMenu() {
        if ($('.navbar-nav').length) {
            $('.navbar-nav li a').each(function () {
                var pageUrl = window.location.href.split(/[?#]/)[0];
                if (this.href == pageUrl) {
                    $(this).addClass('active');
                    $(this).parent().parent().addClass('active'); // add active to li of the current link
                    $(this).parent().parent().parent().parent().addClass('active');
                    $(this).parent().parent().parent().parent().parent().parent().addClass('active');
                }
            });

            // Topbar - main menu
            $('.navbar-toggle').on('click', function () {
                $(this).toggleClass('open');
                $('#navigation').slideToggle(400);
            });
        }
    }

    initConfig() {
        this.defaultConfig = JSON.parse(JSON.stringify(window.defaultConfig));
        this.config = JSON.parse(JSON.stringify(window.config));
        this.setSwitchFromConfig();
    }

    changeMenuColor(color) {
        this.config.menu.color = color;
        this.html.setAttribute('data-menu-color', color);
        this.setSwitchFromConfig();
    }

    changeLeftbarSize(size, save = true) {
        this.html.setAttribute('data-sidebar-size', size);
        if (save) {
            this.config.sidenav.size = size;
            this.setSwitchFromConfig();
        }
    }

    changeLayoutWidth(width, save = true) {
        this.html.setAttribute('data-layout-width', width);
        if (save) {
            this.config.layout.width = width;
            this.setSwitchFromConfig();
        }
    }

    changeLayoutPosition(position, save = true) {
        this.html.setAttribute('data-layout-position', position);
        if (save) {
            this.config.layout.position = position;
            this.setSwitchFromConfig();
        }
    }

    changeLayoutColor(color) {
        this.config.theme = color;
        this.html.setAttribute('data-bs-theme', color);
        this.setSwitchFromConfig();
    }

    changeTopbarColor(color) {
        this.config.topbar.color = color;
        this.html.setAttribute('data-topbar-color', color);
        this.setSwitchFromConfig();
    }

    changeSidebarUser(showUser) {

        this.config.sidenav.user = showUser;
        if (showUser) {
            this.html.setAttribute('data-sidebar-user', showUser);
        } else {
            this.html.removeAttribute('data-sidebar-user');
        }
        this.setSwitchFromConfig();
    }

    resetTheme() {
        this.config = JSON.parse(JSON.stringify(window.defaultConfig));
        this.changeMenuColor(this.config.menu.color);
        this.changeLeftbarSize(this.config.sidenav.size);
        this.changeLayoutColor(this.config.theme);
        this.changeLayoutWidth(this.config.layout.width);
        this.changeLayoutPosition(this.config.layout.position)
        this.changeTopbarColor(this.config.topbar.color);
        this.changeSidebarUser(this.config.sidenav.user);
        this._adjustLayout();
    }

    initSwitchListener() {
        var self = this;
        document.querySelectorAll('input[name=menu-color]').forEach(function (element) {
            element.addEventListener('change', function (e) {
                self.changeMenuColor(element.value);
            })
        });

        document.querySelectorAll('input[name=leftsidebar-size]').forEach(function (element) {
            element.addEventListener('change', function (e) {
                self.changeLeftbarSize(element.value);
            })
        });

        document.querySelectorAll('input[name=color-scheme-mode]').forEach(function (element) {
            element.addEventListener('change', function (e) {
                self.changeLayoutColor(element.value);
            })
        });

        document.querySelectorAll('input[name=layout-width]').forEach(function (element) {
            element.addEventListener('change', function (e) {
                self.changeLayoutWidth(element.value);
            })
        });

        document.querySelectorAll('input[name=menu-position]').forEach(function (element) {
            element.addEventListener('change', function (e) {
                self.changeLayoutPosition(element.value);
            })
        });

        document.querySelectorAll('input[name=topbar-color]').forEach(function (element) {
            element.addEventListener('change', function (e) {
                self.changeTopbarColor(element.value);
            })
        });

        document.querySelectorAll('input[name=leftsidebar-user]').forEach(function (element) {
            element.addEventListener('change', function (e) {
                self.changeSidebarUser(element.checked);
            })
        });

        // TopBar Light Dark
        var themeColorToggle = document.getElementById('light-dark-mode');
        if (themeColorToggle) {
            themeColorToggle.addEventListener('click', function (e) {

                if (self.config.theme === 'light') {
                    self.changeLayoutColor('dark');
                } else {
                    self.changeLayoutColor('light');
                }
            });
        }

        var resetBtn = document.querySelector('#resetBtn')
        if (resetBtn) {
            resetBtn.addEventListener('click', function (e) {
                self.resetTheme();
            });
        }

        var menuToggleBtn = document.querySelector('.button-menu-mobile');
        if (menuToggleBtn) {
            menuToggleBtn.addEventListener('click', function () {
                var configSize = self.config.sidenav.size;
                var size = self.html.getAttribute('data-sidebar-size', configSize);

                if (size === 'mobile') {
                    self.showBackdrop();
                } else {
                    if (configSize == 'fullscreen') {
                        if (size === 'fullscreen') {
                            self.changeLeftbarSize(configSize == 'fullscreen' ? 'default' : configSize, false);
                        } else {
                            self.changeLeftbarSize('fullscreen', false);
                        }
                    } else {
                        if (size === 'condensed') {
                            self.changeLeftbarSize(configSize == 'condensed' ? 'default' : configSize, false);
                        } else {
                            self.changeLeftbarSize('condensed', false);
                        }
                    }
                }

                self.html.classList.toggle('sidebar-enable');

            });
        }

        var menuCloseBtn = document.querySelector('.button-close-fullsidebar');
        if (menuCloseBtn) {
            menuCloseBtn.addEventListener('click', function () {
                self.html.classList.remove('sidebar-enable');
                self.hideBackdrop();
            });
        }

        var hoverBtn = document.querySelectorAll('.button-sm-hover');
        hoverBtn.forEach(function (element) {
            element.addEventListener('click', function () {
                var configSize = self.config.sidenav.size;
                var size = self.html.getAttribute('data-sidebar-size', configSize);

                if (size === 'sm-hover-active') {
                    self.changeLeftbarSize('sm-hover', false);
                } else {
                    self.changeLeftbarSize('sm-hover-active', false);
                }
            });
        })
    }

    showBackdrop() {
        const backdrop = document.createElement('div');
        backdrop.id = 'custom-backdrop';
        backdrop.classList = 'offcanvas-backdrop fade show';
        document.body.appendChild(backdrop);
        document.body.style.overflow = "hidden";
        if (window.innerWidth > 767) {
            document.body.style.paddingRight = "15px";
        }
        const self = this
        backdrop.addEventListener('click', function (e) {
            self.html.classList.remove('sidebar-enable');
            self.hideBackdrop();
        })
    }

    hideBackdrop() {
        var backdrop = document.getElementById('custom-backdrop');
        if (backdrop) {
            document.body.removeChild(backdrop);
            document.body.style.overflow = null;
            document.body.style.paddingRight = null;
        }
    }

    initWindowSize() {
        var self = this;
        window.addEventListener('resize', function (e) {
            self._adjustLayout();
        })
    }

    _adjustLayout() {
        var self = this;

        if (window.innerWidth <= 1199) {
            html.setAttribute("data-sidebar-size", "mobile");
        } else {
            html.setAttribute("data-sidebar-size", self.config.sidenav.size);
        }

    }

    setSwitchFromConfig() {

        sessionStorage.setItem('__SHREYU_CONFIG__', JSON.stringify(this.config));
        // localStorage.setItem('__SHREYU_CONFIG__', JSON.stringify(this.config));

        document.querySelectorAll('#theme-settings-offcanvas input[type=checkbox]').forEach(function (checkbox) {
            checkbox.checked = false;
        })

        var config = this.config;
        if (config) {
            var layoutColorSwitch = document.querySelector('input[type=checkbox][name=color-scheme-mode][value=' + config.theme + ']');
            var layoutWidthSwitch = document.querySelector('input[type=checkbox][name=layout-width][value=' + config.layout.width + ']');
            var layoutPositionSwitch = document.querySelector('input[type=checkbox][name=menu-position][value=' + config.layout.position + ']');
            var topbarColorSwitch = document.querySelector('input[type=checkbox][name=topbar-color][value=' + config.topbar.color + ']');
            var menuColorSwitch = document.querySelector('input[type=checkbox][name=menu-color][value=' + config.menu.color + ']');
            var leftbarSizeSwitch = document.querySelector('input[type=checkbox][name=leftsidebar-size][value=' + config.sidenav.size + ']');
            var sidebarUserSwitch = document.querySelector('input[type=checkbox][name=leftsidebar-user]');

            if (layoutColorSwitch) layoutColorSwitch.checked = true;
            if (layoutWidthSwitch) layoutWidthSwitch.checked = true;
            if (layoutPositionSwitch) layoutPositionSwitch.checked = true;
            if (topbarColorSwitch) topbarColorSwitch.checked = true;
            if (menuColorSwitch) menuColorSwitch.checked = true;
            if (leftbarSizeSwitch) leftbarSizeSwitch.checked = true;
            if (sidebarUserSwitch && config.sidenav.user.toString() === "true") sidebarUserSwitch.checked = true;
        }
    }

    init() {
        this.initLeftSidebar()
        this.initTopbarMenu();
        this.initConfig();
        this.initSwitchListener();
        this.initWindowSize();
        this._adjustLayout();
        this.setSwitchFromConfig();
    }
}

new App().init();
new ThemeCustomizer().init();

///////////////////////////////////////////////////////////////////////////////

// showing the sidebar on load if user is visiting the page first time only
// var bodyConfig = this.$body.data('layout');
// if (window.sessionStorage && bodyConfig && bodyConfig.hasOwnProperty('showRightSidebarOnPageLoad') && bodyConfig['showRightSidebarOnPageLoad']) {
//     var alreadyVisited = sessionStorage.getItem("_UBOLD_VISITED_");
//     if (!alreadyVisited) {
//         $.RightBar.toggleRightSideBar();
//         sessionStorage.setItem("_UBOLD_VISITED_", true);
//     }
// }