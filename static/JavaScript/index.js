let signUpModal = null;
let signInModal = null;
let newTodoItemModal  = null;
let editTodoItemModal = null;

window.addEventListener("load", async () => {
    createSignUpModal();
    createSignInModal();
    createNewTodoItemModal();
    createEditTodoItemModal();

    const isSessionActive = await getSessionActive();
    if (isSessionActive) {
        activateSignOutButton();
        loadTodoItems();
    } else {
        activateSignUpButton();
        activateSignInButton();
    }
});

function createSignUpModal() {
    signUpModal = new ModalWindow(
        "#sign-up-modal-content",
        (modal) => {
            modal.submitButton  = modal.shadowRoot.querySelector("#sign-up-submit-button");
            modal.usernameInput = modal.shadowRoot.querySelector("#sign-up-username");
            modal.passwordInput = modal.shadowRoot.querySelector("#sign-up-password");
            modal.passwordConfirmInput = modal.shadowRoot.querySelector("#sign-up-password-confirm");

            modal.submitButton.addEventListener("click", async () => {
                const username = modal.usernameInput.value;
                const password = modal.passwordInput.value;

                if (password !== modal.passwordConfirmInput.value) {
                    alert("Passwords do not match.");
                } else if (!validateUsername(username)) {
                    alert("This username is not valid.");
                } else if (!validatePassword(password)) {
                    alert("This password is not valid.");
                } else {
                    try {
                        await sendSignUpRequest(username, password);
                        location.reload();
                    } catch (error) {
                        console.error(error);
                        alert("An error occurred while attempting sign-up request.");
                    }
                }
            });
        }
    );
    document.querySelector("body").appendChild(signUpModal);
}

function createSignInModal() {
    signInModal = new ModalWindow(
        "#sign-in-modal-content",
        (modal) => {
            modal.submitButton  = modal.shadowRoot.querySelector("#sign-in-submit-button");
            modal.usernameInput = modal.shadowRoot.querySelector("#sign-in-username");
            modal.passwordInput = modal.shadowRoot.querySelector("#sign-in-password");

            modal.submitButton.addEventListener("click", async () => {
                const username = modal.usernameInput.value;
                const password = modal.passwordInput.value;

                if (!validateUsername(username)) {
                    alert("This username is not valid.");
                } else if (!validatePassword(password)) {
                    alert("This password is not valid.");
                } else {
                    try {
                        await sendSignInRequest(username, password);
                        location.reload();
                    } catch (error) {
                        console.error(error);
                        alert("An error occurred while attempting sign-in request.");
                    }
                }
            });
        }
    );
    document.querySelector("body").appendChild(signInModal);
}

function createNewTodoItemModal() {
    newTodoItemModal = new ModalWindow(
        "#new-todo-item-modal-content",
        (modal) => {
            modal.textArea     = modal.shadowRoot.querySelector("#new-todo-item-text-area");
            modal.submitButton = modal.shadowRoot.querySelector("#new-todo-item-submit-button");

            modal.submitButton.addEventListener("click", async () => {
                try {
                    await sendCreateTodoItemRequest(modal.textArea.value, false);
                    await loadTodoItems();
                    modal.hide();
                } catch (error) {
                    console.error(error);
                    alert("An error occurred white attempting create todo-item request. Please try again.");
                }
            });
        }
    );

    newTodoItemModal.onHide = (modal) => {
        modal.textArea.value = "";
    };

    document.querySelector("body").appendChild(newTodoItemModal);
    document.querySelector("#new-todo-item-button").addEventListener("click", () => newTodoItemModal.show());
}

function createEditTodoItemModal() {
    editTodoItemModal = new ModalWindow(
        "#edit-todo-item-modal-content",
        (modal) => {
            modal.textArea     = modal.shadowRoot.querySelector("#edit-todo-item-text-area");
            modal.submitButton = modal.shadowRoot.querySelector("#edit-todo-item-submit-button");

            modal.submitButton.addEventListener("click", async () => {
                const todoItem = modal.todoItem;
                const originalTextState = todoItem.text;
                try {
                    todoItem.text = modal.textArea.value;
                    await sendUpdateTodoItemRequest(todoItem);
                    todoItem.syncWithGui();
                    modal.hide();
                } catch (error) {
                    todoItem.text = originalTextState;
                    console.error(error);
                    alert("An error occurred white attempting update todo-item request, please try again.");
                }
            });

            modal.todoItem = null;
            modal.openWithTodoItem = (todoItem) => {
                modal.todoItem = todoItem;
                modal.show();
            };
        }
    );

    editTodoItemModal.onShow = (modal) => {
        modal.textArea.value = modal.todoItem.text;
    };

    editTodoItemModal.onHide = (modal) => {
        modal.textArea.value = "";
    };

    document.querySelector("body").appendChild(editTodoItemModal);
}

function activateSignUpButton() {
    const signUpShowButton = document.querySelector("#sign-up-show-button");
    activateNavbarButton(signUpShowButton);
    signUpShowButton.addEventListener("click", () => signUpModal.show());
}

function activateSignInButton() {
    const signInShowButton = document.querySelector("#sign-in-show-button");
    activateNavbarButton(signInShowButton);
    signInShowButton.addEventListener("click", () => signInModal.show());
}

function activateSignOutButton() {
    const signOutButton = document.querySelector("#sign-out-button");
    activateNavbarButton(signOutButton);
    signOutButton.addEventListener("click", async () => {
        try {
            await sendSignOutRequest();
            location.reload();
        } catch (error) {
            console.error(error);
            alert("An error occurred while attempting sign-out request, please reload the page.");
        }
    });
}

function activateNavbarButton(button) {
    button.classList.remove("navbar-button-inactive");
    button.classList.add("navbar-button-active");
}

async function loadTodoItems() {
    try {
        const todoItems = await getAllTodoItems();

        const todoItemsContainer = document.querySelector("#todo-items-container");
        todoItemsContainer.innerHTML = "";
        for (const todoItem of todoItems) {
            todoItemsContainer.appendChild(
                new TodoItem(
                    todoItem.id, 
                    todoItem.guid,
                    todoItem.text,
                    todoItem.done,
                    todoItem.creation_time
                )
            );
        }

        document.querySelector("#primary-content").classList.remove("hidden");

    } catch (error) {
        console.error(error);
        alert("An error occurred while trying to load the todo items, please reload the page.");
    }
}

function validateUsername(username) {
    // TODO -- client side validation
    return true;
}

function validatePassword(password) {
    // TODO -- client side validation
    return true;
}

async function sendSignUpRequest(username, password) {
    const data = {
        username: username,
        password: password
    };

    const response = await fetch(
        "/users/sign-up",
        {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data)
        }
    );

    if (response.status !== 200) {
        throw new Error(`Attempt to sign-up failed. Status: (${response.status}) ${response.statusText}.`);
    }
}

async function sendSignInRequest(username, password) {
    const data = {
        username: username,
        password: password
    };

    const response = await fetch(
        "/users/sign-in",
        {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data)
        }
    );

    if (response.status !== 200) {
        throw new Error(`Attempt to sign-in failed. Status: (${response.status}) ${response.statusText}.`);
    }
}

async function sendSignOutRequest() {
    const response = await fetch(
        "/users/sign-out",
        {
            method: "POST"
        }
    );

    if (response.status !== 200) {
        throw new Error(`Attempt to sign-out failed. Status: (${response.status}) ${response.statusText}.`);
    }
}

async function getSessionActive() {
    const response = await fetch("/users/is-session-active");

    if (response.status === 200) {
        const json = await response.json();
        return json.result;
    } else {
        return false;
    }
}

async function getAllTodoItems() {
    const response = await fetch("/protected/todos/get-all");

    if (response.status === 200) {
        const json = await response.json();
        return json;
    } else {
        throw response;
    }
}

async function sendCreateTodoItemRequest(text, done) {
    const data = {
        text: text,
        done: done
    };

    const response = await fetch(
        "/protected/todos/create",
        {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        }
    )

    if (response.status !== 200) {
        throw new Error(`Attempt to create todo item failed. Status: (${response.status}) ${response.statusText}.`);
    }
}

async function sendUpdateTodoItemRequest(todoItem) {
    const response = await fetch(
        `/protected/todos/update/${todoItem.id}`,
        {
            method: "PUT",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(todoItem)
        }
    );

    if (response.status !== 200) {
        throw new Error(`Attempt to update todo item failed. Status: (${response.status}) ${response.statusText}.`);
    }
}

async function sendDeleteTodoItemRequest(todoItem) {
    const response = await fetch(
        `/protected/todos/delete/${todoItem.id}`,
        {
            method: "DELETE"
        }
    );

    if (response.status !== 200) {
        throw new Error(`Attempt to delete todo item failed. Status: (${response.status}) ${response.statusText}.`);
    }
}
