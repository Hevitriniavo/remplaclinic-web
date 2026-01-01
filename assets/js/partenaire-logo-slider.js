class PartenaireLogoSlide {
    constructor(slideContainer) {
        this.slideItems = slideContainer.querySelectorAll('.slide-partenaire-logo-item')
        this.slideItemActiveIndex = 0
        this.slideTimer = null

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
        }, 5000)
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

export default function createPartenaireLogoSlider() {
    const partenaireLogoContainers = document.querySelectorAll('.slide-partenaire-logo')
    partenaireLogoContainers.forEach(partenaireLogoContainer => {
        new PartenaireLogoSlide(partenaireLogoContainer)
    })
}
