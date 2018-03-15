const setupNavBar = function () {

    "use strict";

    const setupSignIn = function () {

        byId("google-signin-btn").addEventListener("click", function () {
            $("#login-modal").modal("show");
        });
    };

    return fetch(`${currentUrl()}/session/check`, {
        credentials: "same-origin"
    })
        .then(res => res.json())
        .then(function (session) {

            if (session.signed_in) {

                const name = byId("nav-user-name");
                removeAllChildren(name);
                name.appendChild(asTxt(session.full_name));

                qsa(".signout").forEach(hide);
                qsa(".signin").forEach(show);

                if (session.admin) {
                    qsa(".admin").forEach(show);
                }
            } else {
                qsa(".signin").forEach(hide);
                qsa(".admin").forEach(hide);
                qsa(".signout").forEach(show);
                setupSignIn();
            }
        });
};
