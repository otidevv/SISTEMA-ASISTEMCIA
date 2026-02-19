/**
* Theme: Shreyu - Responsive Admin & Dashboard Template
* Author: Coderthemes
* Module/App: Theme Config Js
*/

(function () {
    var savedConfig = sessionStorage.getItem("__SHREYU_CONFIG__");
    // var savedConfig = localStorage.getItem("__SHREYU_CONFIG__");

    var html = document.getElementsByTagName("html")[0];

    //  Default Config Value
    var defaultConfig = {
        theme: "light",

        layout: {
            width: "fluid",
            position: "fixed"
        },

        topbar: {
            color: "light",
        },

        menu: {
            color: "light",
        },

        // This option for only vertical (left Sidebar) layout
        sidenav: {
            size: "default",
            user: true,
        },
    };

    this.html = document.getElementsByTagName('html')[0];

    config = Object.assign(JSON.parse(JSON.stringify(defaultConfig)), {});

    config.theme = html.getAttribute('data-bs-theme') || defaultConfig.theme;
    config.layout.width = html.getAttribute('data-layout-width') || defaultConfig.layout.width;
    config.layout.position = html.getAttribute('data-layout-position') || defaultConfig.layout.position;
    config.topbar.color = html.getAttribute('data-topbar-color') || defaultConfig.topbar.color;
    config.sidenav.size = html.getAttribute('data-sidebar-size') || defaultConfig.sidenav.size;
    config.menu.color = html.getAttribute('data-menu-color') || defaultConfig.menu.color;
    config.sidenav.user = html.getAttribute('data-sidebar-user') !== null ? true : defaultConfig.sidenav.user;

    window.defaultConfig = JSON.parse(JSON.stringify(config));

    if (savedConfig !== null) {
        config = JSON.parse(savedConfig);
    }

    // FORCE USER INFO TO BE VISIBLE ALWAYS
    config.sidenav.user = true;

    window.config = config;

    if (config) {

        if (window.innerWidth <= 1199) {
            html.setAttribute("data-sidebar-size", "mobile");
        } else {
            html.setAttribute("data-sidebar-size", config.sidenav.size);
        }

        html.setAttribute("data-bs-theme", config.theme);
        html.setAttribute("data-menu-color", config.menu.color);
        html.setAttribute("data-topbar-color", config.topbar.color);
        html.setAttribute("data-layout-width", config.layout.width);
        html.setAttribute("data-layout-position", config.layout.position);

        if (config.sidenav.user && config.sidenav.user.toString() === "true") {
            html.setAttribute("data-sidebar-user", true);
        } else {
            html.removeAttribute("data-sidebar-user");
        }

    }
})();