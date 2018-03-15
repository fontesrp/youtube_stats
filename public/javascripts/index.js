(function () {

    "use strict";

    const onload = function () {

        setupNavBar();
        setupBroadcasts();

        setupUserMessages();
        setupReport();
    };

    document.addEventListener("DOMContentLoaded", onload);
}());
