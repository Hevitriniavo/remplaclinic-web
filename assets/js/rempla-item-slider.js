class RemplaItemSlide {
    constructor(slideContainer, duration = 5000, parentSelector = '.slide-rempla') {
        this.slideItems = slideContainer.querySelectorAll(parentSelector + '-item')
        this.slideItemActiveIndex = 0
        this.slideTimer = null
        this.duration = duration

        this.initSlideTimer(0)
    }

    initSlideTimer(startIndex) {
        if (startIndex >= this.slideItems.length) {
            return
        }

        this.slideItemActiveIndex = startIndex
        this.setSlideItemActive(startIndex)
        this.slideTimer = setInterval(() => {
            this.slideItemActiveIndex++

            if (this.slideItemActiveIndex >= this.slideItems.length) {
                this.slideItemActiveIndex = 0
            }

            this.setSlideItemActive(this.slideItemActiveIndex)
        }, this.duration)
    }

    setSlideItemActive(index) {
        if (index >= this.slideItems.length) {
            return
        }

        this.slideItems.forEach((item) => {
            item.classList.remove('active')
        })

        this.slideItems[index].classList.add('active')
    }
}

export default function createRemplaItemSlider(selector, duration = 5000) {
    const itemContainers = document.querySelectorAll(selector)
    itemContainers.forEach(itemContainer => {
        new RemplaItemSlide(itemContainer, duration, selector)
    })
}
