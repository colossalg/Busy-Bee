class ModalWindow extends HTMLElement {
    constructor(id, callback) {
        super();

        this.id = id;
        this.callback = callback;

        this.onShow = null;
        this.onHide = null;
    }

    show() {
        const modal = this.shadowRoot.querySelector("#modal");
        modal.style.display = "block";

        if (this.onShow !== null) {
            this.onShow(this);
        }
    }

    hide() {
        const modal = this.shadowRoot.querySelector("#modal");
        modal.style.display = "none";

        if (this.onHide !== null) {
            this.onHide(this);
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

                #modal {
                    display: none;
                    position: fixed;
                    top:  0;
                    left: 0;
                    z-index: 1;
                    width:  100%;
                    height: 100%;
                    overflow: auto;
                    background-color: rgba(0, 0, 0, 0.4);
                }
            </style>

            <div id="modal">
                <div class="w-100-pct h-100-pct display-flex justify-content-center align-items-center">
                    <div class="p-pt-250-rem bg-white" id="modal-container-inner">
                        <div class="display-flex justify-content-right align-items-center">
                            <button class="icon-button bg-red" id="modal-close-button">
                                <span class="text-white">X</span>
                            </button>
                        </div>
                        <!-- User content here... -->
                    </div>
                </div>
            </div>
        `;

        let closeButton = this.shadowRoot.querySelector("#modal-close-button");
        closeButton.addEventListener("click", () => {
            this.hide();
        });

        const innerContainer = this.shadowRoot.querySelector("#modal-container-inner");
        const contentClone   = document.querySelector(this.id).content.cloneNode(true);
        for (const child of contentClone.children) {
            innerContainer.appendChild(child);
        }

        this.callback(this);
    }
}

customElements.define("modal-window", ModalWindow);