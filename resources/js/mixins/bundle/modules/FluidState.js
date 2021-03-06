import Helper from './helpers/Helper';

class FluidState {
  constructor(options) {
    this.$el = options.$el;
    this.$parent = options.$parent;
    this.classTarget = options.classTarget;
    this.$target = null;

    this.init();
  }

  init() {
    this.$parent.addEventListener('mousemove', (e) => {
      this.check(e);
      this.showEl();
    });
    this.$parent.addEventListener('mouseout', (e) => this.hideEl());
  }

  check(e) {
    const isTargetOfChild = Helper.isParentHasClass(e.target, this.classTarget);

    if (isTargetOfChild) this.setPosEl(e);
  }

  setPosEl(e) {
    this.$target = e.target.closest(`.${this.classTarget}`);

    const targetRect = this.$target.getBoundingClientRect();
    const parentRect = this.$parent.getBoundingClientRect();

    this.$el.style.width = targetRect.width + 'px';
    this.$el.style.height = targetRect.height + 'px';
    this.$el.style.top =
      targetRect.top - parentRect.top + this.$parent.scrollTop + 'px';
  }

  hideEl() {
    this.$el.style.opacity = 0;
  }

  showEl() {
    this.$el.style.opacity = 1;
  }

  static init() {
    const $el = document.querySelector('.address-placeholder');
    const $parent = document.querySelector('.address-scroller');

    if ($el && $parent) {
      const fluidState = new FluidState({
        $el,
        $parent,
        classTarget: 'address-item',
      });
    }
  }
}

FluidState.init();
window.FluidState = FluidState;
