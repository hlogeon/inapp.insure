import Helper from './helpers/Helper';

class Header {
  constructor($header) {
    this.$header = $header;
    this.currentScroll = window.pageYOffset;

    this._init();
  }

  _init() {
    if (!this.$header) return false;

    document.addEventListener('DOMContentLoaded', () => this.checkPosition());
    window.addEventListener('load', () => {
      this.checkTouch();
      this.addPadding();
    });
    window.addEventListener('resize', () => this.addPadding());
    window.addEventListener('scroll', () => this.checkPosition());
  }

  checkPosition() {
    window.pageYOffset !== 0
      ? this.$header.classList.add('fixed')
      : this.$header.classList.remove('fixed');

    this.currentScroll = window.pageYOffset;
  }

  addPadding() {
    const $firstSection = document.querySelector('section');
    if (!$firstSection) return false;
    $firstSection.style.paddingTop = '';

    const paddingTop = Number(
      // eslint-disable-next-line no-undef
      getComputedStyle($firstSection).paddingTop.replace(/[^\d]/g, '')
    );

    $firstSection.style.paddingTop =
      paddingTop + this.$header.clientHeight + 'px';
  }

  checkTouch() {
    if (document.body.clientHeight >= window.innerHeight) {
      if (Helper.isMobileOrTablet()) {
        document.body.classList.add('touch-device');
      } else if ($header.offsetWidth === window.innerWidth) {
        this.$header.style.paddingRight = `${Helper.getScrollBarWidth()}px`;
      }
    }
  }
}

const $header = document.querySelector('.header');

let header = null;

if ($header) header = new Header($header);

export { header };
