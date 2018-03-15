const setupUserMessages = function () {

    "use strict";

    const setupDisplayModal = function () {

        const btn = byId("user-messages-json");

        btn.addEventListener("click", function (evt) {
            window.location.href = userMessagesPath(btn.dataset.userId);
        });
    };

    const showUserMessages = function (messages) {

        const list = byId("user-meassages-list");

        removeAllChildren(list);

        messages.forEach(function (msg) {

            const created = new Date(msg.created_at);

            const item = cEl("li");
            item.classList.add("list-group-item");
            item.appendChild(asTxt(`${localeDateTime(created)}: ${msg.body}`));

            list.appendChild(item);
        });

        $("#user-messages-modal").modal("show");
    };

    const messageSearchClick = function (evt) {

        const form = byId("messages-search-form");

        if (!form.reportValidity()) {
            alert("Please, select a valid user");
            return;
        }

        const data = new FormData(form);
        const userId = data.get("id");

        fetch(userMessagesPath(userId))
            .then(res => res.json())
            .then(showUserMessages);

        $("#messages-search-modal").modal("hide");
        byId("user-messages-json").dataset.userId = userId;
    };

    const setupAutocomplete = function (param) {

        $(`#${param.domId}`).autocomplete({
            minLength: 2,
            source: param.source,
            select: function (ignore, ui) {
                param.change(ui.item.value);
            },
            change: function (ignore, ui) {

                const selected = (ui.item === null)
                    ? null
                    : ui.item.value;

                param.change(selected);
            }
        });
    };

    const setUser = function (params) {

        let user = params.users.find(usr => (usr[params.field] === params.selected));

        if (user === undefined) {
            user = {
                id: "",
                full_name: "",
                email: ""
            };
        }

        byId("user-id").value = user.id;
        byId("user-name").value = user.full_name;
        byId("user-email").value = user.email;
    };

    const listUsers = function (params) {
        return params.users.map(usr => usr[params.field]);
    };

    const fetchUsers = async function (param) {

        const res = await fetch(`${currentUrl()}/users/all_with_messages?${param.field}=${param.term}`);

        const users = await res.json();

        param.cache.users = users;

        const list = listUsers({
            users,
            field: param.field
        });

        param.res(list);
    };

    const setupSearch = function (users) {

        const cache = {};

        const source = function (field) {
            return function (req, res) {
                fetchUsers({
                    cache,
                    res,
                    term: req.term,
                    field
                });
            };
        };

        const change = function (field) {
            return function (selected) {
                setUser({
                    users: cache.users,
                    field,
                    selected
                });
            };
        };

        const fields = [
            {
                domId: "user-name",
                source: source("full_name"),
                change: change("full_name")
            }, {
                domId: "user-email",
                source: source("email"),
                change: change("email")
            }
        ];

        fields.forEach(function (fld) {
            setupAutocomplete(fld);
        });

        byId("messages-search-submit").addEventListener("click", messageSearchClick);
    };

    setupSearch();
    setupDisplayModal();
};
