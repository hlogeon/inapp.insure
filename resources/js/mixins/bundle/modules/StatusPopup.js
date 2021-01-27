const template = ({ status = true, text = 'Изменения сохранены', ...options }) => {
  const className = status ? '' : 'button--red';
  return `
    <p class="button button--big ${className}">
      ${status ? "<svg class='svg'><use xlink:href='#check'></use></svg>" : ''}
      ${text}
    </p>
  `;
};

const initialState = {
  text: 'Изменения сохранены',
};

class StatusPopup {
  constructor(options = initialState) {
    this.$element = document.createElement('div');
    this.$element.classList.add('status-popup');
    this.$element.innerHTML = template(options);

    this.delay = options.delay || 3000;

    this.init();
  }

  init() {
    this.create(document.querySelector('body'));

    setTimeout(this.show.bind(this));
    setTimeout(this.hide.bind(this), this.delay);
  }

  create(node) {
    node.insertAdjacentElement('afterbegin', this.$element);
  }

  remove() {
    this.$element.remove();
  }

  show() {
    this.$element.classList.add('anim');
  }

  hide() {
    this.$element.classList.remove('anim');
    setTimeout(this.remove.bind(this), 600);
  }
}

window.StatusPopup = StatusPopup;

/*
  Показать простое сообщение:
    new window.StatusPopup()

  Показать сообщение: "Hello, World",
  которое закроется через 10 секунд:
    new window.StatusPopup({
      text: "Hello, World",
      delay: 10000
    })
*/
