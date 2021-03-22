<template>
  <div>
    <v-spinner v-if="loading" />
    <template v-else>
      <v-header></v-header>
    <section class="step-section step-section--no-overflow">
        <div class="container">
          <!-- wrap -->
            <div class="step-wrap">
              <ul class="steps-list for-desktop-inline-flex" v-if="!another">
                <li class="steps-list-item steps-list-item--current">
                  <i class="steps-list-item__num"></i>
                </li>
                <li class="steps-list-item">
                  <i class="steps-list-item__num"></i>
                </li>
                <li class="steps-list-item">
                  <i class="steps-list-item__num"></i>
                </li>
              </ul>
              <ul class="steps-list for-mobile-inline-flex" v-if="!another">
                <li class="steps-list-item steps-list-item--completed">
                  <i class="steps-list-item__num"></i>
                </li>
                <li class="steps-list-item steps-list-item--current">
                  <i class="steps-list-item__num"></i>
                </li>
                <li class="steps-list-item">
                  <i class="steps-list-item__num"></i>
                </li>
              </ul>
  
              <h1 class="title">
                <a @click="previousPage" class="button button-arrow button-arrow--prev" v-if="!another">
                  <svg class="svg">
                    <use xlink:href="#arrow"></use>
                  </svg>
                </a>
                <router-link to="/account" class="button button-arrow button-arrow--prev" v-else>
                </router-link>
                  <template v-if="!another">
                      Для начала <br />
                      введи адрес квартиры
                  </template>
                  <template v-else>
                      Введи адрес <br />
                      новой квартиры
                  </template>
              </h1>
  
              <!-- form -->
              <div class="step-form">
                
                <div class="step-form-inputs">
                  <div class="address-dropdown active">
                    <div class="input-wrapper">
                      <label class="input-label">
                        <input
                          id="autocompleteinput"
                              class="input address"
                              type="text"
                              placeholder="Город, улица, номер дома"
                              v-on:keyup="keypressAdress()"
                          />
                        <div class="input__placeholder">
                          <span>Город, улица, номер дома</span>
                        </div>
                      </label>
                    </div>
  
                    <div class="address-content" style="display: none;">
                      <div class="address-scroller">
                        <ul class="address-list">
                          
                        </ul>
  
                        <i class="address-placeholder"></i>
                      </div>
  
                      <div class="address-bottom">
                        <p class="address-subtext"> Не нашли нужный варинт? <b>Укажите адрес вручную</b> </p>
                      </div>
                    </div>
                  </div>
  
                  <div class="input-wrapper">
                    <label class="input-label">
                      <input class="input appartment" type="text" v-on:keyup="isDisabled" v-model="appartment" placeholder="Квартира" />
                      <div class="input__placeholder">
                        <span>Квартира</span>
                      </div>
                    </label>
                  </div>
                </div>
                <div class="error" v-for="value in errors">{{ showError(value) }}</div>
                <button class="button submit-button" type="button" :disabled="disabled" v-on:click="sendSmsAddress()">
                    Продолжить
                </button>
              </div>
              <!-- ./ End of form -->
            </div>
          <!-- ./ End of wrap -->
        </div>
        <!-- ./ End of container -->
    </section>
    </template>
  </div>
	
</template>
<script>
  fbq('track', 'Subscribe');
</script>
<script>
  import fluidstate from "../../mixins/FluidState"

	export default {
		data:() => ({
      loading: true,
			phone: "",
			errors: [],
			nextStep: "",
			address: "",
			appartment: "",
      another: false,
      disabled: true
		}),
    mixins: [fluidstate],
		mounted: function() {
      const data = new URLSearchParams()
      data.append('action', 'checking')
      axios.get('/api/v1/send_phone', {params: data}).then(response => {
          if(response.data.status) {
              this.appartment = response.data.data.appartment
              this.address = response.data.data.address
              if(response.data.data.another_polic)
                this.another = true
              setTimeout(() => {
                document.getElementById('autocompleteinput').value = response.data.data.address
                this.isDisabled()
              }, 500)
          }
      })

			document.addEventListener('click', function(e){
				let list = document.querySelector('.address-content')
				if(list && list.style.display != "none") list.style.display = 'none'
			})
      setTimeout(() => {
        this.loading = false
      }, 500)
      setTimeout(() => {
        this.addressInit()
      }, 600)
      this.onSubmit()
		},
		methods: {
      isDisabled() {
        if(this.address == "" || this.appartment == "") 
         this.disabled = true
        else
         this.disabled = false
      },
      previousPage() {
        return this.$router.go(-1)
      },
			keypressAdress() {
				const input = document.getElementById('autocompleteinput')
				// var autocomplete = new google.maps.places.AutocompleteService()
				 this.address = input.value
        //
  			// 	autocomplete.getPlacePredictions(
  			// 	    {
        //         input: (input.value.length > 0) ? input.value : " ",
        //         componentRestrictions: {country: 'ru'},
        //         types: ['address'],
        //       },
        //       this.getMapResults
        //   )
        if(input.value.length > 0) {
          const url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address";
          const token = "e593fa470435d79ebd2df38b0b41e91e75221387";
          const query = input.value;

          const options = {
            method: "POST",
            mode: "cors",
            headers: {
              "Content-Type": "application/json",
              "Accept": "application/json",
              "Authorization": "Token " + token
            },
            body: JSON.stringify({query: query})
          }

          fetch(url, options)
              .then(response => response.text())
              .then(result => this.getMapResults(result, 'OK'))
              .catch(error => console.log(error));
        }


			},
			getMapResults(predictions, status) {

        let res = JSON.parse(predictions);
        
  			  	const list = document.querySelector('.address-content')
  			  	let th = this
  			  	list.style.display = 'none'
  			  	list.querySelectorAll('li').forEach(function($elem) {
  			  		$elem.remove()
  			  	})

  			  	if (status != 'OK') {
  			  	  	return;
  			  	}

				let $str = "";
        res.suggestions.forEach(function(prediction) {
  			  	  	$str += '<li class="address-item">\
          		      	<p class="address-item__text">\
          		      	  	'+prediction.value+'\
          		      	</p>\
          		    </li>'
  			  	})
  			  	if($str.length > 0) {
  			  		list.querySelector('.address-list').innerHTML = $str
  			  		list.style.display = 'block'
  			  		list.querySelectorAll('li').forEach(function($elem) {
  			  			$elem.addEventListener('click', function(){
  			  				document.getElementById('autocompleteinput').value = this.querySelector('p').innerText
  			  				list.style.display = 'none'
  			  				th.address = this.querySelector('p').innerText
  			  			})
  			  		})
  			  	}
            this.isDisabled()
  			},
			sendSmsAddress() {
				this.errors = []
				
				if( ! this.address) {
					this.errors.push({address: "Вы не ввели адрес"})
				}
				if( ! this.appartment ) {
					this.errors.push({appartment: "Вы не ввели квартиру"})
        }
        this.isInvalid('address')
        this.isInvalid('appartment')

				if(this.errors.length == 0) {
          this.address = ""+this.address.trim()
					const data = new URLSearchParams()
					data.append('phone', this.phone)
					data.append('address', this.address)
					data.append('appartment', this.appartment)
					data.append('action', 'address')
          data.append('remove', true)

			    axios.get('/api/v1/send_phone', {params: data}).then(response => {
			      	console.log(response)
			      	if(response.data.status) {
                  this.$router.push({ path: '/authpayment', query: this.$route.query })
			      	} else {
			      		if(response.data.data.hasOwnProperty('errors'))
			      			response.data.data.errors.forEach(($error) => {
			      				this.errors.push($error)
			      			})
			      	}
              this.isInvalid('address')
              this.isInvalid('appartment')
			      })
			    }
			},
		}
	}
</script>

<style scoped="">
  .for-mobile-inline-flex li:nth-child(1) {
    flex: 1 1 50% !important;
  }
  .for-mobile-inline-flex li:nth-child(2),
  .for-mobile-inline-flex li:nth-child(3) {
    flex: 1 1 25% !important;
  }
</style>