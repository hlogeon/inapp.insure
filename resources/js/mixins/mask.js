export default {
  data:() => ({
    event: null
  }),
  mounted() {
    setTimeout(() => {
      const inputs = document.querySelectorAll('.j_mask'); // Inputs

      if (inputs.length) {
        // eslint-disable-next-line no-undef
        this.event = new Event('input');
      
        inputs.forEach((input) => {
          input.addEventListener('input', this.mask(), false);
          input.addEventListener('focus', this.mask(), false);
          input.addEventListener('blur', this.mask(), false);
          input.addEventListener('keydown', this.mask(), false);
      
          if (input.value !== '') {
            input.dispatchEvent(this.event);
            input.blur();
          }
        });
      }
    }, 600)
  },
  methods: {
      setCursorPosition(pos, elem) {
        elem.focus();
        if (elem.setSelectionRange) elem.setSelectionRange(pos, pos);
        else if (elem.createTextRange) {
          const range = elem.createTextRange();
          range.collapse(true);
          range.moveEnd('character', pos);
          range.moveStart('character', pos);
          range.select();
        }
      },
      
      mask() {
        if (this.selectionStart < 4) this.event.preventDefault();
        const matrix = '+7 (___) ___-__-__';
        let i = 0;
        const def = matrix.replace(/\D/g, '');
        let val = this.phone.replace(/\D/g, '');
      
        if (def.length >= val.length) val = def;
        this.phone = matrix.replace(/[_\d]/g, function(a) {
          return i < val.length ? val.charAt(i++) : a;
        });
        i = this.phone.indexOf('_');
        if (this.event.keyCode === 8) i = this.phone.lastIndexOf(val.substr(-1)) + 1;
        if (i !== -1) {
          i < 5 && (i = 4);
          this.phone = this.phone.slice(0, i);
        }
        if (this.event.type === 'blur') {
          if (this.phone.length < 5) this.phone = '';
        } else this.setCursorPosition(this.phone.length, this.phone);
      }
    }
}