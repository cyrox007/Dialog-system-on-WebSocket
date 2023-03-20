main = {};
main.core = {
    data: {
        usersFieldSelect: document.querySelectorAll(".item"),
        msgField: document.querySelector("section"),
        userID: null,
        dialogesArr: null,

        messageTextField: document.getElementById("text"),
        messageSendButton: document.getElementById("send"),

        socket: new WebSocket("ws://localhost:2346?user_id=<? echo $user_id; ?>"),
    },
    handler: {
        getSelectedDialog: () => {
            for (let index = 0; index <  main.core.data.usersFieldSelect.length; index++) {
                const element =  main.core.data.usersFieldSelect[index];
                if (element.classList.contains("select")) {
                    return element;
                }
            }
        },
        getDialogesIDs: () => {
            let IDs = Array();
            main.core.data.usersFieldSelect.forEach(el=>{
                IDs.push(el.getAttribute('id'));
            });
            return IDs;
        },
    },
    methods: {
        sendMessage: () => {
            let message = JSON.stringify({
                "action": "Message",
                "dialogID": document.getElementById("dialog-send-field").getAttribute("dialog-id"),
                "text": main.core.data.messageTextField.value,
            });
            main.core.data.socket.send(message);
            main.core.data.messageTextField.value = null;
        },
        loadMessages: () => {
            let dialog_id = document.getElementById("dialog-send-field").getAttribute("dialog-id");
            let messagesContainer = document.getElementById("send_msgs");
            messagesContainer.innerHTML = null;

            if (main.core.data.dialogesArr[dialog_id] != null) {
                main.core.data.dialogesArr[dialog_id].forEach(el=>{
                    let messageBox = document.createElement("div");
                    messageBox.setAttribute("class", "message");
                    let messageText = document.createElement("div");
                    let messageInfo = document.createElement("div");
                    messagesContainer.appendChild(messageBox);
                    messageBox.appendChild(messageInfo);
                    messageBox.appendChild(messageText);
                    messageText.textContent = el["text"];
                });
            }
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }
};

(function () {
    main.core.data.usersFieldSelect.forEach(el=>{
        el.addEventListener('click', ()=>{
            main.core.data.msgField.classList.add("active");
            let user_id = el.getAttribute('id');
            let dialog_id = el.getAttribute("dialog-id");
            let old_el = main.core.handler.getSelectedDialog();
            if (old_el != undefined) {
                old_el.classList.remove("select");
            }
            el.classList.add("select");
            document.querySelector("header").textContent = "Пользователь "+user_id;
            document.getElementById("dialog-send-field").setAttribute("dialog-id", dialog_id);
            main.core.methods.loadMessages();
        });
    });

    
    main.core.data.socket.onopen = () => {

    };
    
    main.core.data.socket.onmessage = (event) => {
        let data = JSON.parse(event.data);
        if (data["action"] == "Ping") {
            return;
        }
        console.log(data);
        main.core.data.userID = data["userId"];
        main.core.data.dialogesArr = data["userDialoges"];
        main.core.methods.loadMessages();
    };

    main.core.data.messageSendButton.addEventListener("click", (e)=>{
        e.preventDefault();
        console.log("send");
        main.core.methods.sendMessage();
        
    });
}());