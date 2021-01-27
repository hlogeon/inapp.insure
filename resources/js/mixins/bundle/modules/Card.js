import CardInfo from 'card-info/dist/card-info.min';

function requireAll(r) {
  r.keys().forEach(r);
}
requireAll(require.context('../../img/brands/', true));

CardInfo.setDefaultOptions({
  banksLogosPath: '/images/',
  brandsLogosPath: '/images/',
  brandLogoPolicy: 'colored',
});

class Card {
  constructor($card) {
    this.$card = $card;
    this.$number = $card.querySelector('.j_card-number');
    this.$date = $card.querySelector('.j_card-date');
    this.$cvv = $card.querySelector('.j_card-cvv');
    this.$cvvTrigger = $card.querySelector('.j_card-cvv-trigger');
    this.$brand = $card.querySelector('.j_card-brand');

    this._isPasswordShow = false;

    this.init();
  }

  init() {
    this.check();
    this.$number.addEventListener('input', () => this.check());
    this.$date.addEventListener('input', (e) => this.dateMask(e));
    this.$cvv.addEventListener('input', () => this.cvvMask());
    this.$cvvTrigger.addEventListener('click', () => this.togglePassword());
    // this.$cvvTrigger.addEventListener('mousedown', () => this.showPassword());
    // this.$cvvTrigger.addEventListener('mouseup', () => this.hidePassword());
    // document.addEventListener('mouseup', () => this.hidePassword());
  }

  togglePassword() {
    this._isPasswordShow ? this.hidePassword() : this.showPassword();
  }

  showPassword() {
    if (this._isPasswordShow) return false;
    console.log('show');
    this.$cvv.setAttribute('type', 'text');
    this._isPasswordShow = true;
  }

  hidePassword() {
    if (!this._isPasswordShow) return false;
    console.log('hide');
    this.$cvv.setAttribute('type', 'password');
    this._isPasswordShow = false;
  }

  cvvMask() {
    this.$cvv.value = this.$cvv.value.replace(/\D/g, '');
  }

  dateMask(e) {
    const maxLength = this.$date.getAttribute('maxlength');
    let value = this.$date.value.replace(/\D/g, '').split('');

    if (value.length > maxLength - 1) value = [];
    if (value.length > 2) value.splice(2, 0, '/');
    if (value.length === 3 && !e.data) value = value.slice(0, -1);

    value = value.join('');

    this.$date.value = value;
  }

  check() {
    this.$number.value = this.$number.value.replace(/\D/g, '');
    this.cardInfo = new CardInfo(this.$number.value);
    this.$number.value = this.cardInfo.numberNice;

    if (this.cardInfo.brandLogo) {
      this.setBrand();
      this.showBrand();
    } else {
      this.hideBrand();
    }
  }

  setBrand() {
    this.$brand.setAttribute('src', this.cardInfo.brandLogo);
  }

  hideBrand() {
    this.$brand.classList.add('hidden');
  }

  showBrand() {
    this.$brand.classList.remove('hidden');
  }
}

const $cards = document.querySelectorAll('.j_card');
if ($cards.length) {
  $cards.forEach(($card) => new Card($card));
}
