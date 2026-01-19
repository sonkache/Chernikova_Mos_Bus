let slideIndex = 0;
bannerShow();
function bannerShow() {
    let slides = document.getElementsByClassName("banner-slide");
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }

    slideIndex++;
    if (slideIndex > slides.length) slideIndex = 1;
    slides[slideIndex - 1].style.display = "block";
    setTimeout(bannerShow, 4000);
}
