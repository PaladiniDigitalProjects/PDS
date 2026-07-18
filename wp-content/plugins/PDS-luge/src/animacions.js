window.addEventListener('load', function() {
  console.log('animations ON');

  // -------------------------
  // 1. Add <p class="alt"> for each image
  // -------------------------
  jQuery(function($) {
    $(".ghub-scroll-box-wrapper img").each(function() {
      $(this).after('<p class="alt">' + $(this).attr('alt') + '</p>');
    });
  });

  // -------------------------
  // 2. Scroll-triggered background color
  // -------------------------
  //gsap.registerPlugin(ScrollTrigger);

  gsap.utils.toArray('.bkg').forEach(section => {
    const colorAttr = section.getAttribute('data-color');
    if (colorAttr) {
      gsap.to("body", {
        backgroundColor: colorAttr,
        immediateRender: false,
        scrollTrigger: {
          trigger: section,
          scrub: true,
          start: 'top bottom',
          end: '+=100%'
        }
      });
    }
  });

  // -------------------------
  // 3. Animations
  // -------------------------
  const time = 1;
  const FL = gsap.utils.toArray('.FromLeft');
  const FR = gsap.utils.toArray('.FromRight');
  const OP = gsap.utils.toArray('.opacity');
  const TXT = gsap.utils.toArray('.AnimateText');

  function animateLeftRight(elements, distance) {
    elements.forEach(el => {
      const direction = el.classList.contains('FromLeft') ? 1 : -1;
      gsap.fromTo(el, 
        { x: 0 }, 
        {
          x: distance * direction,
          duration: time,
          scrollTrigger: {
            trigger: el,
            start: 'top center',
            end: '+=40%',
            toggleActions: "play none none none"
          }
        }
      );
    });
  }

  function animateOpacity(elements) {
    elements.forEach(el => {
      gsap.fromTo(el, { opacity: 0 }, {
        opacity: 1,
        duration: time,
        scrollTrigger: {
          trigger: el,
          start: 'top 40%',
          end: '+=60%',
          toggleActions: "play none none none"
        }
      });
    });
  }

  // -------------------------
  // 3b. Animate text with directional movement
  // -------------------------
  function animateText(elements, distance = 100) {
    elements.forEach(el => {
      // Determine direction based on viewport width
      const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
      const direction = vw >= 769 ? 1 : -1; // example: large screens move right, smaller move left

      gsap.fromTo(el, { opacity: 0, x: -distance * direction }, {
        opacity: 1,
        x: 0,
        duration: time,
        scrollTrigger: {
          trigger: el,
          start: 'center',
          end: '+=50%',
          toggleActions: "play none none none"
        }
      });
    });
  }

  // -------------------------
  // 4. Responsive animations
  // -------------------------
  ScrollTrigger.matchMedia({
    "(min-width: 769px)": () => {
      animateLeftRight(FL, 769);
      animateLeftRight(FR, 769);
    },
    "(min-width: 400px) and (max-width: 768px)": () => {
      animateLeftRight(FL, 768);
      animateLeftRight(FR, 768);
    },
    "(max-width: 399px)": () => {
      animateLeftRight(FL, 399);
      animateLeftRight(FR, 399);
    },
    "all": () => {
      animateOpacity(OP);
      animateText(TXT, 100);
    }
  });

  // -------------------------
  // 5. Handle window resize for vw-based animations
  // -------------------------
  window.addEventListener('resize', () => {
    const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
    FL.forEach(el => gsap.set(el, { x: 0 }));
    FR.forEach(el => gsap.set(el, { x: 0 }));
    animateLeftRight(FL, vw);
    animateLeftRight(FR, vw);
    animateText(TXT, 100); // recalc text movement on resize
  });

  // -------------------------
  // 6. Image scale + pin animation
  // -------------------------
  const imgWrapper = document.querySelector("#image");
  if (imgWrapper) {
    const img = imgWrapper.querySelector("img");
    const tl = gsap.timeline({ ease: "none" });

    tl.from(img, {
      scale: 0.6,
      duration: 1,
      transformOrigin: "bottom center"
    }).to({}, { duration: 1 }); // pause timeline

    ScrollTrigger.create({
      trigger: "#image",
      start: "top top",
      end: "+=200%",
      pin: true,
      animation: tl,
      scrub: 0.78,
      pinSpacing: false
    });
  }

});
