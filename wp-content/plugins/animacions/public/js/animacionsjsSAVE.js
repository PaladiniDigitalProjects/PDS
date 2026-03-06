window.addEventListener('load',function(event){
    console.log('animacions ON');
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