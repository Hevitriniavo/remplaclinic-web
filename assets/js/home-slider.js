class HomeSlide {
    constructor(slideContainer) {
        this.slideItems = slideContainer.querySelectorAll('.slide-item')
        this.slideContainerItems = slideContainer.querySelectorAll('.slide-container-item')
        this.slideItemActiveIndex = 0
        this.slideTimer = null

        this.addSlideItemEventListener()
        this.initSlideTimer(0)
    }

    initSlideTimer(startIndex) {
        this.slideItemActiveIndex = startIndex
        this.setSlideItemActive(startIndex)
        this.slideTimer = setInterval(() => {
            this.slideItemActiveIndex++

            if (this.slideItemActiveIndex >= this.slideItems.length) {
                this.slideItemActiveIndex = 0
            }

            this.setSlideItemActive(this.slideItemActiveIndex)
        }, 5000)
    }

    addSlideItemEventListener() {
        this.slideItems.forEach((slideItem, index) => {
            slideItem.addEventListener('mouseenter', () => { this.mouseEnterSlideItem(index) })
            slideItem.addEventListener('mouseleave', () => { this.mouseLeaveSlideItem(index) })
        })
    }

    setSlideItemActive(index) {
        this.slideItems.forEach((item, itemIndex) => {
            item.classList.remove('active')

            if (itemIndex < this.slideContainerItems.length) {
                this.slideContainerItems[itemIndex].classList.remove('active')
            }
        })

        this.slideItems[index].classList.add('active')
        if (index < this.slideContainerItems.length) {
            this.slideContainerItems[index].classList.add('active')
        }
    }

    mouseEnterSlideItem(index) {
        if (this.slideTimer) {
            clearInterval(this.slideTimer)
        }
        this.setSlideItemActive(index)
    }

    mouseLeaveSlideItem(index) {
        this.slideItems[index].classList.remove('active')
        this.initSlideTimer(index)
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const slideContainers = document.querySelectorAll('.slide-container')
    slideContainers.forEach(slideContainer => {
        new HomeSlide(slideContainer)
    })
})
