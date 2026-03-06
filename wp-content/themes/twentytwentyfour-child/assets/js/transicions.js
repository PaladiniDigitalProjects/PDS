function onJS(){

  barba.init({
    // debug:true,
    transitions: [{
      beforeEnter: ({ next }) => {
        window.scrollTo(0, 0);
        const matches = next.html.match(/<body.+?class="([^""]*)"/i);
        document.body.setAttribute('class', (matches && matches.at(1)) ?? '');
        location.reload();
        // let nextHtml = next.html;
        // Get next page scripts.
        // const nextScripts = $(next.html).filter("scripts");
        // Replace the current scripts with the new ones.
        // $("footer").html(nextScripts.html());
      },

      enter(data) {
      return gsap.from(data.next.container, {
        opacity: 0,
        });
      },
        
      leave(data) {
        return gsap.to(data.current.container, {
        opacity: 0
        });
      },

      }]
    });
  }


window.addEventListener('load', function(){
  onJS();
});