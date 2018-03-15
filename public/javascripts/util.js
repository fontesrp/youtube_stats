const currentUrl = function () {

    "use strict";

    // Remove non-word characters from the end of the URL
    // ".../youtube_stats/#" becomes ".../youtube_stats"
    return window.location.href.replace(/\W+$/, "");
};

const usersWithMessagesPath = function () {

    "use strict";

    return `${currentUrl()}/users/messages`;
};

const qs = function (query, node) {

    "use strict";

    if (node === undefined) {
        node = document;
    }

    return node.querySelector(query);
};

const qsa = function (query, node) {

    "use strict";

    if (node === undefined) {
        node = document;
    }

    return node.querySelectorAll(query);
};

const byId = function (id) {

    "use strict";

    return document.getElementById(id);
};

const cEl = function (tag) {

    "use strict";

    return document.createElement(tag);
};

const asTxt = function (str) {

    "use strict";

    return document.createTextNode(str);
};

const removeAllChildren = function (node) {

    "use strict";

    while (node.firstChild) {
        $(node.firstChild).remove();
    }
};

const keepOnlyFirstling = function (node) {

    "use strict";

    let firstling = null;

    if (node.firstElementChild) {
        firstling = node.firstElementChild.cloneNode(true);
    }

    removeAllChildren(node);

    if (firstling) {
        node.appendChild(firstling);
    }
};

const localeDateTime = function (dateObj) {

    "use strict";

    return `${dateObj.toLocaleDateString()} ${dateObj.toLocaleTimeString()}`;
};

const toUtc = function (dateStr) {

    "use strict";

    const date = new Date(dateStr);

    const day = String(date.getUTCDate()).padStart(2, "0");
    const month = String(date.getUTCMonth() + 1).padStart(2, "0");
    const year = date.getUTCFullYear();

    const hours = String(date.getUTCHours()).padStart(2, "0");
    const minutes = String(date.getUTCMinutes()).padStart(2, "0");
    const seconds = String(date.getUTCSeconds()).padStart(2, "0");

    return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}Z`;
};

const userMessagesPath = function (userId) {

    "use strict";

    return `${currentUrl()}/users/messages?id=${userId}`;
};

const hide = function (node) {

    "use strict";

    node.classList.add("hidden");
};

const show = function (node) {

    "use strict";

    node.classList.remove("hidden");
};
