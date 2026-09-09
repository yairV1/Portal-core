(function () {
  'use strict';
  var track = document.getElementById('module-track');
  var cards = track.children;
  var total = cards.length;
  var dotsWrap = document.getElementById('carousel-dots');
  var btnLeft = document.getElementById('scroll-left');
  var btnRight = document.getElementById('scroll-right');
  var index = 0;

  function itemsPerView() {
    var w = window.innerWidth;
    if (w <= 620) return 1;
    if (w <= 980) return 2;
    return 3;
  }

  function maxIndex() {
    return Math.max(0, total - itemsPerView());
  }

  function renderDots() {
    dotsWrap.innerHTML = '';
    var count = maxIndex() + 1;
    for (var i = 0; i < count; i++) {
      var dot = document.createElement('button');
      dot.setAttribute('aria-label', 'Ir al grupo ' + (i + 1));
      if (i === index) dot.classList.add('is-active');
      (function (i) {
        dot.addEventListener('click', function () { goTo(i); });
      })(i);
      dotsWrap.appendChild(dot);
    }
  }

  function update() {
    var max = maxIndex();
    if (index > max) index = max;
    if (index < 0) index = 0;

    var card = cards[0];
    var gap = 20;
    var step = card.getBoundingClientRect().width + gap;
    track.style.transform = 'translateX(' + (-index * step) + 'px)';

    btnLeft.disabled = index === 0;
    btnRight.disabled = index === max;
    renderDots();
  }

  function goTo(i) { index = i; update(); }

  btnLeft.addEventListener('click', function () { index -= 1; update(); });
  btnRight.addEventListener('click', function () { index += 1; update(); });
  window.addEventListener('resize', update);

  update();
})();