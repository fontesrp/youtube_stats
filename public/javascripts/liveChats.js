const setupLiveChat = async function (props) {

    "use strict";

    const displayMessages = function (messages) {

        messages.sort(function (a, b) {

            if (a.snippet.publishedAt < b.snippet.publishedAt) {
                return 1;
            }

            if (a.snippet.publishedAt === b.snippet.publishedAt) {
                return 0;
            }

            return -1;
        });

        const chat = byId("chat");

        keepOnlyFirstling(chat);

        messages.forEach(function (msg) {

            const row = cEl("div");
            row.classList.add("row");
            row.classList.add("mt-2");

            const pictureDiv = cEl("div");
            pictureDiv.classList.add("col-1");
            const picture = cEl("div");
            picture.classList.add("profile-picture");
            picture.style.backgroundImage = `url(${msg.authorDetails.profileImageUrl})`;
            pictureDiv.appendChild(picture);
            row.appendChild(pictureDiv);

            const name = cEl("div");
            name.classList.add("col-2");
            const strong = cEl("strong");
            strong.appendChild(asTxt(msg.authorDetails.displayName));
            name.appendChild(strong);
            row.appendChild(name);

            const body = cEl("div");
            body.classList.add("col-9");
            body.appendChild(asTxt(msg.snippet.textMessageDetails.messageText));
            row.appendChild(body);

            chat.appendChild(row);
        });
    };

    const fetchMessages = async function (props) {

        const res = await fetch(`${currentUrl()}/broadcasts/messages?id=${props.id}&owner=${props.owner}`);

        const messages = await res.json();

        displayMessages(messages);
    };

    const submitMessage = function (evt) {

        const form = evt.target;

        if (!form.matches(".new-message-form")) {
            return;
        }

        evt.preventDefault();

        if (!form.reportValidity()) {
            alert("Please, fill in the message before submitting");
            return;
        }

        const message = qs("input.form-control[type='text'][name='message']");

        const postBody = {
            body: message.value,
            chat_id: message.dataset.chatId
        };

        form.reset();

        fetch(`${currentUrl()}/broadcasts/messages`, {
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json"
            },
            credentials: "same-origin",
            method: "POST",
            body: JSON.stringify(postBody)
        })
            .then(res => res.json())
            .then(function () {
                fetchMessages({
                    id: message.dataset.chatId,
                    owner: message.dataset.chatOwner
                });
            });
    };

    const addMessageInput = function (props) {

        const row = cEl("div");
        row.classList.add("row");
        row.classList.add("mt-2");

        const formDiv = cEl("div");
        formDiv.classList.add("col-12");

        const form = cEl("form");
        form.classList.add("form-inline");
        form.classList.add("new-message-form");

        const message = cEl("input");
        message.type = "text";
        message.classList.add("form-control");
        message.classList.add("mr-2");
        message.name = "message";
        message.placeholder = "New message";
        message.dataset.chatId = props.id;
        message.dataset.chatOwner = props.owner;
        message.required = true;
        form.appendChild(message);

        const submit = cEl("button");
        submit.type = "submit";
        submit.classList.add("btn");
        submit.classList.add("btn-outline-secondary");
        submit.appendChild(asTxt("Send"));
        form.appendChild(submit);

        formDiv.appendChild(form);

        row.appendChild(formDiv);
        byId("chat").appendChild(row);

        document.addEventListener("submit", submitMessage);
    };

    const isSignedIn = async function () {

        const res = await fetch(`${currentUrl()}/session/check`, {
            credentials: "same-origin"
        });

        const result = await res.json();

        return result["signed_in"];
    };

    const noChatMessage = function () {

        const chat = byId("chat");

        removeAllChildren(chat);

        chat.appendChild(asTxt("Live chat is not enabled for this broadcast"));
    };

    byId("chat-stats-nav-link").dataset.chatId = props.id;

    if (props.id === null) {
        noChatMessage();
        return;
    }

    const signedIn = await isSignedIn();

    if (signedIn) {
        addMessageInput(props);
    }

    return fetchMessages(props);
};
