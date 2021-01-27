const _instances = {};

class Accordion {
  constructor(options) {
    this.$el = options.el;
    this.id = options.id;
    this.$triggerBtn = options.triggerBtn;
    this.$hiddenEl = options.hiddenEl;
    // eslint-disable-next-line no-undef
    this.maxHeightHiddenEl = getComputedStyle(this.$hiddenEl).maxHeight.replace(
      /[^\d]/g,
      ''
    );

    this._isOpen = false;
    this._isOpenOnLoad = this.$el.classList.contains('active');

    this.init();
  }

  init() {
    _instances[this.id] = this;

    if (this._isOpenOnLoad) this.open();

    setTimeout(() => {
      if (this.$hiddenEl.scrollHeight < this.maxHeightHiddenEl) {
        this.$el.classList.add('in-active');
      }
    }, 500);

    if (this.$triggerBtn) {
      this.$triggerBtn.addEventListener('click', (e) => {
        if (this._isOpen) {
          this.close();
        } else {
          this.open();
        }
        e.stopPropagation();
      });
    }

    // document.addEventListener('click', (e) => {
    //   let isTargetofChild = Helper.isDescendant(this.$el, e.target);

    //   if(this._isOpen && !isTargetofChild){
    //     this.close();
    //   }
    // });

    // AccordionObserver.subscribe(this.close.bind(this));
  }

  open() {
    if (this._isOpen) return false;

    // AccordionObserver.broadcast();

    this.$el.classList.add('active');
    this.$hiddenEl.style.maxHeight = this.$hiddenEl.scrollHeight + 'px';

    this._isOpen = true;
  }

  close() {
    if (!this._isOpen) return false;

    this.$el.classList.remove('active');
    this.$hiddenEl.style.maxHeight = '';

    this._isOpen = false;
  }

  static initAll() {
    const $els = document.querySelectorAll('[data-accordion]');
    //console.log($els.length)
    $els.forEach((el) => {
      const id = el.getAttribute('data-accordion');
      const $hiddenEl = el.querySelector('.accordion-list');
      const $triggerBtn = el.querySelector('.accordion-button');

      // eslint-disable-next-line no-new
      new Accordion({
        el: el,
        id: id,
        triggerBtn: $triggerBtn,
        hiddenEl: $hiddenEl,
      });
    });
  }

  static open(id) {
    _instances[id].open();
  }

  static close(id) {
    _instances[id].close();
  }
}

export default {
  methods: {
    initAccording() {
      Accordion.initAll()
    }
  }
}
