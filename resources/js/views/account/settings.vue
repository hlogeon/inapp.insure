<template>
	<div>
		<v-spinner v-if="loading" />
	<template v-else>
    <v-header></v-header>
		<main>
  		  <section class="settings-section">
  		    <div class="container">
  		      <!-- wrapper -->
  		      <div class="settings-wrapper">
  		        <h1 class="title">Настройки</h1>
  		        <!-- personal -->
  		        <div class="settings-personal">
                <h3 class="block-title">Личные данные</h3>
                <div class="settings-personal__wrapper">
                  <div class="settings-wrapper__group">
                    <div class="settings-wrapper__item input-wrapper">
                      <label class="input-label">
                        <input required class="input input--big" disabled type="text" @click="bind" v-model="user_name" placeholder="Имя" />
                        <div class="input__placeholder"><span>Имя</span></div>
                      </label>
                    </div>
                    <div class="settings-wrapper__item input-wrapper">
                      <label class="input-label">
                        <input required class="input input--big" disabled type="text" @click="bind" v-model="user_surname" placeholder="Фамилия" />
                        <div class="input__placeholder"><span>Фамилия</span></div>
                      </label>
                    </div>
                  </div>
                  <div class="settings-wrapper__group">
                    <div class="settings-wrapper__item input-wrapper">
                      <label class="input-label">
                        <input required class="input input--big" disabled type="text" @click="bind" v-model="user_birsday" placeholder="Дата рождения" />
                        <div class="input__placeholder"><span>Дата рождения</span></div>
                      </label>
                    </div>
                </div>
                </div>
              </div>
  		        <!-- ./ End of personal -->
  		        <!-- phone -->
  		        <div class="settings-phone">
  		          <h3 class="block-title">Номер телефона</h3>
  		          <div class="settings-phone__wrapper">
  		            <div class="settings-table">
  		              <div class="settings-table__row">
                      <router-link to="/changephone" class="settings-table__row-link">
                        <svg class="settings-table__row-icon">
                          <use xlink:href="#edit"></use>
                        </svg>
                      </router-link>
  		                <div class="settings-table__col input-wrapper">
  		                  <label class="input-label">
  		                    <input
  		                      required
  		                      class="input input--big input--transparent j_mask"
  		                      type="tel"
                            v-model="user_phone"
  		                      placeholder="Телефон"
                            @click="bind"
                            disabled
  		                    />
  		                    <div class="input__placeholder"><span>Телефон</span></div>
  		                  </label>
  		                </div>
  		              </div>
  		            </div>
  		          </div>
  		        </div>
  		        <!-- ./ End of phone -->
  		        <!-- payments -->
  		        <div class="settings-payments">
  		          <h3 class="block-title">Платежные данные</h3>
  		          <div class="settings-payments__wrapper">
  		            <div class="settings-table">
  		              <div class="settings-table__row">
  		                <router-link to="/changecart" class="settings-table__row-link">
  		                  <svg class="settings-table__row-icon">
  		                    <use xlink:href="#edit"></use>
  		                  </svg>
  		                </router-link>
  		                <div class="settings-table__col input-wrapper">
  		                  <label class="input-label">
  		                    <input
  		                      required
  		                      class="input input--big input--transparent"
  		                      type="tel"
                            v-model="user_cart"
  		                      placeholder="Карта"
                            @click="bind"
                            disabled
  		                      readonly
  		                    />
  		                    <div class="input__placeholder"><span>Карта</span></div>
  		                  </label>
  		                </div>
  		              </div>
  		            </div>
  		          </div>
  		        </div>
  		        <!-- ./ End of payments -->
  		        <!-- past-payments -->
  		        <div class="settings-past-payments" v-if="payments.length > 0">
  		          <h3 class="block-title">Информация о платежах</h3>
  		          <div class="settings-past-payments__wrapper"></div>
  		          <div class="settings-table">
  		            <div class="settings-table__wrapper">
  		              <div class="settings-table__header">
  		                <div class="settings-table__col settings-past-payments__adress">
  		                  Адрес квартиры
  		                </div>
  		                <div class="settings-table__col settings-past-payments__data">
  		                  Дата
  		                </div>
  		                <div class="settings-table__col settings-past-payments__total">
  		                  Сумма
  		                </div>
  		              </div>
  		              
  		              <Payments :payments="payments" />

  		            </div>
  		          </div>
  		        </div>
  		        <!-- ./ End of past-payments -->
  		      </div>
  		      <!-- ./ End of wrapper -->
  		    </div>
  		    <!-- ./ End of container -->
  		  </section>
  		  <!-- ./ End of settings -->
  		</main>

      <v-footer></v-footer>
	</template>
	</div>
</template>

<script>
  //import DatePicker from 'vue2-datepicker'
  //import 'vue2-datepicker/index.css'
  //import 'vue2-datepicker/locale/ru'
  import moment from 'moment'
  import date from "../../mixins/date"
  import Payments from "../../components/account/payment"
  import DatePickerFile from "../../mixins/datepicker"
  import popupFile from "../../mixins/popup"

	export default {
		data: () => ({
			  loading: true,
        user_name: "",
        user_surname: "",
        user_birsday: "",
        user_phone: "",
        user_cart: "",
        payments: [],
		}),
    mixins: [date, DatePickerFile, popupFile],
    components: {
        Payments
    },
		mounted: function() {
			  setTimeout(() => {
          this.loading = false
        }, 500)

			  setTimeout(() => {
          this.initDatepickers()
        }, 1000)

        axios.get('/api/v1/get_personal').then(response => {
          console.log('rrr', response)
          if(response.data.status) {
              let $user = response.data.data.user
              this.user_name = $user.user_name
              this.user_surname = $user.user_surname
              this.user_birsday = ($user.user_birsday) ? moment($user.user_birsday).format('DD.MM.YYYY') : ""
              this.user_phone = $user.phone
              this.payments = (response.data.data.payments.length > 0) ? response.data.data.payments : []

              if(response.data.data.card.hasOwnProperty('CardLastFour'))
                this.user_cart = "**** **** **** " + response.data.data.card.CardLastFour
              
              //console.log('date', this.user_birsday)
          }
        })
		},
    methods: {
      bind($elem) {
        $elem.target.setAttribute('disabled', true)
      }
    }
	}
</script>
