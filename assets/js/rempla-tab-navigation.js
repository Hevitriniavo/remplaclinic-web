export default class {
    constructor(btnSelector, contentSelector) {
        this.btnSelector = btnSelector
        this.contentSelector = contentSelector
        this.buttons = document.querySelectorAll('.tab-btn')
        this.contents = document.querySelectorAll('.tab-content')

        this.buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                this.buttons.forEach(b => {
                    b.classList.remove('text-[#ff9d32]', 'border-[#ff9d32]')
                    b.classList.add('text-gray-600', 'border-transparent')
                })
                this.contents.forEach(c => c.classList.add('hidden'))

                btn.classList.remove('text-gray-600', 'border-transparent')
                btn.classList.add('text-[#ff9d32]', 'border-[#ff9d32]')
                document.getElementById(btn.dataset.tab).classList.remove('hidden')
            })
        })

        // <!-- Tabs -->
        // <div class="flex">
        //     <button
        //         class="tab-btn px-4 py-2 text-sm font-medium border-b-2 text-[#ff9d32] border-[#ff9d32] hover:text-[#ff9d32] hover:border-[#ff9d32]"
        //         data-tab="tab1">
        //         Informations de la demande
        //     </button>
        //     <button
        //         class="tab-btn px-4 py-2 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-[#ff9d32] hover:border-[#ff9d32]"
        //         data-tab="tab2">
        //         Liste des personnes contactes
        //     </button>
        //     <button
        //         class="tab-btn px-4 py-2 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-[#ff9d32] hover:border-[#ff9d32]"
        //         data-tab="tab3">
        //         Tab 3
        //     </button>
        // </div>

        // <!-- Content -->
        // <div class="mt-4">
        //     <div id="tab1" class="tab-content">
        //         <p class="text-gray-700">Content for Tab 1</p>
        //     </div>
        //     <div id="tab2" class="tab-content hidden">
        //         <p class="text-gray-700">Content for Tab 2</p>
        //     </div>
        //     <div id="tab3" class="tab-content hidden">
        //         <p class="text-gray-700">Content for Tab 3</p>
        //     </div>
        // </div>
    }
}