import 'particles.js';
import './plugins/jquery3.min';
import './plugins/owl.carousel.min';
import '../styles/owl.carousel.min.css';

const onPageScroll = function() {
    if (window.scrollY >= 60) {
        VUE.$data.navbarColor = 'white';
        VUE.$data.navbarDark = false;
        VUE.$data.navbarFlat = false;

        if (VUE.$vuetify.breakpoint.smAndDown) {
            VUE.$data.navbarDark = false;
        }

    } else {
        VUE.$data.navbarColor = 'transparent';
        VUE.$data.navbarDark = true;
        VUE.$data.navbarFlat = true;

        if (VUE.$vuetify.breakpoint.smAndDown) {
            VUE.$data.navbarDark = false;
        }
    }
};
window.addEventListener('scroll', onPageScroll);
onPageScroll();

VUE.$nextTick(function() {
    particlesJS.load('particles-js', 'particlesjs-config.json');
    
    $(".owl-carousel-companies").owlCarousel({
        loop: true,
        center: true,
        autoplay: true,
        autoplayTimeout: 3000,
        autoWidth: true
    });

    $(".owl-carousel-images").owlCarousel({
        loop: true,
        center: true,
        autoplay: true,
        autoplayTimeout: 30000,
        autoWidth: true,
    });
});