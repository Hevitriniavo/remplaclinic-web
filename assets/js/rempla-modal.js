export default class {
    constructor(selector) {
        this.selector = selector
        this.modal = document.querySelector('#' + selector)

        if (this.modal) {
            this.addEventListener()
        }
    }

    addEventListener() {
        this.addActivatorEventListener()
        this.addCloseEventListener()
    }

    addActivatorEventListener() {
        const activators = document.querySelectorAll('.' + this.selector + '-activator')
        activators.forEach(activator => {
            activator.addEventListener('click', () => {
                this.modal.classList.remove('hidden')
                this.modal.classList.add('visible')
            })
        })
    }

    addCloseEventListener() {
        const closeActions = document.querySelectorAll('#' + this.selector + ' .close')
        closeActions.forEach(closeAction => {
            closeAction.addEventListener('click', () => {
                this.modal.classList.remove('visible')
                this.modal.classList.add('hidden')
            })
        })
    }

    // <button
    //     class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded my-modal-activator">
    //     Open modal
    // </button>

    // <!-- Modal Overlay & Content Container (Hidden by default) -->
    // <div id="my-modal" class="fixed inset-0 bg-black bg-opacity-50 flex hidden items-center justify-center z-50">

    //     <!-- Modal Content Box -->
    //     <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">

    //         <!-- Modal Header -->
    //         <div class="flex justify-between items-center mb-4">
    //             <h2 class="text-xl font-semibold">Modal Title</h2>
    //             <button class="text-gray-500 hover:text-gray-700 close">&times;</button>
    //         </div>

    //         <!-- Modal Body -->
    //         <div>The modal body here</div>

    //         <!-- Modal Footer (Optional) -->
    //         <div class="mt-4 flex justify-end">
    //             <button
    //                 class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded close">
    //                 Close
    //             </button>
    //         </div>
    //     </div>
    // </div>

    // Alert
    // <div id="alert" class="relative p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50">
    //     <span class="font-medium">Success!</span> Saved successfully.
    //     <button onclick="document.getElementById('alert').remove()"
    //         class="absolute top-2 right-2 text-green-700 hover:text-green-900">
    //         ✕
    //     </button>
    // </div>
}