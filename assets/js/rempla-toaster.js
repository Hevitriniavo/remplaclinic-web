export default function showToast(message, type = 'success', duration = 5000, containerId = 'rempla-toaster-root') {
    const toastContainer = document.getElementById(containerId)

    if (!toastContainer) {
        return
    }

    const colors = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white',
        warning: 'bg-yellow-500 text-black',
    }

    const toast = document.createElement('div')
    toast.className = `
        flex items-center justify-between px-4 py-2 rounded shadow
        ${colors[type] || colors.success}
        animate-slide-in
    `

    const removeToast = () => {
        if (toast) {
            toast.classList.add('animate-slide-out');
            toast.addEventListener('animationend', () => toast.remove())
        }
    }

    // add message
    const spanMessage = document.createElement('span')
    spanMessage.innerText = message
    toast.appendChild(spanMessage)

    // add close btn
    const btnClose = document.createElement('button')
    btnClose.className = 'ml-4 font-bold'
    btnClose.innerText = 'x'
    
    btnClose.addEventListener('click', () => {
        removeToast()
    })

    toast.appendChild(btnClose)

    toastContainer.appendChild(toast);

    setTimeout(removeToast, duration);
}