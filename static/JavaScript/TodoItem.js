class TodoItem extends HTMLElement {
    constructor(id, uid, text, done, creationTime) {
        super();

        this.id = id;
        this.uid = uid;
        this.text = text;
        this.done = done;
        this.creationTime = creationTime;
    }

    syncWithGui() {
        let checkbox = this.shadowRoot.querySelector("#todo-item-checkbox");
        let label    = this.shadowRoot.querySelector("#todo-item-label");
        checkbox.checked  = this.done;
        label.textContent = this.text;
        if (this.done) {
            label.classList.add("strike-through");
        } else {
            label.classList.remove("strike-through");
        }
    }

    connectedCallback() {
        this.attachShadow({ mode: "open" });
        this.shadowRoot.innerHTML = `
            <link rel="stylesheet" href="/static/CSS/framework.css">
            <link rel="stylesheet" href="/static/CSS/style.css">

            <style>
                :host {
                    width: 100%;
                }

                button {
                    width:  6rem;
                    height: 2rem;
                    border-radius: var(--default-border-radius);
                    font-size: large;
                    font-weight: bold;
                    opacity: 0.5;
                }

                button:hover {
                    opacity: 1.0;
                }

                #todo-item-container {
                    margin:  0.25rem auto;
                    padding: 0.25rem;
                    width: 80%;
                    min-height: 2rem;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-radius: var(--default-border-radius);
                    background-color: white;
                }
            </style>

            <div id="todo-item-container">
                <div>
                    <input type="checkbox" id="todo-item-checkbox" name="todo-item-checkbox" value="false">
                    <label for="todo-item-checkbox" id="todo-item-label" name="todo-item-label">${this.text}</label>
                </div>
                <div>
                    <button class="text-white bg-blue" id="todo-item-edit-button">Edit</button>
                    <button class="text-white bg-red"  id="todo-item-delete-button">Delete</button>
                </div>
            </div>
        `;

        this.syncWithGui();

        const checkbox = this.shadowRoot.querySelector("#todo-item-checkbox");
        checkbox.addEventListener("click", async (e) => {
            const originalDoneState = this.done;
            try {
                this.done = !this.done;
                await sendUpdateTodoItemRequest(this);
                this.syncWithGui();
            } catch (error) {
                this.done = originalDoneState;
                console.error(error);
                alert("An error occurred white attempting update todo-item request, please try again.");
            }
        });

        const editButton = this.shadowRoot.querySelector("#todo-item-edit-button");
        editButton.addEventListener("click", () => editTodoItemModal.openWithTodoItem(this));

        const deleteButton = this.shadowRoot.querySelector("#todo-item-delete-button");
        deleteButton.addEventListener("click", async () => {
            try {
                await sendDeleteTodoItemRequest(this);
                this.remove();
            } catch (error) {
                console.error(error);
                alert("An error occurred white attempting delete todo-item request, please try again.");
            }
        });
    }
}

customElements.define("todo-item", TodoItem);