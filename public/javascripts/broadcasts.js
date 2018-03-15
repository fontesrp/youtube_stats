const setupBroadcasts = function () {

    "use strict";

    const embedVideo = function (id) {

        const iframe = cEl("iframe");

        iframe.src = `https://www.youtube.com/embed/${id}`;

        const video = byId("video");

        removeAllChildren(video);
        video.appendChild(iframe);
    };

    const displayBroadcast = function (props) {

        fetch(`${currentUrl()}/broadcasts/show?id=${props.id}&owner=${props.owner}`)
            .then(res => res.json())
            .then(function (items) {

                const broadcast = items[0];

                embedVideo(broadcast.id);
                return setupLiveChat({
                    id: broadcast.snippet.liveChatId,
                    owner: props.owner
                });
            });
    };

    const lockFields = function (lock, fields) {

        Object.keys(fields).forEach(function (id) {

            const fld = byId(id);

            fld.disabled = lock;
            fld.value = fields[id];
        });
    };

    const streamChange = function (evt) {

        const select = evt.currentTarget;

        const option = select.selectedOptions[0];

        const data = option.dataset;

        if (data.id === "new") {
            lockFields(false, {
                "stream-title": "",
                format: "240p",
                ingestion: "rtmp"
            });
        } else {
            lockFields(true, {
                "stream-title": data.title,
                format: data.format,
                ingestion: data.ingestion
            });
        }
    };

    const saveBroadcast = function () {

        const form = byId("new-broadcast-form");

        if (!form.reportValidity()) {
            alert("Please, fill all the fields");
            return;
        }

        const data = new FormData(form);

        let streamId = data.get("stream");

        if (streamId === "new") {
            streamId = null;
        }

        const postBody = {
            schedule: {
                start_at: toUtc(data.get("start_at")),
                end_at: toUtc(data.get("end_at"))
            },
            title: data.get("title"),
            status: data.get("status"),
            stream: {
                id: streamId,
                title: byId("stream-title").value,
                format: byId("format").value,
                ingestion: byId("ingestion").value
            }
        };

        form.reset();
        byId("stream").dispatchEvent(new Event("change"));

        fetch(`${currentUrl()}/broadcasts`, {
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
                loadBroadcasts();
                $("#new-broadcast-modal").modal("hide");
            });
    };

    const loadStreams = async function () {

        const res = await fetch(`${currentUrl()}/broadcasts/streams`, {
            credentials: "same-origin"
        });

        const streams = await res.json();

        const select = byId("stream");
        keepOnlyFirstling(select);

        streams.forEach(function (strm) {

            const option = cEl("option");
            option.value = strm.id;
            option.dataset.id = strm.id;
            option.dataset.title = strm.snippet.title;
            option.dataset.format = strm.cdn.format;
            option.dataset.ingestion = strm.cdn.ingestionType;

            const title = asTxt(strm.snippet.title);
            option.appendChild(title);

            select.appendChild(option);
        });

        select.dispatchEvent(new Event("change"));
    };

    const setDefaultSchedule = function () {

        const oneHour = 1000 * 60 * 60;

        byId("start_at").value = localeDateTime(new Date());
        byId("end_at").value = localeDateTime(new Date(Date.now() + oneHour));
    };

    const openNewBcModal = function () {

        $("#new-broadcast-modal").modal("show");

        setDefaultSchedule();

        return loadStreams();
    };

    const setupMenu = function () {

        byId("broadcasts-menu").addEventListener("click", function (evt) {

            if (!evt.target.matches(".broadcast-link")) {
                return;
            }

            evt.preventDefault();

            const props = evt.target.dataset;

            if (props.id === "new") {
                openNewBcModal();
            } else {
                clearTimeout(chatTimeoutId);
                displayBroadcast(props);
            }
        });

        byId("new-broadcast-save").addEventListener("click", saveBroadcast);

        byId("stream").addEventListener("change", streamChange);
    };

    const loadBroadcasts = async function () {

        const res = await fetch(`${currentUrl()}/broadcasts`);

        const broadcasts = await res.json();

        const broadcastsMenu = byId("broadcasts-menu");
        keepOnlyFirstling(broadcastsMenu);

        Object.keys(broadcasts).forEach(function (owner) {

            broadcasts[owner].forEach(function (bc) {

                const anchor = cEl("a");
                anchor.classList.add("dropdown-item");
                anchor.classList.add("broadcast-link");
                anchor.dataset.id = bc.id;
                anchor.dataset.owner = owner;
                anchor.href = "#";

                const title = `${bc.snippet.title} (${bc.status.lifeCycleStatus})`;
                anchor.appendChild(asTxt(title));

                broadcastsMenu.appendChild(anchor);
            });
        });
    };

    loadBroadcasts();
    setupMenu();
};
