
document.addEventListener('DOMContentLoaded', function () {
    new Splide('#image-slider', {
        type: 'loop',
        perPage: 1,
        pagination: true,
        page: true,
        arrows: true,
    }).mount();
});

$(document).ready(function() {
    $('#wishlist-id').click(function() {
        var currentSrc = $(this).attr('src');
        if (currentSrc === '../../assets/img/icon/star-black.png') {
            $(this).attr('src', '../../assets/img/icon/star-color.png');
        } else {
            $(this).attr('src', '../../assets/img/icon/star-black.png');
        }
    });
});
