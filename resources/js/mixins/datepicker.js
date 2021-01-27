
export default {
  methods: {
    initDatepickers() {
      const $inputss = document.querySelectorAll('.j_date-mask')
      if ($inputss.length) {
          $inputss.forEach(($input) => {
            let maxLength = $input.getAttribute('maxlength') || 10;
            $input.addEventListener('keyup', (e) => {
              let value = $input.value.replace(/\D/g, '').split('');
      
              if (value.length > maxLength - 1) value = [];
              if (value.length > 2) value.splice(2, 0, '.');
              if (value.length > 5) value.splice(5, 0, '.');
      
              $input.value = value.join('');
            });
          })
          
      }

      const $inputs = document.querySelectorAll('.j_datepicker');
    
      if ($inputs.length) {
        const datepickers = [];
    
        $inputs.forEach(($input, i) => {
          const $value = $input.querySelector('.input');
    
          const datepicker = new Datepicker($input, {
            weekStart: 1,
            inline: false,
            min: this.getMin($input),
    
            i18n: {
              months: [
                'Январь',
                'Февраль',
                'Март',
                'Апрель',
                'Май',
                'Июнь',
                'Июль',
                'Август',
                'Сентябрь',
                'Октябрь',
                'Ноябрь',
                'Декабрь',
              ],
              weekdays: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
              time: ['Время', 'Начало', 'Конец'],
            },
    
            templates: {
              header: [
                `<header class="datepicker__header">
                  <button type="button" class="button datepicker__prev<%= (hasPrev) ? "" : " is-disabled" %>" data-prev>
                    <svg data-prev disabled class="button__icon">
                      <use data-prev xlink:href="#arrow-small"></use>
                    </svg>
                  </button>
                  <div class="datepicker__titles">
                    <span class="datepicker__title"><%= renderMonthSelect() %></span>
                    <span class="datepicker__title datepicker__title--year"><%= renderYearSelect() %></span>
                  </div>
                  <button type="button" class="button datepicker__next<%= (hasNext) ? "" : " is-disabled" %>" data-next>
                    <svg data-next disabled data-next class="button__icon">
                      <use data-next xlink:href="#arrow-small"></use>
                    </svg>
                  </button>
                </header>`,
              ].join(''),
            },
    
            onChange: (e) => {
              if (e && datepicker) {
                // const date = datepicker
                //   .getValue()
                //   .split('.')
                //   .reverse()
                //   .join('-');
    
                const date = datepicker.getValue();
    
                if ($input) {
                  $input.value = date;
                  $input.setAttribute('value', date);
                  if ($value) {
                    $value.value = date;
                    let event = new Event("change")
                    $value.dispatchEvent(event)
                  }
                }
    
                if (typeof getActivity === 'function') window.getActivity(date);
              }
            },
    
            onRender: (e) => {
              if (typeof setDateOnRender === 'function') window.setDateOnRender();
            },
          });
    
          datepickers.push(datepicker);
          window.datepickers = datepickers;
        });
      }
    },
    
    getMin($input) {
      const min = +$input.getAttribute('data-datepicker-min') || false;
    
      if (!min) return false;
    
      const date = new Date();
      date.setDate(date.getDate() - 1 + min);
      return date;
    },
    
    _getReverseDate(d) {
      console.log(d);
      const dateStr = d.split('.');
      const year = Number(dateStr[2]);
      const month = Number(dateStr[1]) - 1;
      const day = Number(dateStr[0]);
    
      return new Date(year, month, day);
    },
    
    setDate(datepicker, d) {
      const date = this._getReverseDate(d);
    
      datepicker.setDate([date]);
      datepicker.render();
    },
    
    setEventDate(datepicker, d) {
      const date = this._getReverseDate(d);
      const timestamp = new Date(date).getTime();
      const currentDay = datepicker.node.querySelector(`[data-day="${timestamp}"]`);
    
      currentDay.classList.add('is-event');
    }
  }
}
