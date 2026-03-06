window.addEventListener('load',function(event){
    console.log('animacions ON');

    // Wrap every .img in a div
  // 1. For all items with class .img...
  // 2. Wrap them in a div
  jQuery(function($) {
    $(".ghub-scroll-box-wrapper img").each(function() {
      $(this).after('<p class="alt">' + $(this).attr('alt') + '</p>');
    });
  });
  
  // CSS shows/hides the alt text on hover


 },false);


/* SCROLL BKG */

window.addEventListener('load', () => {

    gsap.registerPlugin(ScrollTrigger);
    gsap.utils.toArray('.bkg').forEach((section, i) => {
    
      if(section.getAttribute('data-color') !== null) {
        
        var colorAttr = section.getAttribute('data-color')
        gsap.to("body", {
          backgroundColor: colorAttr,
          immediateRender: false,
          scrollTrigger: {
            trigger: section,
            scrub: true,
            start:'top bottom',
            end: '+=100%'
          }
        });
      }
    });



/* REGISTRAR ANIMACIONS */

let time = 1;
let vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
let vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
const FL = gsap.utils.toArray('.FromLeft');
const FR = gsap.utils.toArray('.FromRight');
const OP = gsap.utils.toArray('.opacity');
const TXT = gsap.utils.toArray('.AnimateText');

/* ANIMACIONS */

function salto(element) {
  gsap.fromTo(element, {x: 0}, {x: 100, duration: 3, ease: "expo.in"});
}


ScrollTrigger.matchMedia({
  // large
  "(min-width: 769px)": function () {
    
    /* ESQUERRA CAP A LA DRETA */

    FL.forEach(boxD => {
      gsap.to(boxD, { 
        duration: time,
        x: 769,
        scrollTrigger: {
          trigger: boxD,
          start:'top center',
          end:'+=40%',
          toggleActions: "play none none none",
        }
      })
    });

    /* DRETA CAP A LA ESQUERRA */

    FR.forEach(boxE => {
      gsap.to(boxE, { 
        duration: time,
        x: -769,
        scrollTrigger: {
          trigger: boxE,
          start:'top center',
          end: '+=40%',
          toggleActions: "play none none none",
        }
      })
    });


  },

  // medium
  "(min-width: 400px) and (max-width: 768px)": function () {

    /* ESQUERRA CAP A LA DRETA */

    FL.forEach(boxD => {
      gsap.to(boxD, { 
        duration: time,
        x: 768,
        scrollTrigger: {
          trigger: boxD,
          start:'top center',
          end:'+=40%',
          toggleActions: "play none none none",
        }
      })
    });

    /* DRETA CAP A LA ESQUERRA */

    FR.forEach(boxE => {
      gsap.to(boxE, { 
        duration: time,
        x: -768,
        scrollTrigger: {
          trigger: boxE,
          start:'top center',
          end: '+=40%',
          toggleActions: "play none none none",
        }
      })
    });
  },

  // small
  "(max-width: 399px)": function () {
    /* ESQUERRA CAP A LA DRETA */

    FL.forEach(boxD => {
      gsap.to(boxD, { 
        duration: time,
        x: 399,
        scrollTrigger: {
          trigger: boxD,
          start:'top center',
          end:'+=40%',
          toggleActions: "play none none none",
        }
      })
    });

    /* DRETA CAP A LA ESQUERRA */

    FR.forEach(boxE => {
      gsap.to(boxE, { 
        duration: time,
        x: -399,
        scrollTrigger: {
          trigger: boxE,
          start:'top center',
          end: '+=40%',
          toggleActions: "play none none none",
        }
      })
    });

  },

  // all
  all: function () {
    // ScrollTriggers created here aren't associated with a particular media query,
    
     /* ESQUERRA CAP A LA DRETA */

     FL.forEach(boxD => {
      gsap.to(boxD, { 
        duration: time,
        x: vw,
        scrollTrigger: {
          trigger: boxD,
          start:'top center',
          end:'+=40%',
          toggleActions: "play none none none",
        }
      })
    });

    /* DRETA CAP A LA ESQUERRA */

    FR.forEach(boxE => {
      gsap.to(boxE, { 
        duration: time,
        x: -vw,
        scrollTrigger: {
          trigger: boxE,
          start:'top center',
          end: '+=40%',
          toggleActions: "play none none none",
        }
      })
    });


    /* OPACITAT + MOVEMENT */

    TXT.forEach(textMo => {
      gsap.to(textMo, { 
        duration: time,
        opacity: 1,
        scrollTrigger: {
          trigger: textMo,
          start:'center',
          end:'+=50%',
          toggleActions: "play none none none",
          animation: salto(textMo),
        }
      })
    });

    /* OPACITAT */

    OP.forEach(boxO => {
      gsap.to(boxO, { 
        duration: time,
        opacity: 1,
        scrollTrigger: {
          trigger: boxO,
          start:'top 40%',
          end:'+=60%',
          toggleActions: "play none none none",
        }
      })
    });


  },
});


// The relevant part to keeping the progress
ScrollTrigger.addEventListener("refreshInit", () => progress = FL.progress);
ScrollTrigger.addEventListener("refreshInit", () => progress = FR.progress);
ScrollTrigger.addEventListener("refreshInit", () => progress = OP.progress);






/* IMG SCALE */


// const tl = gsap.timeline({
//   ease: "none"
//   });
  
//   tl.from("#image img", {
//     scale: 0.6,
//     duration: 1,
//     transformOrigin: "bottom center",
//   }).to({}, {
//     duration: 1
//   })
//   ScrollTrigger.create({
//     trigger: "#image",
//     start: "top top",
//     end: "+=200%",
//     pin: true,
//     animation: tl,
//     scrub: 0.78,
//     pinSpacing: false
//   })

});