import Datepicker from 'Datepicker.js';

function initDatepickers() {
  const $inputs = document.querySelectorAll('.j_datepicker');

  if ($inputs.length) {
    const datepickers = [];

    $inputs.forEach(($input, i) => {
      const $value = $input.querySelector('.input');

      const datepicker = new Datepicker($input, {
        weekStart: 1,
        inline: false,
        // min: (function() {
        //   var date = new Date();
        //   date.setDate(date.getDate() - 1);
        //   return date;
        // })(),

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
              if ($value) $value.value = date;
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
}

function _getReverseDate(d) {
  console.log(d);
  const dateStr = d.split('.');
  const year = Number(dateStr[2]);
  const month = Number(dateStr[1]) - 1;
  const day = Number(dateStr[0]);

  return new Date(year, month, day);
}

function setDate(datepicker, d) {
  const date = _getReverseDate(d);

  datepicker.setDate([date]);
  datepicker.render();
}

function setEventDate(datepicker, d) {
  const date = _getReverseDate(d);
  const timestamp = new Date(date).getTime();
  const currentDay = datepicker.node.querySelector(`[data-day="${timestamp}"]`);

  currentDay.classList.add('is-event');
}

initDatepickers();
window.initDatepickers = initDatepickers;
window.setDate = setDate;
window.setEventDate = setEventDate;
