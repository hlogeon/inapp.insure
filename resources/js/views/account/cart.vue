<template>
	<div>
		<v-spinner v-if="loading" />
		<template v-else>
			<v-header></v-header>
		<section class="step-section">
 		 	<div class="container">
 		 	  <!-- wrap -->
 		 	  <div class="step-wrap">
			  
 		 	  	<router-link to="/settings" class="button button-arrow button-arrow--prev">
           			<svg class="svg">
           			  <use xlink:href="#arrow"></use>
           			</svg>
           		</router-link>
 		 	    <h1 class="title">
 		 	      	Привет<template v-if="name">, {{ name }}</template>! Давай обновим номер твоей карты
 		 	    </h1>
 		 	    <div class="step-form-card j_card">
                  <div class="step-form-card-wrap">
                    <div class="input-wrapper valid">
                    	<div class="input__logo-wrapper" v-if="card.CardType == 'Visa'">
                    	  	<img src="/images/visa.svg" alt="" class="input__logo j_card-brand">
                    	</div>
                    	<div class="input__logo-wrapper" v-else>
                    	  	<img src="/images/master-card-colored.svg" alt="" class="input__logo j_card-brand">
                    	</div>

                    	<label class="input-label">
                    	  	<input required="" class="input input--medium j_card-number" v-model="user_cart" type="text" placeholder="Номер карты">
                    	 		<div class="input__placeholder">
                    	 		  	<span>Номер карты</span>
                    	 		</div>
                    	</label>
                    </div>
                  </div>
                </div>
 		 	    <Pay method="refund" :price="1" btn_text="Обновить данные карты" title="Измнение номера карты" :tarrif="false" @isPaying="beforeSend" />
 		 	    <div class="text-center">
 		 	    	Мы спишем с вашей новой карты 1 рубль<br/>
 		 	    	и тут же вернем его обратно
 		 	    </div>
 		 	  </div>
 		 	  <!-- ./ End of wrap -->
 		 	</div>
 		  <!-- ./ End of container -->
 		</section>
		</template>
	</div>
    
</template>

<script>
	import axios from "axios"
	import Pay from "../../components/payment"

	export default {
		components: {
			Pay
		},
		data:() => ({
			loading: true,
			phone: "",
			errors: [],
			user_cart: "",
			name: "",
			card: false
		}),
		mounted: function() {
			axios.get('/api/v1/get_personal').then(response => {
				//console.log(response)
				if(response.data.status) {
					this.name = response.data.data.user.user_name

					if(response.data.data.card) {
                		this.card = response.data.data.card
                		this.user_cart = (response.data.data.card.CardLastFour) ? "**** **** **** " + response.data.data.card.CardLastFour : ""
					}
				}
			})
			setTimeout(() => {
      		  this.loading = false
      		}, 500)
		},
		methods: {
			beforeSend($response) {
			  var router = this.$router;
				if($response) {
				  setInterval(function(){
            axios.get('/api/v1/pay_for_changing').then(response => {
              //console.log(response)
              router.push('/settings')
            })

          },2000)
				}
			}
		}
	}
</script>

<style>
	.step-form-card-wrap .button {
   		margin-left: auto;
   		margin-right: auto;
  	}
	.step-form-card {
		margin: 20px auto;
	}
	.text-center {
		font-size: 12px;
		line-height: 18px;
		display: block;
		text-align: center;
		color: #757575;
	}
</style>