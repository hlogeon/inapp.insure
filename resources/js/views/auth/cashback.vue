<template>
	<div>
		<v-spinner v-if="loading" />
		<main v-else>
		  <!-- cashback-all -->
		  <section class="cashback-all-section">
		    <div class="container">
		      <!-- wrap -->
		      <div class="cashback-all-wrap">
		        <div class="cashback-all-inner">
		        	<h1 class="title">
		        	  	Поздравляю, твоя квартира застрахована!
		        	</h1>

		        	<p class="cashback-all__text">
		        	  	Где планируешь тратить свой кэшбэк?
		        	</p>

		        	<div data-accordion class="cashback-all-accordion">
		        		
		        	  	<Cashback @chosenCashback="chosenCashback" />

		        		<div class="cashback-all-bottom">
		        		 	<button
		        		 	  class="button button-arrow accordion-button"
		        		 	  type="button"
		        		 	>
		        		 	  <svg class="svg">
		        		 	    <use xlink:href="#arrow-small"></use>
		        		 	  </svg>
		        		 	</button>
		        		 	<div class="error" v-if="error">{{ error }}</div>
		        		  	<a href="javascript:;" class="button submit-button" v-on:click="done()">
		        		  	  	Готово!
		        		  	</a>
		
		        		  	<a href="javascript:;" class="link skipping" v-on:click="nextStep()">
		        		  	  	Пропустить
		        		  	</a>
		        		</div>
		        	</div>
		        </div>
		      </div>
		      <!-- ./ End of wrap -->
		    </div>
		    <!-- ./ End of container -->
		  </section>
		  <!-- ./ End of cashback-all -->
		</main>
	</div>
</template>

<script>
	import Cashback from "../../components/auth/cashbackall"
	export default {
		data:() => ({
			loading: true,
			error: "",
      ids: []
		}),
		components: {
			Cashback
		},
		mounted() {
			const data = new URLSearchParams()
      //data.append('id', this.id)
      data.append('id', this.ids)
			data.append('action', 'checking')
			axios.get('/api/v1/send_phone', {params: data}).then(response => {
			  console.log(data)
				if(response.data.data.hasOwnProperty('cash_id') && response.data.data.cash_id!==null)
					this.nextStep()
			})

			setTimeout(() => {
      		  	this.loading = false
      		}, 500)
      		this.onSubmit()
		},
		methods: {
			nextStep() {
        this.$router.push("/account")
			},
			chosenCashback($ids) {
        //this.id = $id
        this.ids = $ids
			},
			done() {
				if(this.ids.length > 0) {
					const data = new URLSearchParams()
          //data.append('id', this.id)
          data.append('id', this.ids)
					data.append('action', 'cashback')
					axios.get('/api/v1/send_phone', {params: data}).then(response => {
						if(response.data.data.hasOwnProperty('cash_id') && response.data.data.cash_id!==null)
							this.nextStep()
					})
				} else
					this.error = "Вы не выбрали никакого варианта"
			}
		}
	}
</script>